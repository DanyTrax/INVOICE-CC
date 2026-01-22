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
    public function index(Request $request)
    {
        $query = User::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->with('roles')
            ->withCount('companies')
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
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
            ->route('admin.users.index')
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
        $roles = Role::all();
        $companies = Company::orderBy('name')->get();
        $user->load('roles', 'companies');
        return view('admin.users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(Request $request, User $user)
    {
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
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // No permitir eliminar el usuario actual
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Verificar si tiene registros asignados
        if ($user->assignedRegistrations()->count() > 0) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene expedientes asignados.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
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
