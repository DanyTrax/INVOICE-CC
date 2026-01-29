<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Mapa de rutas admin a (módulo, acción).
     */
    protected function getModuleForRoute(string $routeName, Request $request): ?array
    {
        if (str_starts_with($routeName, 'admin.dashboard')) {
            return ['dashboard', 'view'];
        }
        if (str_starts_with($routeName, 'admin.companies')) {
            return ['companies', 'view'];
        }
        if (str_starts_with($routeName, 'admin.registrations')) {
            return ['registrations', 'view'];
        }
        if (str_starts_with($routeName, 'admin.clients') || str_starts_with($routeName, 'admin.agents') || str_starts_with($routeName, 'admin.users')) {
            return ['users', 'view'];
        }
        if (str_starts_with($routeName, 'admin.settings')) {
            $section = $request->route('section', 'agency');
            $moduleMap = [
                'agency' => 'settings_agency',
                'drive' => 'settings_drive',
                'mail' => 'settings_mail',
                'templates' => 'settings_templates',
                'history' => 'settings_history',
                'system' => 'settings_system',
            ];
            $module = $moduleMap[$section] ?? 'settings_agency';
            return [$module, 'view'];
        }
        if (str_starts_with($routeName, 'admin.backups') || str_starts_with($routeName, 'admin.permissions')) {
            // Solo super_admin; el controlador ya lo hace
            return null;
        }
        // profile y otras: permitir
        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // super_admin no se restringe por permisos granulares
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return $next($request);
        }

        $permission = $this->getModuleForRoute($routeName, $request);
        if ($permission === null) {
            return $next($request);
        }

        [$module, $action] = $permission;
        $service = app(PermissionService::class);
        if (!$service->userHasPermission($module, $action)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
