<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleHierarchy;
use App\Models\RolePermission;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    protected function ensureCanViewPermissions(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        $s = app(PermissionService::class);
        if ($user->hasRole('super_admin')) {
            return;
        }
        if (!$s->userHasPermission('permissions', 'view') && !$s->userHasPermission('permissions', 'edit')) {
            abort(403, 'No tienes permiso para ver la gestión de permisos.');
        }
    }

    protected function ensureCanEditPermissions(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->hasRole('super_admin')) {
            return;
        }
        if (!app(PermissionService::class)->userHasPermission('permissions', 'edit')) {
            abort(403, 'No tienes permiso para modificar roles o permisos.');
        }
    }

    public function index()
    {
        $this->ensureCanViewPermissions();

        $roles = Role::where('name', '!=', 'client')
            ->orderBy('name')
            ->get();
        $targetRolesForHierarchy = Role::orderBy('name')->get();
        $modules = PermissionService::getModules();

        // Jerarquía y permisos por separado: la migración puede rellenar solo permisos
        if (RoleHierarchy::count() === 0) {
            $this->seedDefaultHierarchy($roles);
        }
        if (RolePermission::count() === 0) {
            $this->seedDefaultPermissions($roles);
        }

        $permissions = RolePermission::with('role')->get()->groupBy('role_id');
        $hierarchy = RoleHierarchy::with('role')->get()->groupBy('role_id');

        return view('admin.permissions.index', compact(
            'roles',
            'targetRolesForHierarchy',
            'modules',
            'permissions',
            'hierarchy'
        ));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->ensureCanEditPermissions();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name'),
            ],
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionService::class)->clearCache();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Rol "'.$validated['name'].'" creado. Asigna sus permisos en la tabla y guarda.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        $this->ensureCanEditPermissions();

        if (in_array($role->name, ['super_admin', 'client'], true)) {
            abort(403, 'Este rol no puede eliminarse.');
        }

        RolePermission::where('role_id', $role->id)->delete();
        RoleHierarchy::where('role_id', $role->id)->delete();
        RoleHierarchy::where('can_create_role', $role->name)->delete();

        $role->delete();

        app(PermissionService::class)->clearCache();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Rol eliminado.');
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $this->ensureCanEditPermissions();

        $roles = Role::where('name', '!=', 'client')
            ->where('name', '!=', 'super_admin')
            ->orderBy('name')
            ->get();
        $modules = PermissionService::getModules();

        foreach ($roles as $role) {
            foreach ($modules as $moduleKey => $moduleLabel) {
                $actions = PermissionService::getActionsForModule($moduleKey);
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

        app(PermissionService::class)->clearCache();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permisos actualizados correctamente.')
            ->withHeaders(['Cache-Control' => 'no-store, no-cache, must-revalidate']);
    }

    public function updateHierarchy(Request $request): RedirectResponse
    {
        $this->ensureCanEditPermissions();

        RoleHierarchy::whereHas('role', function ($q) {
            $q->where('name', '!=', 'super_admin');
        })->delete();

        $hierarchyInput = $request->input('hierarchy', []);
        if (!is_array($hierarchyInput)) {
            $hierarchyInput = [];
        }

        foreach ($hierarchyInput as $prefix => $data) {
            if (!is_array($data) || empty($data['can_create']) || (string) $data['can_create'] !== '1') {
                continue;
            }
            if (preg_match('/^(\d+)_(.+)$/', $prefix, $matches)) {
                $roleId = (int) $matches[1];
                $canCreateRole = $matches[2];
                $canView = !empty($data['can_view']) && (string) $data['can_view'] === '1';
                $canEdit = !empty($data['can_edit']) && (string) $data['can_edit'] === '1';
                RoleHierarchy::create([
                    'role_id' => $roleId,
                    'can_create_role' => $canCreateRole,
                    'can_view' => $canView,
                    'can_edit' => $canEdit,
                ]);
            }
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Jerarquía de roles actualizada correctamente.');
    }

    /**
     * Jerarquía por defecto (solo si la tabla está vacía).
     */
    protected function seedDefaultHierarchy($roles): void
    {
        $roleByName = $roles->keyBy('name');

        if ($roleByName->has('admin')) {
            $adminRole = $roleByName->get('admin');
            foreach (['admin', 'agent', 'client'] as $targetRole) {
                RoleHierarchy::firstOrCreate(
                    [
                        'role_id' => $adminRole->id,
                        'can_create_role' => $targetRole,
                    ],
                    [
                        'can_view' => true,
                        'can_edit' => true,
                    ]
                );
            }
        }

        if ($roleByName->has('agent')) {
            $agent = $roleByName->get('agent');
            RoleHierarchy::firstOrCreate(
                [
                    'role_id' => $agent->id,
                    'can_create_role' => 'client',
                ],
                [
                    'can_view' => true,
                    'can_edit' => true,
                ]
            );
        }
    }

    /**
     * Rellenar permisos por defecto (solo si no hay filas en role_permissions).
     */
    protected function seedDefaultPermissions($roles): void
    {
        $roleByName = $roles->keyBy('name');

        $grantAllExcept = function (Role $role, array $deniedModules): void {
            $modules = PermissionService::getModules();
            foreach ($modules as $moduleKey => $_label) {
                if (in_array($moduleKey, $deniedModules, true)) {
                    continue;
                }
                $actions = PermissionService::getActionsForModule($moduleKey);
                foreach ($actions as $actionKey => $_a) {
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
        };

        if ($roleByName->has('admin')) {
            $grantAllExcept($roleByName->get('admin'), ['backups', 'permissions']);
        }

        if ($roleByName->has('agent')) {
            $grantAllExcept($roleByName->get('agent'), ['backups', 'permissions']);
        }
    }
}
