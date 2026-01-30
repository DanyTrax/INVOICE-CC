<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            app(ActivityLogService::class)->log('login', 'Inicio de sesión en el sistema');
            $default = Auth::user()->hasRole('client')
                ? route('portal.dashboard')
                : route('admin.dashboard');
            return redirect()->intended($default);
        }

        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas no son correctas.'],
        ]);
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
