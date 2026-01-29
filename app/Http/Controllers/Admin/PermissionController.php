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
    /** Solo quien tiene permiso "Gestión de Permisos" puede acceder (super_admin lo tiene por defecto). */
    protected function ensureCanManagePermissions(): void
    {
        $user = Auth::user();
        if (!$user || !app(PermissionService::class)->userHasPermission('permissions', 'view')) {
            abort(403, 'No tienes permiso para gestionar permisos.');
        }
    }

    public function index()
    {
        $this->ensureCanManagePermissions();

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
        $this->ensureCanManagePermissions();

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
        $this->ensureCanManagePermissions();

        // Limpiar jerarquía existente (excepto super_admin)
        RoleHierarchy::whereHas('role', function ($q) {
            $q->where('name', '!=', 'super_admin');
        })->delete();

        // Procesar datos del formulario (Laravel agrupa como hierarchy[prefix][campo])
        $hierarchyData = [];
        $hierarchyInput = $request->input('hierarchy', []);
        if (!is_array($hierarchyInput)) {
            $hierarchyInput = [];
        }
        foreach ($hierarchyInput as $prefix => $data) {
            if (!is_array($data) || empty($data['can_create']) || (string) $data['can_create'] !== '1') {
                continue;
            }
            // Prefijo = "roleId_roleName" (ej. 1_panel_user, 2_no_role)
            if (preg_match('/^(\d+)_(.+)$/', $prefix, $matches)) {
                $roleId = (int) $matches[1];
                $canCreateRole = $matches[2];
                $canView = !empty($data['can_view']) && (string) $data['can_view'] === '1';
                $hierarchyData[] = [
                    'role_id' => $roleId,
                    'can_create_role' => $canCreateRole,
                    'can_view' => $canView,
                ];
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

        // panel_user: acceso completo excepto Backups y Gestión de Permisos
        if ($roleByName->has('panel_user')) {
            $panelUser = $roleByName->get('panel_user');
            $createModulePermissions($panelUser, [], ['backups', 'permissions']);

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

        // agent: acceso completo a módulos principales; no Backups ni Gestión de Permisos
        if ($roleByName->has('agent')) {
            $agent = $roleByName->get('agent');
            $createModulePermissions($agent, [], ['backups', 'permissions']);

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
