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
     * Módulos disponibles en el sistema.
     */
    public static function getModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'companies' => 'Empresas',
            'registrations' => 'Expedientes',
            'users' => 'Usuarios',
            // Configuración por secciones
            'settings_agency' => 'Config: Datos Empresa',
            'settings_drive' => 'Config: Conexión Drive',
            'settings_drive_operations_log' => 'Config: Historial Operaciones Drive',
            'settings_mail' => 'Config: Correo',
            'settings_templates' => 'Config: Plantillas',
            'settings_history' => 'Config: Históricos',
            'settings_system' => 'Config: Sistema',
            'backups' => 'Backups',
            'permissions' => 'Gestión de Permisos',
        ];
    }

    /**
     * Acciones disponibles por módulo.
     */
    public static function getActions(): array
    {
        return [
            // Por ahora solo usamos 'view' para mostrar/ocultar módulos.
            'view' => 'Ver',
        ];
    }

    /**
     * Verificar si un rol tiene permiso para una acción en un módulo.
     * Sin caché: marcar/desmarcar en Permisos se refleja de inmediato en sidebar y tabs.
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
     * Verificar si el usuario autenticado tiene permiso.
     */
    public function userHasPermission(string $module, string $action): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Super admin tiene todos los permisos
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

        // Super admin puede crear todos
        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_create_role', '!=', null)
            ->pluck('can_create_role')
            ->toArray();
        // Un rol siempre puede crear usuarios de su mismo tipo (p. ej. admin crea admin)
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

        // Super admin puede ver todos
        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_view', true)
            ->pluck('can_create_role')
            ->toArray();
        // Un rol siempre puede ver usuarios de su mismo tipo (p. ej. admin ve admin)
        if (!in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }
        return $fromHierarchy;
    }

    /**
     * Limpiar caché de permisos para que el menú refleje al desmarcar.
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
