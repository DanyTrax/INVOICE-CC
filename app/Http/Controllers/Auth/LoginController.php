<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->hasRole('client')
                ? redirect()->route('portal.dashboard')
                : redirect()->route('admin.dashboard');
        }

        return view('auth.login-flowbite');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        $user = User::query()->where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta está desactivada.'],
            ]);
        }

        // Cliente explícitamente deshabilitado: no puede entrar. "Pendiente" sí inicia sesión y ve aviso en el portal.
        if ($user->hasRole('client') && $user->client_status === User::CLIENT_STATUS_DESHABILITADO) {
            throw ValidationException::withMessages([
                'email' => ['No tienes acceso al portal con esta cuenta.'],
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('two_factor_login.id', $user->id);
            $request->session()->put('two_factor_login.remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        app(ActivityLogService::class)->log('login', 'Inicio de sesión en el sistema');
        $default = $user->hasRole('client')
            ? route('portal.dashboard')
            : route('admin.dashboard');

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
