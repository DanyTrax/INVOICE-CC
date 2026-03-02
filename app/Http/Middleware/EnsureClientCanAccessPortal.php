<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientCanAccessPortal
{
    /**
     * Si el usuario es cliente y su estado no es "activo", redirigir a la página de cuenta no activada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('client')) {
            return $next($request);
        }

        // Si el cliente YA tiene acceso activo y está en la pantalla de cuenta deshabilitada,
        // redirigirlo automáticamente al dashboard del portal.
        if ($user->canAccessPortal()) {
            if ($request->routeIs('portal.account-disabled')) {
                return redirect()->route('portal.dashboard');
            }
            return $next($request);
        }

        // Si NO tiene acceso activo, permitir solo la vista de cuenta deshabilitada;
        // cualquier otra ruta del portal se redirige a esa pantalla.
        if (!$request->routeIs('portal.account-disabled')) {
            return redirect()->route('portal.account-disabled');
        }

        return $next($request);
    }
}
