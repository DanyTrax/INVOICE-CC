<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::broker()->sendResetLink($request->only('email'));

        // Mensaje genérico (no revelar si el correo existe)
        return back()->with('status', 'Si existe una cuenta con ese correo, te enviamos un enlace para restablecer la contraseña.');
    }
}
