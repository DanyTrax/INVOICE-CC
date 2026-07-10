@extends('layouts.admin-flowbite')

@section('title', 'Ver Usuario')
@section('page-title', $user->name)

@section('content')
    <div class="space-y-6">
    @include('admin.users.partials.two-factor-admin-card', [
        'user' => $user,
        'last_login_at' => $last_login_at ?? null,
        'can_manage_two_factor' => $can_manage_two_factor ?? false,
    ])

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
                <a href="{{ route('admin.users.edit', $user) }}"
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
                <dt class="text-sm font-medium text-gray-500">Registrado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Último ingreso</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($last_login_at ?? null)
                        {{ $last_login_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                    @else
                        <span class="text-gray-500">Sin registros</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
    </div>
@endsection
