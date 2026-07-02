<?php

namespace App\Services;

use App\Models\AppErrorLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class AppErrorLogService
{
    /**
     * Evita registrar el mismo error en cascada (por ejemplo si falla la propia escritura).
     */
    private static bool $recording = false;

    /**
     * Registra una excepción en la tabla app_error_logs si es un fallo relevante.
     */
    public function record(Throwable $e): void
    {
        if (self::$recording || ! $this->shouldRecord($e)) {
            return;
        }

        self::$recording = true;

        try {
            $request = request();
            $user = $request && method_exists($request, 'user') ? $request->user() : null;
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            AppErrorLog::create([
                'level' => 'error',
                'exception_class' => get_class($e),
                'message' => \Illuminate\Support\Str::limit($e->getMessage() ?: '(sin mensaje)', 60000, ''),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'status_code' => $status,
                'user_id' => $user?->id,
                'user_name' => $user ? trim(($user->name ?? '').' <'.($user->email ?? '').'>') : null,
                'ip' => $request?->ip(),
                'trace' => \Illuminate\Support\Str::limit($e->getTraceAsString(), 60000, ''),
            ]);
        } catch (Throwable $inner) {
            // No propagar: registrar en el log de archivo como último recurso.
            try {
                logger()->error('No se pudo guardar AppErrorLog: '.$inner->getMessage());
            } catch (Throwable $ignored) {
                // Silencio total para no romper el manejo de errores.
            }
        } finally {
            self::$recording = false;
        }
    }

    /**
     * Solo registramos fallos de servidor reales; ignoramos validaciones, 404, auth, CSRF, etc.
     */
    private function shouldRecord(Throwable $e): bool
    {
        $ignored = [
            ValidationException::class,
            AuthenticationException::class,
            AuthorizationException::class,
            TokenMismatchException::class,
            ModelNotFoundException::class,
            NotFoundHttpException::class,
        ];

        foreach ($ignored as $class) {
            if ($e instanceof $class) {
                return false;
            }
        }

        // Errores HTTP con código < 500 (403, 404, 419, etc.) no se registran.
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return false;
        }

        return true;
    }
}
