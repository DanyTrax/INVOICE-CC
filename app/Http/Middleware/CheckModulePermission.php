<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        $method = strtoupper($request->method());
        $permission = $this->resolvePermission($routeName, $method, $request);

        if ($permission === null) {
            return $next($request);
        }

        [$module, $action] = $permission;
        if (! app(PermissionService::class)->userHasPermission($module, $action)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function resolvePermission(string $routeName, string $method, Request $request): ?array
    {
        if (str_starts_with($routeName, 'admin.profile') || $routeName === 'admin.preferences.theme' || $routeName === 'admin.preferences.font-scale') {
            return null;
        }

        if ($routeName === 'admin.dashboard') {
            return ['dashboard', 'view'];
        }

        if (str_starts_with($routeName, 'admin.brand-settings')) {
            return $this->crudModule($routeName, $method, 'settings_brand');
        }

        if (str_starts_with($routeName, 'admin.associates')) {
            return $this->crudModule($routeName, $method, 'associates');
        }

        if (str_starts_with($routeName, 'admin.concepts')) {
            return $this->crudModule($routeName, $method, 'concepts');
        }

        if (str_starts_with($routeName, 'admin.invoices')) {
            if ($routeName === 'admin.invoices.send' || $routeName === 'admin.invoices.mark-paid') {
                return ['invoices', 'edit'];
            }

            return $this->crudModule($routeName, $method, 'invoices');
        }

        if (str_starts_with($routeName, 'admin.users') || str_starts_with($routeName, 'admin.agents')) {
            return $this->crudModule($routeName, $method, 'users');
        }

        if (str_starts_with($routeName, 'admin.settings')) {
            return $this->resolveSettingsPermission($routeName, $method, $request);
        }

        if (str_starts_with($routeName, 'admin.backups')) {
            if ($method === 'DELETE' || $routeName === 'admin.backups.wipe') {
                return ['backups', 'delete'];
            }
            if ($method === 'POST') {
                return ['backups', 'edit'];
            }

            return ['backups', 'view'];
        }

        if (str_starts_with($routeName, 'admin.permissions') || str_starts_with($routeName, 'admin.roles.')) {
            return $method === 'GET' ? ['permissions', 'view'] : ['permissions', $method === 'DELETE' ? 'delete' : 'edit'];
        }

        if (str_starts_with($routeName, 'admin.activity-logs')) {
            return $routeName === 'admin.activity-logs.destroy-all' && $method === 'DELETE'
                ? ['activity_logs', 'delete']
                : ['activity_logs', 'view'];
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function crudModule(string $routeName, string $method, string $module): array
    {
        if ($method === 'DELETE') {
            return [$module, 'delete'];
        }
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return [$module, 'edit'];
        }
        if (str_contains($routeName, '.create') || str_contains($routeName, '.edit')) {
            return [$module, 'edit'];
        }

        return [$module, 'view'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveSettingsPermission(string $routeName, string $method, Request $request): array
    {
        if ($routeName === 'admin.settings.update') {
            $section = (string) $request->input('section', 'mail');

            return $this->settingsModuleForSection($section, 'edit');
        }

        if ($routeName === 'admin.settings.section') {
            return $this->settingsModuleForSection((string) $request->route('section', 'mail'), 'view');
        }

        if (str_starts_with($routeName, 'admin.settings.zoho.')) {
            return ['settings_mail', $method === 'GET' ? 'view' : 'edit'];
        }

        if ($routeName === 'admin.settings.email-logs.destroy') {
            return ['settings_mail', 'delete'];
        }

        if ($routeName === 'admin.settings.email-logs.show' || $routeName === 'admin.settings.templates.show') {
            return ['settings_templates', 'view'];
        }

        if (in_array($routeName, ['admin.settings.git-pull', 'admin.settings.artisan', 'admin.settings.maintenance-cli', 'admin.settings.delete-user-by-email', 'admin.settings.login-lockouts.unlock'], true)) {
            return ['settings_system', 'edit'];
        }

        if (str_starts_with($routeName, 'admin.settings.error-logs')) {
            return ['settings_system', $method === 'DELETE' ? 'delete' : 'edit'];
        }

        return ['settings_mail', 'view'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function settingsModuleForSection(string $section, string $action): array
    {
        $map = [
            'mail' => 'settings_mail',
            'templates' => 'settings_templates',
            'history' => 'settings_history',
            'system' => 'settings_system',
            'legal-policies' => 'settings_system',
            'login-lockouts' => 'settings_system',
        ];

        return [$map[$section] ?? 'settings_mail', $action];
    }
}
