<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorLoginPending
{
    /**
     * Solo permite acceso si hay un inicio de sesión pendiente de segundo factor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('two_factor_login.id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
