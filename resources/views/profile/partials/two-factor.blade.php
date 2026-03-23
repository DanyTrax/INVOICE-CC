@php
    $rp = $routePrefix ?? 'admin';
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" id="two-factor">
    <h2 class="text-xl font-bold text-gray-900 mb-2">Verificación en dos pasos (2FA)</h2>
    <p class="text-sm text-gray-600 mb-4">
        Añade una capa extra de seguridad con una app de autenticación (Google Authenticator, Microsoft Authenticator, etc.).
    </p>

    @if (session('two_factor_recovery_display'))
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm font-semibold text-amber-900 mb-2">Guarda estos códigos de respaldo en un lugar seguro (solo se muestran una vez):</p>
            <ul class="font-mono text-sm text-amber-900 space-y-1">
                @foreach (session('two_factor_recovery_display') as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($user->hasTwoFactorEnabled())
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-1"></i> Activo
            </span>
        </div>

        <form action="{{ route($rp . '.profile.two-factor.disable') }}" method="POST" class="space-y-4 mb-6">
            @csrf
            <p class="text-sm text-gray-600">Para desactivar el 2FA introduce tu contraseña actual.</p>
            <div class="max-w-md">
                <label for="tf_disable_password" class="block mb-1 text-sm font-medium text-gray-900">Contraseña actual</label>
                <input type="password" name="current_password" id="tf_disable_password" required
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                Desactivar 2FA
            </button>
        </form>

        <form action="{{ route($rp . '.profile.two-factor.regenerate-recovery') }}" method="POST" class="space-y-4 border-t border-gray-200 pt-6">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900">Regenerar códigos de respaldo</h3>
            <p class="text-sm text-gray-600">Los códigos anteriores dejarán de funcionar.</p>
            <div class="max-w-md">
                <label for="tf_regen_password" class="block mb-1 text-sm font-medium text-gray-900">Contraseña actual</label>
                <input type="password" name="current_password" id="tf_regen_password" required
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                Generar nuevos códigos
            </button>
        </form>
    @else
        @if (session('two_factor_qr'))
            <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-sm text-gray-700 mb-3">Escanea el código con tu app. Luego introduce el código de 6 dígitos para confirmar.</p>
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <div class="bg-white p-2 rounded border border-gray-200">
                        <img src="{{ session('two_factor_qr') }}" alt="QR 2FA" class="w-48 h-48">
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>Si no puedes escanear, introduce manualmente el secreto:</p>
                        <code class="block p-2 bg-white border rounded font-mono text-xs break-all">{{ session('two_factor_secret_display') }}</code>
                    </div>
                </div>

                <form action="{{ route($rp . '.profile.two-factor.confirm') }}" method="POST" class="mt-4 space-y-3 max-w-xs">
                    @csrf
                    <label for="tf_code" class="block text-sm font-medium text-gray-900">Código de 6 dígitos</label>
                    <input type="text" name="code" id="tf_code" inputmode="numeric" pattern="[0-9\s]*" autocomplete="one-time-code" required maxlength="12"
                           class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
                           placeholder="000000">
                    @error('code')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                        Confirmar y activar
                    </button>
                </form>

                <form action="{{ route($rp . '.profile.two-factor.cancel') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cancelar configuración</button>
                </form>
            </div>
        @else
            <form action="{{ route($rp . '.profile.two-factor.start') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                    <i class="fas fa-mobile-screen-button mr-2"></i> Iniciar configuración de 2FA
                </button>
            </form>
        @endif
    @endif
</div>
