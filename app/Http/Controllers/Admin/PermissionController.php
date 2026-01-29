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

        $roles = Role::orderBy('name')->get();
        $modules = PermissionService::getModules();
        $actions = PermissionService::getActions();

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

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.role_id' => 'required|exists:roles,id',
            'permissions.*.module' => 'required|string',
            'permissions.*.action' => 'required|string',
            'permissions.*.enabled' => 'boolean',
        ]);

        foreach ($request->permissions as $perm) {
            RolePermission::updateOrCreate(
                [
                    'role_id' => $perm['role_id'],
                    'module' => $perm['module'],
                    'action' => $perm['action'],
                ],
                [
                    'enabled' => $perm['enabled'] ?? true,
                ]
            );
        }

        // Limpiar caché
        app(PermissionService::class)->clearCache();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permisos actualizados correctamente.');
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
}
