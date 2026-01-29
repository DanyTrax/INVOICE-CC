<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminCache
{
    /**
     * Evita que el navegador cachee el HTML del panel admin,
     * para que al desmarcar permisos el menú se actualice al refrescar.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');

        return $response;
    }
}
