<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Obtener roles que el usuario autenticado puede crear según su rol.
     */
    protected function getAllowedRolesToCreate(): array
    {
        $user = auth()->user();
        
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
     */
    protected function getAllowedRolesToView(): array
    {
        $user = auth()->user();
        
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
        
        // Si el usuario objetivo tiene algún rol permitido, se puede ver
        foreach ($targetUser->roles as $role) {
            if (in_array($role->name, $allowedRoles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validar que los roles a asignar sean permitidos.
     */
    protected function validateRoles(array $roles): void
    {
        $allowedRoles = $this->getAllowedRolesToCreate();
        $invalidRoles = array_diff($roles, $allowedRoles);
        
        if (!empty($invalidRoles)) {
            abort(403, 'No tienes permiso para asignar los roles: ' . implode(', ', $invalidRoles));
        }
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
            $query->role($request->role);
        }

        $users = $query->with('roles')
            ->withCount('companies')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::where('name', '!=', 'client')->get();

        return view('admin.users.listing', [
            'users' => $users,
            'roles' => $roles,
            'listingType' => 'agents',
        ]);
    }

    public function create()
    {
        $allowedRoles = $this->getAllowedRolesToCreate();
        $roles = Role::whereIn('name', $allowedRoles)->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'companies'));
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

        // Validar que los roles sean permitidos
        if ($request->filled('roles')) {
            $this->validateRoles($request->roles);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        // Asignar roles
        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

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
        $roles = Role::whereIn('name', $allowedRoles)->get();
        $companies = Company::orderBy('name')->get();
        $user->load('roles', 'companies');
        return view('admin.users.edit', compact('user', 'roles', 'companies'));
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

        // Validar que los roles sean permitidos
        if ($request->filled('roles')) {
            $this->validateRoles($request->roles);
        }

        // Solo actualizar password si se proporciona
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        // Sincronizar roles
        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        } else {
            $user->syncRoles([]);
        }

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
