@extends('layouts.admin-flowbite')

@section('title', 'Mi Perfil - RAMS')

@section('page-title', 'Mi Perfil')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Mi Perfil</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2">
            <!-- Información Personal -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Información Personal</h2>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nombre Completo</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Teléfono</label>
                            <input type="text" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cambiar Contraseña</h3>
                        <p class="text-sm text-gray-600 mb-4">Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_password" class="block mb-2 text-sm font-medium text-gray-900">Contraseña Actual</label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Nueva Contraseña</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Confirmar Nueva Contraseña</label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" 
                                class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            @include('profile.partials.two-factor', ['routePrefix' => 'admin', 'user' => $user])
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Avatar y Resumen -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4 shadow-lg">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>
                    
                    @if($user->roles->count() > 0)
                        <div class="flex flex-wrap gap-2 justify-center mb-4">
                            @foreach($user->roles as $role)
                                <span class="px-3 py-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Empresas Asignadas</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $user->companies_count }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Solicitudes asignadas</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $user->assigned_registrations_count }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Estado</dt>
                        <dd class="text-sm">
                            @if($user->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Activo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Inactivo</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Información Adicional -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $user->phone ?? 'No especificado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Miembro desde</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $user->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
