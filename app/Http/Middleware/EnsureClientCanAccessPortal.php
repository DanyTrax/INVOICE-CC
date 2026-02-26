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
        if ($request->routeIs('portal.account-disabled')) {
            return $next($request);
        }
        if (!$user->canAccessPortal()) {
            return redirect()->route('portal.account-disabled');
        }

        return $next($request);
    }
}
