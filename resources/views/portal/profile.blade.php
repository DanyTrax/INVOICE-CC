@extends('layouts.portal')

@section('title', 'Mi perfil')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Mi perfil</h1>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Datos personales</h2>
        <form action="{{ route('portal.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-md font-semibold text-gray-900 mb-2">Cambiar contraseña</h3>
                <p class="text-sm text-gray-500 mb-3">Deja en blanco si no deseas cambiarla.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña actual</label>
                        <input type="password" name="current_password" id="current_password"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('current_password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
                        <input type="password" name="password" id="password"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    @include('profile.partials.two-factor', ['routePrefix' => 'portal', 'user' => $user])
</div>
@endsection
