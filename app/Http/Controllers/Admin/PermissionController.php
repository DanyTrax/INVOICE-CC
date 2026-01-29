<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleHierarchy;
use App\Models\RolePermission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        if (!Auth::user() || !Auth::user()->hasRole('super_admin')) {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        // No incluimos el rol client porque usa otro panel (portal) y no requiere permisos aquí
        $roles = Role::where('name', '!=', 'client')
            ->orderBy('name')
            ->get();
        $modules = PermissionService::getModules();
        $actions = PermissionService::getActions();

        // Si no hay registros aún, rellenar con los permisos actuales por defecto
        if (RolePermission::count() === 0 && RoleHierarchy::count() === 0) {
            $this->seedDefaultPermissions($roles, $modules, $actions);
        }

        // Obtener permisos existentes
        $permissions = RolePermission::with('role')->get()->groupBy('role_id');
        
        // Obtener jerarquía de roles
        $hierarchy = RoleHierarchy::with('role')->get()->groupBy('role_id');

        return view('admin.permissions.index', compact(
            'roles',
            'modules',
            'actions',
            'permissions',
            'hierarchy'
        ));
    }

    public function updatePermissions(Request $request)
    {
        $this->ensureSuperAdmin();

        // Los checkboxes desmarcados NO se envían en el POST. Hay que procesar TODAS las
        // combinaciones rol+módulo+acción y marcar enabled=false para las que no vengan.
        $roles = Role::where('name', '!=', 'client')
            ->where('name', '!=', 'super_admin')
            ->orderBy('name')
            ->get();
        $modules = PermissionService::getModules();
        $actions = PermissionService::getActions();

        foreach ($roles as $role) {
            foreach ($modules as $moduleKey => $moduleLabel) {
                foreach ($actions as $actionKey => $actionLabel) {
                    $key = "{$role->id}_{$moduleKey}_{$actionKey}";
                    $enabled = isset($request->permissions[$key]['enabled'])
                        && (string) $request->permissions[$key]['enabled'] === '1';

                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $moduleKey,
                            'action' => $actionKey,
                        ],
                        [
                            'enabled' => $enabled,
                        ]
                    );
                }
            }
        }

        // Limpiar caché para que el menú refleje los desmarcados de inmediato
        app(PermissionService::class)->clearCache();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permisos actualizados correctamente.')
            ->withHeaders(['Cache-Control' => 'no-store, no-cache, must-revalidate']);
    }

    public function updateHierarchy(Request $request)
    {
        $this->ensureSuperAdmin();

        // Limpiar jerarquía existente (excepto super_admin)
        RoleHierarchy::whereHas('role', function ($q) {
            $q->where('name', '!=', 'super_admin');
        })->delete();

        // Procesar datos del formulario
        $hierarchyData = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'hierarchy[') && str_ends_with($key, '][can_create]')) {
                // Extraer el prefijo: "hierarchy[1_panel_user][can_create]" -> "1_panel_user"
                $prefix = str_replace(['hierarchy[', '][can_create]'], '', $key);
                
                // Separar role_id y can_create_role
                // El formato es "roleId_roleName", pero el roleName puede tener guiones bajos
                // Necesitamos encontrar el primer número (role_id) y el resto es el nombre del rol
                if (preg_match('/^(\d+)_(.+)$/', $prefix, $matches)) {
                    $roleId = $matches[1];
                    $canCreateRole = $matches[2];
                    
                    $canView = $request->input("hierarchy[{$prefix}][can_view]", false);
                    
                    if ($value == '1') {
                        $hierarchyData[] = [
                            'role_id' => $roleId,
                            'can_create_role' => $canCreateRole,
                            'can_view' => $canView == '1',
                        ];
                    }
                }
            }
        }

        foreach ($hierarchyData as $hier) {
            RoleHierarchy::create($hier);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Jerarquía de roles actualizada correctamente.');
    }

    /**
     * Rellenar permisos y jerarquía con la configuración actual por defecto.
     *
     * - super_admin: tiene todos los permisos de forma implícita (no se crean filas).
     * - panel_user: acceso completo a todos los módulos excepto Backups.
     * - agent: acceso completo a módulos principales; no a Backups.
     * - Jerarquía de roles según reglas actuales:
     *   * super_admin: puede crear/ver todos (implícito).
     *   * panel_user: puede crear/ver panel_user, agent, client.
     *   * agent: puede crear/ver solo client.
     */
    protected function seedDefaultPermissions($roles, $modules, $actions): void
    {
        $roleByName = $roles->keyBy('name');

        // Helper para crear permisos por módulo/acción para un rol
        $createModulePermissions = function (Role $role, array $allowedModules, array $deniedModules = []) use ($modules, $actions) {
            foreach ($modules as $moduleKey => $moduleLabel) {
                // Si el módulo está explícitamente denegado, saltar
                if (in_array($moduleKey, $deniedModules, true)) {
                    continue;
                }
                // Si se pasó una lista de permitidos y este no está, saltar
                if (!empty($allowedModules) && !in_array($moduleKey, $allowedModules, true)) {
                    continue;
                }

                foreach ($actions as $actionKey => $actionLabel) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $moduleKey,
                            'action' => $actionKey,
                        ],
                        [
                            'enabled' => true,
                        ]
                    );
                }
            }
        };

        // panel_user: acceso completo excepto Backups
        if ($roleByName->has('panel_user')) {
            $panelUser = $roleByName->get('panel_user');
            $createModulePermissions($panelUser, [], ['backups']);

            // Jerarquía: puede crear/ver panel_user, agent, client
            foreach (['panel_user', 'agent', 'client'] as $targetRole) {
                RoleHierarchy::firstOrCreate([
                    'role_id' => $panelUser->id,
                    'can_create_role' => $targetRole,
                ], [
                    'can_view' => true,
                ]);
            }
        }

        // agent: acceso completo a módulos principales; no Backups
        if ($roleByName->has('agent')) {
            $agent = $roleByName->get('agent');

            // Módulos principales sin backups
            $createModulePermissions($agent, [], ['backups']);

            // Jerarquía: puede crear/ver solo client (regla actual)
            RoleHierarchy::firstOrCreate([
                'role_id' => $agent->id,
                'can_create_role' => 'client',
            ], [
                'can_view' => true,
            ]);
        }

        // client: normalmente no accede al panel admin, no se configuran permisos aquí.
    }
}
