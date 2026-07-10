@extends('layouts.admin-flowbite')

@section('title', 'Verificación en dos pasos')
@section('page-title', 'Verificación en dos pasos (2FA)')

@section('content')
    @include('admin.partials.flash')

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start gap-4 mb-6">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-shield-halved text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Control global del 2FA</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Activa o desactiva la verificación en dos pasos para todo el panel administrativo.
                        Cuando está desactivada, nadie será solicitado el código al iniciar sesión y no se mostrará el asistente de configuración.
                    </p>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg text-sm">
                <div>
                    <dt class="text-gray-500">Usuarios con 2FA activo</dt>
                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $usersWithTwoFactor }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Usuarios administrativos</dt>
                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $totalUsers }}</dd>
                </div>
            </dl>

            <form action="{{ route('admin.two-factor-settings.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <label class="flex items-start gap-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="two_factor_system_enabled" value="0">
                    <input type="checkbox"
                           name="two_factor_system_enabled"
                           value="1"
                           class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                           @checked($enabled)>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Habilitar verificación en dos pasos</span>
                        <span class="block mt-1 text-sm text-gray-600">
                            Si está marcado, los usuarios que configuren 2FA en su perfil deberán ingresar el código al iniciar sesión.
                            Si lo desmarcas, el acceso será solo con usuario y contraseña.
                        </span>
                    </span>
                </label>

                @if($usersWithTwoFactor > 0 && $enabled)
                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        Hay {{ $usersWithTwoFactor }} usuario(s) con 2FA configurado. Si desactivas esta opción, dejarán de ser solicitados al ingresar, pero su configuración se conservará por si la vuelves a activar.
                    </p>
                @endif

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                        <i class="fas fa-save mr-2"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
