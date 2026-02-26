<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Services\MailService;
use App\Services\EmailTemplateService;
use App\Services\PermissionService;
use App\Services\ActivityLogService;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
            return ['super_admin', 'admin', 'agent', 'client'];
        }
        
        if ($user->hasRole('admin')) {
            return ['admin', 'agent', 'client'];
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
            return ['super_admin', 'admin', 'agent', 'client'];
        }
        
        if ($user->hasRole('admin')) {
            return ['admin', 'agent', 'client'];
        }
        
        if ($user->hasRole('agent')) {
            return ['agent', 'client'];
        }
        
        return [];
    }

    /**
     * Obtener roles que el usuario autenticado puede editar según su rol.
     */
    protected function getAllowedRolesToEdit(): array
    {
        $user = auth()->user();

        try {
            $permissionService = app(PermissionService::class);
            foreach ($user->roles as $role) {
                $canEdit = $permissionService->getRolesCanEdit($role->name);
                if (!empty($canEdit)) {
                    return $canEdit;
                }
            }
        } catch (\Exception $e) {
        }

        if ($user->hasRole('super_admin')) {
            return ['super_admin', 'admin', 'agent', 'client', PermissionService::NO_ROLE];
        }
        if ($user->hasRole('admin')) {
            return ['admin', 'agent', 'client'];
        }
        if ($user->hasRole('agent')) {
            return ['agent', 'client'];
        }
        return [];
    }

    /**
     * Verificar si el usuario autenticado puede ver otro usuario.
     */
    protected function canViewUser(User $targetUser): bool
    {
        $allowedRoles = $this->getAllowedRolesToView();

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
     * Verificar si el usuario autenticado puede editar otro usuario (editar, actualizar, eliminar, enviar correo).
     */
    protected function canEditUser(User $targetUser): bool
    {
        $allowedRoles = $this->getAllowedRolesToEdit();

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
     * Validar que el rol seleccionado (único) sea permitido. null o no_role = sin rol.
     */
    protected function validateRole(?string $role): void
    {
        $allowedRoles = $this->getAllowedRolesToCreate();
        $noRole = PermissionService::NO_ROLE;
        if (empty($role) || $role === $noRole) {
            if (!in_array($noRole, $allowedRoles, true)) {
                abort(403, 'No tienes permiso para asignar "Sin roles".');
            }
            return;
        }
        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'No tienes permiso para asignar el rol: ' . $role);
        }
    }

    /** Convierte el rol seleccionado (único) a array para syncRoles. */
    protected function roleToSync(?string $role): array
    {
        if (empty($role) || $role === PermissionService::NO_ROLE) {
            return [];
        }
        return [$role];
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
        $canCreateClients = in_array('client', $this->getAllowedRolesToCreate(), true);
        $editableUserIds = collect($users->items())->filter(fn ($u) => $this->canEditUser($u))->pluck('id')->all();
        return view('admin.users.listing', [
            'users' => $users,
            'roles' => $roles,
            'listingType' => 'clients',
            'canCreateClients' => $canCreateClients,
            'canFilterNoRole' => false,
            'editableUserIds' => $editableUserIds,
        ]);
    }

    /**
     * Formulario para crear un nuevo cliente (rol client). Solo si el rol tiene permiso para crear clientes.
     */
    public function createClient()
    {
        if (!in_array('client', $this->getAllowedRolesToCreate(), true)) {
            abort(403, 'No tienes permiso para crear clientes.');
        }
        $companies = Company::orderBy('name')->get();
        return view('admin.users.create-client', compact('companies'));
    }

    /**
     * Guardar nuevo cliente (rol client). Solo si el rol tiene permiso para crear clientes.
     */
    public function storeClient(Request $request): RedirectResponse
    {
        if (!in_array('client', $this->getAllowedRolesToCreate(), true)) {
            abort(403, 'No tienes permiso para crear clientes.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        $validated['client_status'] = $validated['is_active'] ? User::CLIENT_STATUS_ACTIVO : User::CLIENT_STATUS_DESHABILITADO;
        $user = User::create($validated);
        $user->assignRole('client');
        if ($request->filled('companies')) {
            $user->companies()->sync($request->companies);
        }
        app(ActivityLogService::class)->log('created', 'Creó el cliente "' . $user->name . '" (' . $user->email . ')', $user);
        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Actualizar estado del cliente (activo, pendiente, deshabilitado).
     */
    public function updateClientStatus(Request $request, User $user): RedirectResponse
    {
        if (!$user->hasRole('client')) {
            abort(404, 'El usuario no es un cliente.');
        }
        if (!$this->canEditUser($user)) {
            abort(403, 'No tienes permiso para editar este cliente.');
        }
        $validated = $request->validate([
            'client_status' => 'required|in:activo,pendiente,deshabilitado',
        ]);
        $user->client_status = $validated['client_status'];
        $user->is_active = ($validated['client_status'] === 'activo');
        $user->save();
        app(ActivityLogService::class)->log('updated', 'Cambió el estado del cliente "' . $user->name . '" a ' . $validated['client_status'], $user);
        return back()->with('success', 'Estado del cliente actualizado.');
    }

    /**
     * Lista solo usuarios que no son clientes (agentes, admin, super_admin).
     */
    public function agents(Request $request)
    {
        $query = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'));

        // Solo mostrar usuarios que el actual puede ver (según jerarquía) + siempre incluir usuarios sin rol
        $allowedToView = $this->getAllowedRolesToView();
        $query->where(function ($q) use ($allowedToView) {
            $q->whereHas('roles', fn ($r) => $r->whereIn('name', $allowedToView));
            $q->orWhereDoesntHave('roles'); // usuarios sin rol siempre se muestran en la lista de agentes
        });

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
        $editableUserIds = collect($users->items())->filter(fn ($u) => $this->canEditUser($u))->pluck('id')->all();

        return view('admin.users.listing', [
            'users' => $users,
            'roles' => $roles,
            'listingType' => 'agents',
            'canFilterNoRole' => $canFilterNoRole,
            'editableUserIds' => $editableUserIds,
        ]);
    }

    public function create()
    {
        // En Agentes no se muestra el rol "client"; solo se crean agentes (otros roles).
        $allowedRoles = array_values(array_filter($this->getAllowedRolesToCreate(), fn ($n) => $n !== 'client'));
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
            'role' => 'nullable|string|max:255',
        ]);

        $roleInput = $request->input('role');
        $this->validateRole($roleInput);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        $user->syncRoles($this->roleToSync($roleInput));

        // Asignar empresas (clientes)
        if ($request->filled('companies')) {
            $user->companies()->sync($request->companies);
        }

        app(ActivityLogService::class)->log('created', 'Creó el agente/usuario "' . $user->name . '" (' . $user->email . ')', $user);

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
        if (!$this->canEditUser($user)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        // En Agentes no se muestra el rol "client"; solo roles de agentes.
        $allowedRoles = array_values(array_filter($this->getAllowedRolesToCreate(), fn ($n) => $n !== 'client'));
        $noRole = PermissionService::NO_ROLE;
        $canAssignNoRole = in_array($noRole, $allowedRoles, true);
        $roles = Role::whereIn('name', array_filter($allowedRoles, fn ($n) => $n !== $noRole))->get();
        $companies = Company::orderBy('name')->get();
        $user->load('roles', 'companies');
        return view('admin.users.edit', compact('user', 'roles', 'companies', 'canAssignNoRole'));
    }

    public function update(Request $request, User $user)
    {
        if (!$this->canEditUser($user)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'role' => 'nullable|string|max:255',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
        ]);

        $roleInput = $request->input('role');
        $this->validateRole($roleInput);

        // Solo actualizar password si se proporciona
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        $user->syncRoles($this->roleToSync($roleInput));

        // Sincronizar empresas (clientes)
        if ($request->filled('companies')) {
            $user->companies()->sync($request->companies);
        } else {
            $user->companies()->sync([]);
        }

        app(ActivityLogService::class)->log('updated', 'Actualizó el usuario "' . $user->name . '" (' . $user->email . ')', $user);

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if (!$this->canEditUser($user)) {
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

        $name = $user->name;
        $email = $user->email;
        $user->delete();
        app(ActivityLogService::class)->log('deleted', 'Eliminó el usuario "' . $name . '" (' . $email . ')');

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Enviar correo de acceso al agente: link para establecer o restablecer contraseña.
     */
    public function sendAccessEmail(User $user): RedirectResponse
    {
        if (!$this->canEditUser($user)) {
            abort(403, 'No tienes permiso para enviar correo a este usuario.');
        }
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'No puedes enviarte el correo a ti mismo.');
        }

        $token = Password::broker()->createToken($user);
        $link = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $templateService = app(EmailTemplateService::class);
        $processed = $templateService->processTemplate('access_email', [
            'name' => $user->name,
            'email' => $user->email,
            'link' => $link,
        ]);

        if (!$processed) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'No existe la plantilla de correo de acceso. Ejecuta: php artisan db:seed --class=EmailTemplateSeeder');
        }

        $mailService = app(MailService::class);
        $sent = $mailService->send(
            $user->email,
            $processed['subject'],
            $processed['body']
        );

        if (!$sent) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'No se pudo enviar el correo. Revisa Configuración → Historial de correos.');
        }

        app(ActivityLogService::class)->log('sent_email', 'Envió correo de acceso a "' . $user->name . '" (' . $user->email . ')', $user);
        return redirect()->route('admin.agents.index')
            ->with('success', 'Correo de acceso enviado a ' . $user->email);
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
