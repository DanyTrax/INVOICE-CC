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
        $service = app(PermissionService::class);

        if ($module === 'processes') {
            // Subir a Drive: alimentar línea de tiempo O editar expedientes (gestión documental sin timeline_feed).
            if ($routeName === 'admin.processes.documents.upload' && $method === 'POST') {
                $canTimeline = $service->userHasProcessAction(PermissionService::ACTION_TIMELINE_FEED);
                $canEdit = $service->userHasProcessAction('edit');
                if (! $canTimeline && ! $canEdit) {
                    abort(403, 'No tienes permiso para realizar esta acción en expedientes.');
                }

                return $next($request);
            }

            // Actualizar ítem de checklist: edit O alimentar línea de tiempo (misma lógica que gestión con asignación).
            if ($routeName === 'admin.checklist-items.update' && in_array($method, ['PUT', 'PATCH'], true)) {
                $canTimeline = $service->userHasProcessAction(PermissionService::ACTION_TIMELINE_FEED);
                $canEdit = $service->userHasProcessAction('edit');
                if (! $canTimeline && ! $canEdit) {
                    abort(403, 'No tienes permiso para realizar esta acción en expedientes.');
                }

                return $next($request);
            }

            if (! $service->userHasProcessAction($action)) {
                abort(403, 'No tienes permiso para realizar esta acción en expedientes.');
            }

            return $next($request);
        }

        if (str_contains($module, '|')) {
            $modules = explode('|', $module);
            $hasAny = false;
            foreach ($modules as $m) {
                if ($service->userHasPermission(trim($m), $action)) {
                    $hasAny = true;
                    break;
                }
            }
            if (! $hasAny) {
                abort(403, 'No tienes permiso para acceder a este módulo.');
            }

            return $next($request);
        }

        if (! $service->userHasPermission($module, $action)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }

    /**
     * @return array{0: string, 1: string}|null null = sin restricción por permiso granular
     */
    protected function resolvePermission(string $routeName, string $method, Request $request): ?array
    {
        // Perfil y rutas auxiliares
        if (str_starts_with($routeName, 'admin.profile')) {
            return null;
        }

        if ($routeName === 'admin.preferences.theme') {
            return null;
        }

        if ($routeName === 'admin.dashboard') {
            return ['dashboard', 'view'];
        }

        if ($routeName === 'admin.api.companies.search') {
            return ['companies', 'view'];
        }

        // Empresas
        if (str_starts_with($routeName, 'admin.companies')) {
            return $this->crudModule($routeName, $method, 'companies');
        }

        if ($routeName === 'admin.company-invites.resend' || $routeName === 'admin.company-invites.destroy') {
            return ['companies', 'edit'];
        }

        // Cotizaciones
        if (str_starts_with($routeName, 'admin.quotes')) {
            // Ver detalle y PDF: ver cotizaciones o ver propuestas
            if ($method === 'GET' && in_array($routeName, ['admin.quotes.show', 'admin.quotes.pdf'], true)) {
                return ['quotes|proposals', 'view'];
            }

            return $this->crudModule($routeName, $method, 'quotes');
        }

        // Propuestas
        if (str_starts_with($routeName, 'admin.proposals')) {
            return $this->crudModule($routeName, $method, 'proposals');
        }

        // Conceptos
        if (str_starts_with($routeName, 'admin.concept-catalogs')) {
            return $this->crudModule($routeName, $method, 'concept_catalogs');
        }

        // Trámite (tipos)
        if (str_starts_with($routeName, 'admin.service-types')) {
            return $this->crudModule($routeName, $method, 'service_types');
        }

        // Servicios catálogo
        if (str_starts_with($routeName, 'admin.services')) {
            return $this->crudModule($routeName, $method, 'services');
        }

        // Expedientes / submissions / eventos / checklist / documentos
        if ($this->isProcessFamilyRoute($routeName)) {
            return $this->processPermission($routeName, $method);
        }

        // Capacitaciones
        if (str_starts_with($routeName, 'admin.capacitaciones')) {
            return $this->crudModule($routeName, $method, 'capacitaciones');
        }

        // Usuarios / clientes / agentes
        if (str_starts_with($routeName, 'admin.clients')
            || str_starts_with($routeName, 'admin.agents')
            || str_starts_with($routeName, 'admin.users')) {
            return $this->crudModule($routeName, $method, 'users');
        }

        // Settings (plantillas PDF bajo settings)
        if (str_starts_with($routeName, 'admin.settings.quote-pdf-templates')
            || str_starts_with($routeName, 'admin.settings.proposal-pdf-templates')) {
            return $this->settingsModuleForRoute($routeName, $method, 'settings_templates');
        }

        if (str_starts_with($routeName, 'admin.settings')) {
            return $this->resolveSettingsPermission($routeName, $method, $request);
        }

        // Backups
        if (str_starts_with($routeName, 'admin.backups')) {
            if ($method === 'DELETE' || $routeName === 'admin.backups.wipe') {
                return ['backups', 'delete'];
            }
            if (in_array($method, ['POST'], true)) {
                return ['backups', 'edit'];
            }

            return ['backups', 'view'];
        }

        // Permisos (gestión)
        if (str_starts_with($routeName, 'admin.permissions')) {
            if ($method === 'GET') {
                return ['permissions', 'view'];
            }

            return ['permissions', 'edit'];
        }

        // Roles (crear / eliminar) — mismo módulo que permisos
        if (str_starts_with($routeName, 'admin.roles.')) {
            if ($method === 'DELETE') {
                return ['permissions', 'delete'];
            }

            return ['permissions', 'edit'];
        }

        // Activity logs (ver listado / línea de tiempo; borrar todo requiere eliminar)
        if (str_starts_with($routeName, 'admin.activity-logs')) {
            if ($routeName === 'admin.activity-logs.destroy-all' && $method === 'DELETE') {
                return ['activity_logs', 'delete'];
            }

            return ['activity_logs', 'view'];
        }

        return null;
    }

    protected function isProcessFamilyRoute(string $routeName): bool
    {
        return str_starts_with($routeName, 'admin.processes')
            || str_starts_with($routeName, 'admin.submissions')
            || str_starts_with($routeName, 'admin.regulatory-events')
            || str_starts_with($routeName, 'admin.checklist-items')
            || str_starts_with($routeName, 'admin.processes.checklist-items');
    }

    /**
     * @return array{0: 'processes', 1: string}
     */
    protected function processPermission(string $routeName, string $method): array
    {
        // Lectura y pantallas
        if ($method === 'GET') {
            if ($routeName === 'admin.processes.create') {
                return ['processes', 'edit'];
            }

            return ['processes', 'view'];
        }

        // Alimentar línea de tiempo (altas / respuestas nuevas, sin actualizar ni borrar existentes)
        $timelineFeedRoutes = [
            'admin.processes.submissions.store',
            'admin.submissions.register-response',
            'admin.submissions.events.store-auto',
            'admin.submissions.events.store-resolution',
            'admin.processes.checklist-items.store',
            'admin.processes.documents.upload',
        ];
        if (in_array($routeName, $timelineFeedRoutes, true)) {
            return ['processes', PermissionService::ACTION_TIMELINE_FEED];
        }

        // Edición de datos ya existentes o creación del expediente maestro
        $editRoutes = [
            'admin.processes.store',
            'admin.processes.link-to-quote',
            'admin.processes.assignments.update',
            'admin.submissions.update',
            'admin.submissions.update-radicado',
            'admin.submissions.link-quote',
            'admin.regulatory-events.update',
        ];
        if (in_array($routeName, $editRoutes, true)) {
            return ['processes', 'edit'];
        }

        // Actualizar checklist: se resuelve como edit; en handle() se permite también con timeline_feed.
        if ($routeName === 'admin.checklist-items.update' && in_array($method, ['PUT', 'PATCH'], true)) {
            return ['processes', 'edit'];
        }

        // Borrado
        $deleteRoutes = [
            'admin.processes.destroy',
            'admin.submissions.destroy',
            'admin.submissions.destroy-radicado',
            'admin.processes.documents.destroy',
            'admin.regulatory-events.destroy',
        ];
        if (in_array($routeName, $deleteRoutes, true)) {
            return ['processes', 'delete'];
        }

        abort(403, 'Acción no reconocida en expedientes.');
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

        if ($method === 'GET') {
            if (str_contains($routeName, '.create') || str_contains($routeName, '.edit')) {
                return [$module, 'edit'];
            }

            return [$module, 'view'];
        }

        return [$module, 'view'];
    }

    protected function resolveSettingsPermission(string $routeName, string $method, Request $request): array
    {
        if ($routeName === 'admin.settings.index') {
            return ['settings_agency', 'view'];
        }

        if ($routeName === 'admin.settings.git-pull' || $routeName === 'admin.settings.artisan') {
            return ['settings_system', 'edit'];
        }

        if ($routeName === 'admin.settings.delete-user-by-email') {
            return ['settings_system', 'edit'];
        }

        if ($routeName === 'admin.settings.login-lockouts.unlock') {
            return ['settings_system', 'edit'];
        }

        if ($routeName === 'admin.settings.email-logs.show') {
            return ['settings_mail', 'view'];
        }

        if ($routeName === 'admin.settings.email-logs.destroy') {
            return ['settings_mail', 'delete'];
        }

        if ($routeName === 'admin.settings.templates.show') {
            return ['settings_templates', 'view'];
        }

        if (str_starts_with($routeName, 'admin.settings.zoho.') || str_starts_with($routeName, 'admin.settings.drive-oauth.')) {
            return $method === 'GET'
                ? ['settings_drive|settings_drive_operations_log', 'view']
                : ['settings_drive|settings_drive_operations_log', 'edit'];
        }

        if ($routeName === 'admin.settings.test-drive-connection') {
            return ['settings_drive|settings_drive_operations_log', 'edit'];
        }

        if ($method === 'DELETE') {
            if ($routeName === 'admin.settings.drive-operations-log.delete') {
                return ['settings_drive_operations_log', 'delete'];
            }

            return ['settings_system', 'delete'];
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            if ($routeName === 'admin.settings.update') {
                $section = (string) $request->input('section', 'agency');

                return $this->settingsModuleForSection($section, 'edit');
            }
            if (str_contains($routeName, 'drive-oauth') || str_contains($routeName, 'zoho') || str_contains($routeName, 'test-drive')) {
                return ['settings_drive|settings_drive_operations_log', 'edit'];
            }

            return ['settings_agency', 'edit'];
        }

        if ($method === 'GET') {
            if ($routeName === 'admin.settings.drive-operations-log') {
                return ['settings_drive_operations_log', 'view'];
            }

            $section = $request->route('section');
            if ($routeName === 'admin.settings.section' && $section) {
                return $this->settingsModuleForSection((string) $section, 'view');
            }

            return ['settings_agency', 'view'];
        }

        return ['settings_agency', 'view'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function settingsModuleForSection(string $section, string $action): array
    {
        $map = [
            'agency' => 'settings_agency',
            'drive' => 'settings_drive|settings_drive_operations_log',
            'mail' => 'settings_mail',
            'templates' => 'settings_templates',
            'history' => 'settings_history',
            'system' => 'settings_system',
            'quote-pdf' => 'settings_templates',
            'proposal-pdf' => 'settings_templates',
            'legal-policies' => 'settings_agency',
            'login-lockouts' => 'settings_system',
        ];

        $module = $map[$section] ?? 'settings_agency';

        return [$module, $action];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function settingsModuleForRoute(string $routeName, string $method, string $defaultModule): array
    {
        if ($method === 'DELETE') {
            return [$defaultModule, 'delete'];
        }
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return [$defaultModule, 'edit'];
        }

        return [$defaultModule, 'view'];
    }
}
