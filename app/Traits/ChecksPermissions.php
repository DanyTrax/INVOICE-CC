<?php

namespace App\Traits;

use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;

trait ChecksPermissions
{
    /**
     * Verificar si el usuario tiene permiso para una acción en un módulo.
     */
    protected function checkPermission(string $module, string $action): bool
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->userHasPermission($module, $action);
    }

    /**
     * Abortar si el usuario no tiene permiso.
     */
    protected function requirePermission(string $module, string $action): void
    {
        if (!$this->checkPermission($module, $action)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }
    }

    /**
     * Redirigir si el usuario no tiene permiso.
     */
    protected function redirectIfNoPermission(string $module, string $action): ?RedirectResponse
    {
        if (!$this->checkPermission($module, $action)) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return null;
    }
}
