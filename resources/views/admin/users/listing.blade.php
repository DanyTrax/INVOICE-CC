@extends('layouts.admin-flowbite')

@section('title', $listingType === 'clients' ? 'Clientes - RAMS' : 'Especialistas - RAMS')

@section('page-title', $listingType === 'clients' ? 'Clientes' : 'Especialistas')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">{{ $listingType === 'clients' ? 'Clientes' : 'Especialistas' }}</span>
        </div>
    </li>
@endsection

@section('content')
    @php
        $indexRoute = $listingType === 'clients' ? 'admin.clients.index' : 'admin.agents.index';
    @endphp
    <!-- Barra de búsqueda y acciones -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <form method="GET" action="{{ route($indexRoute) }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre, email o teléfono..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                </div>
                @if($listingType === 'agents' && ($roles->isNotEmpty() || ($canFilterNoRole ?? false)))
                    <select name="role" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                        <option value="">Todos los roles</option>
                        @if($canFilterNoRole ?? false)
                            <option value="{{ \App\Services\PermissionService::NO_ROLE }}" {{ request('role') === \App\Services\PermissionService::NO_ROLE ? 'selected' : '' }}>
                                Sin roles
                            </option>
                        @endif
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
                @if(request('search') || request('role'))
                    <a href="{{ route($indexRoute) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>
        @if($listingType === 'agents')
            @php
                // Solo mostrar botón si el usuario puede crear agentes (no solo clientes)
                $allowedRolesToCreate = auth()->user()->hasRole('super_admin') 
                    ? ['super_admin', 'admin', 'agent', 'client']
                    : (auth()->user()->hasRole('admin') 
                        ? ['admin', 'agent', 'client']
                        : []);
                $canCreateAgents = !empty(array_intersect(['super_admin', 'admin', 'agent'], $allowedRolesToCreate));
            @endphp
            @if($canCreateAgents)
                <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-plus mr-2"></i> Nuevo Especialista
                </a>
            @endif
        @endif
        @if($listingType === 'clients' && ($canCreateClients ?? false))
            <a href="{{ route('admin.clients.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                <i class="fas fa-plus mr-2"></i> Nuevo Cliente
            </a>
        @endif
    </div>

    @if($listingType === 'clients' && isset($pendingInvites) && $pendingInvites->total() > 0)
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-amber-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-amber-100 bg-amber-50/80">
                <h2 class="text-sm font-semibold text-amber-900">
                    <i class="fas fa-hourglass-half mr-2"></i> Invitaciones pendientes de registro
                </h2>
                <p class="text-xs text-amber-800 mt-1">Correos a los que se envió enlace y aún no completan el formulario de alta. Al registrarse pasan al listado de abajo con estado <strong>pendiente</strong> hasta que un administrador active el acceso al portal.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Correo</th>
                            <th scope="col" class="px-6 py-3">Empresa</th>
                            <th scope="col" class="px-6 py-3">Estado</th>
                            <th scope="col" class="px-6 py-3">Vence</th>
                            <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingInvites as $invite)
                            @php
                                $expired = $invite->expires_at && $invite->expires_at->isPast();
                            @endphp
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $invite->email }}</td>
                                <td class="px-6 py-3">{{ $invite->company?->name ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-900">Pendiente registro</span>
                                    @if($expired)
                                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Enlace expirado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-xs">{{ $invite->expires_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <form action="{{ route('admin.company-invites.resend', $invite) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-teal-700 hover:text-teal-900 text-sm font-medium" title="Generar nuevo enlace y reenviar correo">
                                                <i class="fas fa-redo mr-1"></i> Reenviar
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.company-invites.destroy', $invite) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('¿Eliminar esta invitación pendiente? El enlace dejará de ser válido.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50"
                                                    title="Eliminar invitación">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pendingInvites->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $pendingInvites->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Usuario</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Teléfono</th>
                        <th scope="col" class="px-6 py-3">Roles</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Empresas</th>
                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold mr-3">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-gray-500">(Tú)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">{{ $user->phone ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-xs">Sin roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($listingType === 'clients')
                                    @php
                                        $status = $user->client_status ?? 'activo';
                                        $statusLabels = ['activo' => 'Activo', 'pendiente' => 'Pendiente', 'deshabilitado' => 'Deshabilitado'];
                                        $statusClasses = [
                                            'activo' => 'bg-green-100 text-green-800',
                                            'pendiente' => 'bg-amber-100 text-amber-800',
                                            'deshabilitado' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    @if(in_array($user->id, $editableUserIds ?? []))
                                        <form action="{{ route('admin.users.client-status.update', $user) }}" method="POST" class="inline" onchange="this.submit()">
                                            @csrf
                                            @method('PATCH')
                                            <select name="client_status" class="text-xs font-medium rounded-full border-0 py-1 pr-6 {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                <option value="activo" {{ $status === 'activo' ? 'selected' : '' }}>Activo</option>
                                                <option value="pendiente" {{ $status === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="deshabilitado" {{ $status === 'deshabilitado' ? 'selected' : '' }}>Deshabilitado</option>
                                            </select>
                                        </form>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }} rounded-full">{{ $statusLabels[$status] ?? $status }}</span>
                                    @endif
                                @else
                                    @if($user->is_active)
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Inactivo</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ $user->companies_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php $canEditThis = in_array($user->id, $editableUserIds ?? []); @endphp
                                <div class="flex items-center justify-end gap-2">
                                    @if(($listingType === 'agents' || $listingType === 'clients') && $user->id !== auth()->id() && $canEditThis)
                                        <form action="{{ route('admin.users.send-access-email', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-amber-600 hover:text-amber-800"
                                                    title="Enviar correo de acceso (activación o recuperación de contraseña)">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="text-blue-600 hover:text-blue-800" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($canEditThis)
                                        <a href="{{ $listingType === 'clients' ? route('admin.clients.edit', $user) : route('admin.users.edit', $user) }}"
                                           class="text-teal-600 hover:text-teal-800" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>{{ $listingType === 'clients' ? 'No se encontraron clientes.' : 'No se encontraron especialistas.' }}</p>
                                @if(request('search') || request('role'))
                                    <a href="{{ route($indexRoute) }}" class="text-teal-600 hover:text-teal-700 mt-2 inline-block">
                                        Ver todos
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
