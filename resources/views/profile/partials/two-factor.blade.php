@php
    $rp = $routePrefix ?? 'admin';
    $twoFactorSystemEnabled = app(\App\Services\TwoFactorService::class)->isSystemEnabled();
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" id="two-factor">

    <h2 class="text-xl font-bold text-gray-900 mb-2">Verificación en dos pasos (2FA)</h2>

    @if (! $twoFactorSystemEnabled)
        <p class="text-sm text-gray-600">
            La verificación en dos pasos está desactivada a nivel del sistema. Un administrador puede habilitarla en
            <strong>Sistema → Verificación 2FA</strong>.
        </p>
    @else
    <p class="text-sm text-gray-600 mb-4">
        Añade una capa extra de seguridad con una app de autenticación (Google Authenticator, Microsoft Authenticator, etc.).
    </p>

    @if (session('two_factor_recovery_display') && $user->hasTwoFactorEnabled())
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
        @include('profile.partials.two-factor-setup-wizard', ['routePrefix' => $rp, 'variant' => 'full'])
    @endif
    @endif
</div>
