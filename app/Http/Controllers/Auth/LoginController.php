<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\LoginLockoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login-flowbite');
    }

    public function login(Request $request, LoginLockoutService $lockoutService)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $lockoutService->assertNotBlocked($request);

        $remember = $request->boolean('remember');

        $email = (string) $request->input('email');

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            $lockoutService->recordFailedAttempt($request, $email);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        if (! $user->is_active) {
            $lockoutService->recordFailedAttempt($request, $email);
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta está desactivada.'],
            ]);
        }

        if ($user->hasRole('client')) {
            $lockoutService->recordFailedAttempt($request, $email);
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta no tiene acceso al panel administrativo.'],
            ]);
        }

        $lockoutService->clearForSuccessfulLogin($request);

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('two_factor_login.id', $user->id);
            $request->session()->put('two_factor_login.remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        app(ActivityLogService::class)->log('login', 'Inicio de sesión en el sistema');
        $default = route('admin.dashboard');

        return redirect()->intended($default);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            app(ActivityLogService::class)->log('logout', 'Cierre de sesión');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
