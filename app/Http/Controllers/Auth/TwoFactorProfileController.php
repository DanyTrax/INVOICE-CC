<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorProfileController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor
    ) {}

    /**
     * Inicia configuración: genera secreto y códigos de respaldo (sesión hasta confirmar).
     */
    public function start(Request $request)
    {
        if (! $this->twoFactor->isSystemEnabled()) {
            return back()->with('error', 'La verificación en dos pasos está desactivada por el administrador del sistema.');
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasTwoFactorEnabled()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ya tienes el 2FA activado.'], 422);
            }

            return back()->with('error', 'Ya tienes el 2FA activado.');
        }

        $secret = $this->twoFactor->generateSecretKey();
        $recoveryPlain = $this->twoFactor->generateRecoveryCodesPlain();

        $request->session()->put('two_factor_setup.secret', Crypt::encryptString($secret));
        $request->session()->put('two_factor_setup.recovery_plain', $recoveryPlain);

        try {
            $settings = app(\App\Settings\GeneralSettings::class);
            $issuer = $settings->agency_name ?? config('app.name');
        } catch (\Throwable) {
            $issuer = config('app.name');
        }

        $otpUrl = $this->twoFactor->getOtpAuthUrl($secret, $user->email, $issuer);
        $qrDataUri = $this->twoFactor->getQrCodeDataUri($otpUrl);

        if ($request->expectsJson()) {
            return response()->json([
                'qr_data_uri' => $qrDataUri,
                'secret' => $secret,
            ]);
        }

        return back()
            ->with('two_factor_qr', $qrDataUri)
            ->with('two_factor_secret_display', $secret)
            ->with('two_factor_recovery_display', $recoveryPlain);
    }

    /**
     * Confirma el código TOTP y activa 2FA.
     */
    public function confirm(Request $request)
    {
        if (! $this->twoFactor->isSystemEnabled()) {
            return back()->with('error', 'La verificación en dos pasos está desactivada por el administrador del sistema.');
        }

        $clean = preg_replace('/\D/', '', (string) $request->input('code'));
        $request->merge(['code' => $clean]);
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasTwoFactorEnabled()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'El 2FA ya está activo.'], 422);
            }

            return back()->with('error', 'El 2FA ya está activo.');
        }

        $encrypted = $request->session()->get('two_factor_setup.secret');
        $recoveryPlain = $request->session()->get('two_factor_setup.recovery_plain');

        if (! $encrypted || ! is_array($recoveryPlain)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesión de configuración caducada. Abre el asistente de nuevo.',
                ], 422);
            }

            return back()->with('error', 'Sesión de configuración caducada. Pulsa «Iniciar configuración» de nuevo.');
        }

        $secret = Crypt::decryptString($encrypted);

        if (! $this->twoFactor->verifyTotp($secret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => ['El código de 6 dígitos no es válido.'],
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $this->twoFactor->hashRecoveryCodes($recoveryPlain),
        ])->save();

        $request->session()->forget(['two_factor_setup.secret', 'two_factor_setup.recovery_plain']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Verificación en dos pasos activada correctamente.',
                'recovery_codes' => $recoveryPlain,
            ]);
        }

        return back()->with('success', 'Verificación en dos pasos activada correctamente. Guarda tus códigos de respaldo en un lugar seguro.');
    }

    /**
     * Desactiva 2FA (requiere contraseña actual).
     */
    public function disable(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->hasTwoFactorEnabled()) {
            return back()->with('error', 'No tienes el 2FA activado.');
        }

        if (! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña no es correcta.'],
            ]);
        }

        $this->twoFactor->disableTwoFactor($user->fresh());

        $request->session()->forget(['two_factor_setup.secret', 'two_factor_setup.recovery_plain']);

        return back()->with('success', 'Verificación en dos pasos desactivada.');
    }

    /**
     * Regenera códigos de respaldo (invalida los anteriores).
     */
    public function regenerateRecovery(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->hasTwoFactorEnabled()) {
            return back()->with('error', 'Activa primero el 2FA.');
        }

        if (! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña no es correcta.'],
            ]);
        }

        $plain = $this->twoFactor->generateRecoveryCodesPlain();
        $user->forceFill([
            'two_factor_recovery_codes' => $this->twoFactor->hashRecoveryCodes($plain),
        ])->save();

        return back()
            ->with('success', 'Se generaron nuevos códigos de respaldo. Los anteriores ya no son válidos.')
            ->with('two_factor_recovery_display', $plain);
    }

    /**
     * Cancela la configuración pendiente (antes de confirmar).
     */
    public function cancelSetup(Request $request)
    {
        $request->session()->forget(['two_factor_setup.secret', 'two_factor_setup.recovery_plain']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Configuración de 2FA cancelada.');
    }
}
