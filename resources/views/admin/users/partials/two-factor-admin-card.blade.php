{{--
    Tarjeta 2FA + último ingreso (vistas ver/editar usuario para admin/super_admin).
    Requiere: $user, $last_login_at (nullable), $can_manage_two_factor (bool)
--}}
@php
    $lastLogin = $last_login_at ?? null;
    $canManage = $can_manage_two_factor ?? false;
    $twoFaOn = $user->hasTwoFactorEnabled();
@endphp

<div class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/80 p-5 shadow-sm ring-1 ring-gray-100">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex gap-3 min-w-0">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                <i class="fas fa-shield-halved text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-gray-900">Segundo factor (2FA)</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Estado de la verificación en dos pasos y último acceso registrado en el sistema.
                </p>
            </div>
        </div>
        <div class="shrink-0">
            @if($twoFaOn)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    Activo
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                    No activo
                </span>
            @endif
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Último ingreso</dt>
            <dd class="mt-0.5 text-sm font-semibold text-gray-900">
                @if($lastLogin)
                    {{ $lastLogin->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @else
                    <span class="font-normal text-gray-500">Sin registros de acceso</span>
                @endif
            </dd>
            <p class="mt-1 text-[11px] text-gray-400">Según historial de inicios de sesión (incluye acceso con 2FA).</p>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">2FA configurado</dt>
            <dd class="mt-0.5 text-sm text-gray-800">
                @if($twoFaOn && $user->two_factor_confirmed_at)
                    {{ $user->two_factor_confirmed_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @elseif($twoFaOn)
                    <span class="text-gray-600">—</span>
                @else
                    <span class="text-gray-500">Aún no ha activado el 2FA en su perfil</span>
                @endif
            </dd>
        </div>
    </dl>

    @if($canManage && app(\App\Services\TwoFactorService::class)->isSystemEnabled())
        <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
            @if($twoFaOn)
                <form action="{{ route('admin.users.disable-two-factor', $user) }}" method="POST" class="inline"
                      onsubmit="return confirm('¿Restablecer el segundo factor para {{ $user->name }}? Deberá configurarlo de nuevo desde su perfil.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                        <i class="fas fa-unlock-keyhole"></i>
                        Restablecer 2FA
                    </button>
                </form>
                <p class="text-xs text-gray-500 max-w-md">
                    Quita la vinculación con la app de autenticación. El usuario podrá volver a activar el 2FA desde su perfil.
                </p>
            @else
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle text-teal-600 mr-1"></i>
                    Este usuario no tiene 2FA activo. No hay nada que restablecer.
                </p>
            @endif
        </div>
    @elseif($canManage && ! app(\App\Services\TwoFactorService::class)->isSystemEnabled())
        <p class="mt-3 text-xs text-gray-500 border-t border-gray-100 pt-3">
            La verificación en dos pasos está desactivada a nivel del sistema (Sistema → Verificación 2FA).
        </p>
    @elseif(auth()->id() === $user->id)
        <p class="mt-3 text-xs text-gray-400 border-t border-gray-100 pt-3">Estás viendo tu propio usuario.</p>
    @endif
</div>
