@extends('layouts.admin-flowbite')

@section('title', 'Ver Usuario - RAMS')

@section('page-title', $user->name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            @if($user->hasRole('client'))
                <a href="{{ route('admin.clients.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Clientes</a>
            @else
                <a href="{{ route('admin.agents.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Especialistas</a>
            @endif
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Ver</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold text-xl mr-4">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                            @if($user->id === auth()->id())
                                <span class="text-sm text-gray-500">(Tu perfil)</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if($canEdit ?? false)
                        <a href="{{ $user->hasRole('client') ? route('admin.clients.edit', $user) : route('admin.users.edit', $user) }}"
                           class="px-3 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        @endif
                    </div>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="mailto:{{ $user->email }}" class="text-teal-600 hover:text-teal-700">
                                {{ $user->email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            @if($user->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Activo
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                    Inactivo
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Roles</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-sm">Sin roles asignados</span>
                                @endforelse
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Verificación en dos pasos (2FA)</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($user->hasTwoFactorEnabled())
                                <span class="text-green-700 font-medium">Activado</span>
                                @if(($canEdit ?? false) && auth()->user()->hasAnyRole(['super_admin', 'admin']) && auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.disable-two-factor', $user) }}" method="POST" class="mt-2 inline-block"
                                          onsubmit="return confirm('¿Desactivar el 2FA para este usuario? Podrá configurarlo de nuevo desde su perfil.');">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:underline">
                                            Quitar 2FA (administrador)
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-gray-500">No activado</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Estadísticas -->
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Empresas Asignadas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $user->companies_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Expedientes Asignados</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $user->assigned_registrations_count }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empresas Asignadas -->
    @if($user->companies->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Empresas Asignadas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($user->companies as $company)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <h4 class="font-medium text-gray-900">{{ $company->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $company->nit_rut }}</p>
                        <a href="{{ route('admin.companies.show', $company) }}" 
                           class="text-teal-600 hover:text-teal-700 text-sm mt-2 inline-block">
                            Ver detalles →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
