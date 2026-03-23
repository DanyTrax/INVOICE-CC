<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registra en activity_logs las peticiones mutadoras del panel admin (POST, PUT, PATCH, DELETE)
 * con respuesta exitosa (2xx/3xx), para auditoría sin duplicar lógica en cada controlador.
 */
class LogAdminMutationActivity
{
    protected const MUTATING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldLog($request, $response)) {
            return $response;
        }

        try {
            app(ActivityLogService::class)->logAdminMutation($request, $response);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar actividad: '.$e->getMessage(), [
                'route' => $request->route()?->getName(),
            ]);
        }

        return $response;
    }

    protected function shouldLog(Request $request, Response $response): bool
    {
        if (! $request->user()) {
            return false;
        }

        $route = $request->route();
        if (! $route || ! str_starts_with((string) $route->getName(), 'admin.')) {
            return false;
        }

        if (! in_array($request->method(), self::MUTATING, true)) {
            return false;
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            return false;
        }

        // Evitar ruido: solo lectura de registros (GET) no pasa por aquí
        return true;
    }
}
