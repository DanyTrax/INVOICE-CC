<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Obtener roles que el usuario autenticado puede crear según su rol.
     * Usa el servicio de permisos si está configurado, sino usa la lógica hardcodeada.
     */
    protected function getAllowedRolesToCreate(): array
    {
        $user = auth()->user();
        
        // Intentar usar el servicio de permisos
        try {
            $permissionService = app(PermissionService::class);
            foreach ($user->roles as $role) {
                $canCreate = $permissionService->getRolesCanCreate($role->name);
                if (!empty($canCreate)) {
                    return $canCreate;
                }
            }
        } catch (\Exception $e) {
            // Si falla, usar lógica hardcodeada
        }
        
        // Lógica hardcodeada como fallback
        if ($user->hasRole('super_admin')) {
            return ['super_admin', 'panel_user', 'agent', 'client'];
        }
        
        if ($user->hasRole('panel_user')) {
            return ['panel_user', 'agent', 'client'];
        }
        
        if ($user->hasRole('agent')) {
            return ['client'];
        }
        
        return [];
    }

    /**
     * Obtener roles que el usuario autenticado puede ver según su rol.
     * Usa el servicio de permisos si está configurado, sino usa la lógica hardcodeada.
     */
    protected function getAllowedRolesToView(): array
    {
        $user = auth()->user();
        
        // Intentar usar el servicio de permisos
        try {
            $permissionService = app(PermissionService::class);
            foreach ($user->roles as $role) {
                $canView = $permissionService->getRolesCanView($role->name);
                if (!empty($canView)) {
                    return $canView;
                }
            }
        } catch (\Exception $e) {
            // Si falla, usar lógica hardcodeada
        }
        
        // Lógica hardcodeada como fallback
        if ($user->hasRole('super_admin')) {
            return ['super_admin', 'panel_user', 'agent', 'client'];
        }
        
        if ($user->hasRole('panel_user')) {
            return ['panel_user', 'agent', 'client'];
        }
        
        if ($user->hasRole('agent')) {
            return ['agent', 'client'];
        }
        
        return [];
    }

    /**
     * Verificar si el usuario autenticado puede ver/editar otro usuario.
     */
    protected function canViewUser(User $targetUser): bool
    {
        $allowedRoles = $this->getAllowedRolesToView();
        
        // Usuarios sin rol: permitir si la jerarquía incluye "Sin roles" (no_role)
        if ($targetUser->roles->isEmpty()) {
            return in_array(PermissionService::NO_ROLE, $allowedRoles, true);
        }
        
        foreach ($targetUser->roles as $role) {
            if (in_array($role->name, $allowedRoles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validar que los roles a asignar sean permitidos. 'no_role' permite dejar sin rol.
     */
    protected function validateRoles(array $roles): void
    {
        $allowedRoles = $this->getAllowedRolesToCreate();
        $noRole = PermissionService::NO_ROLE;
        // Quitar no_role para validar solo roles reales; [] o [no_role] es válido si no_role está permitido
        $rolesToCheck = array_values(array_filter($roles, fn ($r) => $r !== $noRole));
        if (empty($rolesToCheck)) {
            if (!in_array($noRole, $allowedRoles, true)) {
                abort(403, 'No tienes permiso para asignar "Sin roles".');
            }
            return;
        }
        $invalidRoles = array_diff($rolesToCheck, $allowedRoles);
        if (!empty($invalidRoles)) {
            abort(403, 'No tienes permiso para asignar los roles: ' . implode(', ', $invalidRoles));
        }
    }

    /** Roles a sincronizar: quitar no_role (dejar sin rol = sync vacío). */
    protected function rolesToSync(array $roles): array
    {
        return array_values(array_filter($roles, fn ($r) => $r !== PermissionService::NO_ROLE));
    }

    public function index(Request $request)
    {
        return redirect()->route('admin.agents.index');
    }

    /**
     * Lista solo usuarios con rol client (usuarios que consultan desde el portal).
     */
    public function clients(Request $request)
    {
        $query = User::role('client');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->with('roles')
            ->withCount('companies')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = collect();
        return view('admin.users.listing', [
            'users' => $users,
            'roles' => $roles,
            'listingType' => 'clients',
        ]);
    }

    /**
     * Lista solo usuarios que no son clientes (agentes, panel_user, super_admin).
     */
    public function agents(Request $request)
    {
        $query = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            if ($request->role === PermissionService::NO_ROLE) {
                $query->whereDoesntHave('roles');
            } else {
                $query->role($request->role);
            }
        }

        $users = $query->with('roles')
            ->withCount('companies')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::where('name', '!=', 'client')->get();
        $canFilterNoRole = in_array(PermissionService::NO_ROLE, $this->getAllowedRolesToView(), true);

        return view('admin.users.listing', [
            'users' => $users,
            'roles' => $roles,
            'listingType' => 'agents',
            'canFilterNoRole' => $canFilterNoRole,
        ]);
    }

    public function create()
    {
        $allowedRoles = $this->getAllowedRolesToCreate();
        $noRole = PermissionService::NO_ROLE;
        $canAssignNoRole = in_array($noRole, $allowedRoles, true);
        $roles = Role::whereIn('name', array_filter($allowedRoles, fn ($n) => $n !== $noRole))->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'companies', 'canAssignNoRole'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'roles' => 'array',
        ]);

        $rolesInput = $request->input('roles', []);
        $this->validateRoles(is_array($rolesInput) ? $rolesInput : []);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        $user->syncRoles($this->rolesToSync(is_array($rolesInput) ? $rolesInput : []));

        // Asignar empresas (clientes)
        if ($request->filled('companies')) {
            $user->companies()->sync($request->companies);
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        $user->load('roles', 'companies');
        $user->loadCount('companies', 'assignedRegistrations');
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Verificar que se puede ver/editar este usuario
        if (!$this->canViewUser($user)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $allowedRoles = $this->getAllowedRolesToCreate();
        $noRole = PermissionService::NO_ROLE;
        $canAssignNoRole = in_array($noRole, $allowedRoles, true);
        $roles = Role::whereIn('name', array_filter($allowedRoles, fn ($n) => $n !== $noRole))->get();
        $companies = Company::orderBy('name')->get();
        $user->load('roles', 'companies');
        return view('admin.users.edit', compact('user', 'roles', 'companies', 'canAssignNoRole'));
    }

    public function update(Request $request, User $user)
    {
        // Verificar que se puede editar este usuario
        if (!$this->canViewUser($user)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'roles' => 'array',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
        ]);

        $rolesInput = $request->input('roles', []);
        $this->validateRoles(is_array($rolesInput) ? $rolesInput : []);

        // Solo actualizar password si se proporciona
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        $user->syncRoles($this->rolesToSync(is_array($rolesInput) ? $rolesInput : []));

        // Sincronizar empresas (clientes)
        if ($request->filled('companies')) {
            $user->companies()->sync($request->companies);
        } else {
            $user->companies()->sync([]);
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // Verificar que se puede eliminar este usuario
        if (!$this->canViewUser($user)) {
            abort(403, 'No tienes permiso para eliminar este usuario.');
        }

        // No permitir eliminar el usuario actual
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.agents.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Verificar si tiene registros asignados
        if ($user->assignedRegistrations()->count() > 0) {
            return redirect()
                ->route('admin.agents.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene expedientes asignados.');
        }

        $user->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Mostrar perfil del usuario autenticado
     */
    public function profile()
    {
        $user = auth()->user();
        $user->load('roles', 'companies');
        $user->loadCount('companies', 'assignedRegistrations');
        
        return view('admin.users.profile', compact('user'));
    }

    /**
     * Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ]);

        // Validar contraseña actual si se está cambiando la contraseña
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()
                    ->route('admin.profile')
                    ->withErrors(['current_password' => 'La contraseña actual no es correcta.'])
                    ->withInput();
            }
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']);

        $user->update($validated);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Perfil actualizado exitosamente.');
    }
}
