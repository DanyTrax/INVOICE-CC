<?php

namespace App\Services;

use App\Models\RoleHierarchy;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class PermissionService
{
    /** Valor especial en jerarquía: usuarios sin rol asignado (crear/ver/editar). */
    public const NO_ROLE = 'no_role';

    /**
     * Acción solo para expedientes: registrar en línea de tiempo sin editar/borrar registros existentes.
     */
    public const ACTION_TIMELINE_FEED = 'timeline_feed';

    /**
     * Módulos disponibles en el sistema (panel admin).
     *
     * @return array<string, string>
     */
    public static function getModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'companies' => 'Empresas',
            'quotes' => 'Cotizaciones',
            'proposals' => 'Propuestas',
            'concept_catalogs' => 'Conceptos (catálogo)',
            'service_types' => 'Trámite (tipos de servicio)',
            'services' => 'Servicios (catálogo)',
            'processes' => 'Expedientes INVIMA',
            'capacitaciones' => 'Capacitaciones',
            'users' => 'Directorio (clientes, agentes, usuarios)',
            'settings_agency' => 'Config: datos empresa',
            'settings_drive' => 'Config: conexión Drive',
            'settings_drive_operations_log' => 'Config: historial operaciones Drive',
            'settings_mail' => 'Config: correo',
            'settings_templates' => 'Config: plantillas',
            'settings_history' => 'Config: históricos',
            'settings_system' => 'Config: sistema',
            'backups' => 'Backups',
            'permissions' => 'Gestión de permisos y roles',
            'activity_logs' => 'Registros de actividad',
        ];
    }

    /**
     * Acciones estándar (todos los módulos excepto variaciones en expedientes).
     *
     * @return array<string, string>
     */
    public static function getStandardActions(): array
    {
        return [
            'view' => 'Ver',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
        ];
    }

    /**
     * Acciones por módulo (expedientes incluye alimentar línea de tiempo).
     *
     * @return array<string, string>
     */
    public static function getActionsForModule(string $moduleKey): array
    {
        if ($moduleKey === 'processes') {
            return [
                'view' => 'Ver',
                self::ACTION_TIMELINE_FEED => 'Alimentar línea de tiempo (sin editar/borrar)',
                'edit' => 'Editar',
                'delete' => 'Eliminar',
            ];
        }

        return self::getStandardActions();
    }

    /**
     * @deprecated Usar getStandardActions / getActionsForModule
     */
    public static function getActions(): array
    {
        return array_merge(self::getStandardActions(), [
            self::ACTION_TIMELINE_FEED => 'Alimentar línea de tiempo',
        ]);
    }

    /**
     * Verificar si un rol tiene permiso para una acción en un módulo.
     */
    public function hasPermission(string $roleName, string $module, string $action): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return false;
        }

        if ($roleName === 'super_admin') {
            return true;
        }

        $permission = RolePermission::where('role_id', $role->id)
            ->where('module', $module)
            ->where('action', $action)
            ->first();

        return $permission && $permission->enabled;
    }

    /**
     * Permisos de expediente con jerarquía: edit/delete implican capacidades inferiores para rutas.
     */
    public function userHasProcessAction(string $needed): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($needed === 'view') {
            return $this->userHasPermission('processes', 'view');
        }

        if ($needed === self::ACTION_TIMELINE_FEED) {
            return $this->userHasPermission('processes', self::ACTION_TIMELINE_FEED)
                || $this->userHasPermission('processes', 'edit')
                || $this->userHasPermission('processes', 'delete');
        }

        if ($needed === 'edit') {
            return $this->userHasPermission('processes', 'edit')
                || $this->userHasPermission('processes', 'delete');
        }

        if ($needed === 'delete') {
            return $this->userHasPermission('processes', 'delete');
        }

        return false;
    }

    /**
     * Verificar si el usuario autenticado tiene permiso.
     */
    public function userHasPermission(string $module, string $action): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        foreach ($user->roles as $role) {
            if ($this->hasPermission($role->name, $module, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener roles que un rol puede crear.
     */
    public function getRolesCanCreate(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_create_role', '!=', null)
            ->pluck('can_create_role')
            ->toArray();
        if (!in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    /**
     * Obtener roles que un rol puede ver.
     */
    public function getRolesCanView(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_view', true)
            ->pluck('can_create_role')
            ->toArray();
        if (!in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    /**
     * Obtener roles que un rol puede editar.
     */
    public function getRolesCanEdit(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return array_merge(Role::pluck('name')->toArray(), [self::NO_ROLE]);
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_edit', true)
            ->pluck('can_create_role')
            ->toArray();
        if (!in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    public function clearCache(): void
    {
        Cache::flush();
    }
}
