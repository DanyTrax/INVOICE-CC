<?php

declare(strict_types=1);

use App\Models\RolePermission;
use App\Services\PermissionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Rellena la matriz de permisos (view/edit/delete/timeline_feed) sin romper instalaciones existentes:
 * - admin y agent: todo habilitado salvo backups y permisos (como el seeder original).
 * - Otros roles: se parte de los permisos ya guardados; si un módulo tenía acceso (view u otro),
 *   se habilitan todas las acciones actuales de ese módulo; módulos nuevos (cotizaciones, etc.) quedan en false
 *   salvo equivalencias (registrations → processes).
 *
 * Idempotente: si ya existen filas con action=timeline_feed, no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions') || !Schema::hasTable('roles')) {
            return;
        }

        if (RolePermission::where('action', PermissionService::ACTION_TIMELINE_FEED)->exists()) {
            return;
        }

        $modules = PermissionService::getModules();
        $deniedForAdminAgent = ['backups', 'permissions'];

        $roles = Role::whereNotIn('name', ['client', 'super_admin'])->orderBy('name')->get();

        foreach ($roles as $role) {
            if (in_array($role->name, ['admin', 'agent'], true)) {
                foreach ($modules as $moduleKey => $_label) {
                    if (in_array($moduleKey, $deniedForAdminAgent, true)) {
                        continue;
                    }
                    foreach (PermissionService::getActionsForModule($moduleKey) as $actionKey => $_a) {
                        RolePermission::updateOrCreate(
                            [
                                'role_id' => $role->id,
                                'module' => $moduleKey,
                                'action' => $actionKey,
                            ],
                            ['enabled' => true]
                        );
                    }
                }

                continue;
            }

            // Roles personalizados: inferir desde filas existentes (sistema anterior = sobre todo "view" por módulo)
            $snapshot = RolePermission::where('role_id', $role->id)->get();

            $hadModuleAccess = [];
            foreach ($snapshot as $perm) {
                if (!$perm->enabled) {
                    continue;
                }
                $mod = $perm->module;
                if ($mod === 'registrations') {
                    $mod = 'processes';
                }
                $hadModuleAccess[$mod] = true;
            }

            foreach ($modules as $moduleKey => $_label) {
                $hadAccess = (bool) ($hadModuleAccess[$moduleKey] ?? false);
                if ($moduleKey === 'processes') {
                    $hadAccess = $hadAccess
                        || ($hadModuleAccess['registrations'] ?? false)
                        || ($hadModuleAccess['processes'] ?? false);
                }

                foreach (PermissionService::getActionsForModule($moduleKey) as $actionKey => $_a) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $moduleKey,
                            'action' => $actionKey,
                        ],
                        ['enabled' => $hadAccess]
                    );
                }
            }
        }

        // Limpiar clave de módulo obsoleto (si existía)
        RolePermission::where('module', 'registrations')->delete();
    }

    public function down(): void
    {
        // No revertir: los permisos son datos de negocio
    }
};
