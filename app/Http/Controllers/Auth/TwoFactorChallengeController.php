<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorEmailToken;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\MailService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor
    ) {}

    public function show(Request $request)
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:32',
        ]);

        $userId = $request->session()->get('two_factor_login.id');
        $remember = (bool) $request->session()->get('two_factor_login.remember', false);
        $user = User::query()->find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor_login.id', 'two_factor_login.remember']);

            return redirect()->route('login')->with('error', 'Sesión de verificación inválida. Intenta iniciar sesión de nuevo.');
        }

        $code = (string) $request->input('code');
        $normalized = preg_replace('/\s+/', '', $code) ?? '';
        $ok = false;

        if (strlen($normalized) === 6 && ctype_digit($normalized)) {
            $secret = $user->two_factor_secret;
            if ($secret && $this->twoFactor->verifyTotp($secret, $normalized)) {
                $ok = true;
            }
        } else {
            $ok = $this->twoFactor->verifyAndConsumeRecoveryCode($user, $code);
            $user->refresh();
        }

        if (! $ok) {
            throw ValidationException::withMessages([
                'code' => ['El código no es válido o ya fue usado.'],
            ]);
        }

        $request->session()->forget(['two_factor_login.id', 'two_factor_login.remember']);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        app(ActivityLogService::class)->log('login', 'Inicio de sesión en el sistema (2FA verificado)');

        $default = $user->hasRole('client')
            ? route('portal.dashboard')
            : route('admin.dashboard');

        return redirect()->intended($default);
    }

    /**
     * Envía correo con enlace para desactivar 2FA (pérdida del autenticador).
     */
    public function sendRecoveryEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $userId = $request->session()->get('two_factor_login.id');
        $user = User::query()->find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login')->with('error', 'Sesión inválida.');
        }

        if (! hash_equals(strtolower((string) $user->email), strtolower($request->input('email')))) {
            throw ValidationException::withMessages([
                'email' => ['El correo no coincide con la cuenta en verificación.'],
            ]);
        }

        TwoFactorEmailToken::query()->where('user_id', $user->id)->delete();

        $plain = Str::random(64);
        TwoFactorEmailToken::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addHours(1),
        ]);

        $url = url(route('two-factor.recovery.confirm', ['token' => $plain], true));

        $body = '<p>Hola '.e($user->name).',</p>'
            .'<p>Se solicitó recuperar el acceso a tu cuenta sin el autenticador de dos factores.</p>'
            .'<p><a href="'.$url.'">Desactivar 2FA y volver a iniciar sesión</a></p>'
            .'<p>Si no fuiste tú, ignora este mensaje. El enlace caduca en 1 hora.</p>';

        $mail = app(MailService::class);
        $sent = $mail->send($user->email, 'Recuperación de acceso (2FA)', $body);

        if (! $sent) {
            return back()->with('error', 'No se pudo enviar el correo. Revisa la configuración de correo.');
        }

        return back()->with('status', 'Revisa tu bandeja: te enviamos un enlace para desactivar el 2FA.');
    }

    /**
     * Confirma token por correo y desactiva 2FA.
     */
    public function confirmRecovery(Request $request, string $token)
    {
        $hashed = hash('sha256', $token);
        $record = TwoFactorEmailToken::query()
            ->where('token', $hashed)
            ->first();

        if (! $record || $record->isExpired()) {
            return redirect()->route('login')->with('error', 'El enlace no es válido o ha caducado.');
        }

        $user = $record->user;
        $record->delete();

        $this->twoFactor->disableTwoFactor($user);

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->forget(['two_factor_login.id', 'two_factor_login.remember']);

        return redirect()->route('login')
            ->with('success', 'La verificación en dos pasos se desactivó. Inicia sesión con tu contraseña y, si quieres, vuelve a activar el 2FA en tu perfil.');
    }
}
