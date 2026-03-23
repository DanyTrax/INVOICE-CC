@extends('layouts.admin-flowbite')

@section('title', 'Editar Usuario - RAMS')

@section('page-title', 'Editar Usuario')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Usuarios</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Editar</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="space-y-6">
    @include('admin.users.partials.two-factor-admin-card', [
        'user' => $user,
        'last_login_at' => $last_login_at ?? null,
        'can_manage_two_factor' => $can_manage_two_factor ?? false,
    ])

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}"
                           required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900">
                        Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           minlength="8"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Dejar en blanco para mantener la contraseña actual</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">
                        Confirmar Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           minlength="8"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">
                        Teléfono
                    </label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $user->phone) }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- Estado Activo -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">
                        Estado
                    </label>
                    <div class="flex items-center h-10">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-900">Usuario activo</label>
                    </div>
                </div>

                <!-- Roles (solo uno) -->
                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900">
                        Rol <span class="text-gray-500 font-normal">(seleccione solo uno)</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $noRole = \App\Services\PermissionService::NO_ROLE;
                            $currentRole = $user->roles->first()?->name ?? $noRole;
                            $selectedRole = old('role', $currentRole);
                            if ($selectedRole === $noRole && !($canAssignNoRole ?? false) && $roles->isNotEmpty()) {
                                $selectedRole = $roles->first()->name;
                            }
                        @endphp
                        @if($canAssignNoRole ?? false)
                            <div class="flex items-center">
                                <input type="radio" 
                                       id="role_no_role" 
                                       name="role" 
                                       value="{{ $noRole }}"
                                       {{ $selectedRole === $noRole ? 'checked' : '' }}
                                       class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 focus:ring-teal-500">
                                <label for="role_no_role" class="ml-2 text-sm text-gray-900">Sin roles</label>
                            </div>
                        @endif
                        @foreach($roles as $role)
                            <div class="flex items-center">
                                <input type="radio" 
                                       id="role_{{ $role->id }}" 
                                       name="role" 
                                       value="{{ $role->name }}"
                                       {{ $selectedRole === $role->name ? 'checked' : '' }}
                                       class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 focus:ring-teal-500">
                                <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-900">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Empresas Asignadas -->
                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900">
                        Empresas Asignadas
                    </label>
                    <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                        @if($companies->count() > 0)
                            <div class="space-y-2">
                                @foreach($companies as $company)
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="company_{{ $company->id }}" 
                                               name="companies[]" 
                                               value="{{ $company->id }}"
                                               {{ in_array($company->id, old('companies', $user->companies->pluck('id')->toArray())) ? 'checked' : '' }}
                                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                                        <label for="company_{{ $company->id }}" class="ml-2 text-sm text-gray-900 flex-1">
                                            <span class="font-medium">{{ $company->name }}</span>
                                            @if($company->nit_rut)
                                                <span class="text-gray-500 ml-2">({{ $company->nit_rut }})</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">No hay empresas registradas</p>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Selecciona las empresas que estarán asignadas a este usuario</p>
                    @error('companies')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.agents.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
    </div>
@endsection
