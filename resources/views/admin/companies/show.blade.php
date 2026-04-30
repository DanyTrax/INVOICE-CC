@extends('layouts.admin-flowbite')

@section('title', 'Ver Empresa - RAMS')

@section('page-title', $company->name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.companies.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Empresas</a>
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
    @php
        $inviteContactUser = $company->contactRegisteredUser();
        $permService = app(\App\Services\PermissionService::class);
        $canCompaniesEdit = $permService->userHasPermission('companies', 'edit');
    @endphp
    {{-- Botones de acción --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @if($canCompaniesEdit)
            <a href="{{ route('admin.companies.edit', $company) }}"
               class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
        @endif
        <a href="{{ route('admin.processes.monitor', ['client_id' => $company->id]) }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-folder-open mr-2"></i> Ver solicitudes
        </a>
        @if($canCompaniesEdit && ! $inviteContactUser)
            <button type="button"
                    onclick="openCompanyInviteModal({{ $company->id }})"
                    class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm font-medium">
                <i class="fas fa-envelope mr-2"></i> Invitar al registro
            </button>
        @endif
    </div>

    {{-- Información básica (compacta) --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div>
                <dt class="text-gray-500">NIT/RUT</dt>
                <dd class="font-medium text-gray-900">{{ $company->nit_rut }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">País</dt>
                <dd class="font-medium text-gray-900">{{ $company->country ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Contacto principal</dt>
                <dd class="font-medium text-gray-900">{{ $company->contact_person_name ?? '—' }}</dd>
                <dd class="text-xs text-gray-500 mt-0.5">Sincronizado con el primer cliente asignado (orden alfabético).</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email principal</dt>
                <dd class="font-medium text-gray-900">
                    @if($company->contact_person_email)
                        <a href="mailto:{{ $company->contact_person_email }}" class="text-teal-600 hover:text-teal-700">{{ $company->contact_person_email }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Teléfono</dt>
                <dd class="font-medium text-gray-900">{{ $company->phone ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    @php
        $assignedClients = $company->users->filter(fn($u) => $u->hasRole('client'));
        $assignedAgents = $company->users->filter(fn($u) => !$u->hasRole('client'));
    @endphp
    {{-- Clientes y especialistas asignados --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Clientes asignados</h3>
                <p class="text-xs text-gray-500 mt-0.5">Usuarios del portal con acceso a esta empresa</p>
            </div>
            <div class="p-4">
                @forelse($assignedClients as $user)
                    <div class="flex items-center justify-between py-2 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                        <div>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600">
                                <a href="mailto:{{ $user->email }}" class="text-teal-600 hover:underline">{{ $user->email }}</a>
                            </p>
                            @if($user->pivot && $user->pivot->description)
                                <p class="text-xs text-amber-900 bg-amber-50 border border-amber-100 rounded px-2 py-1 mt-1 inline-block">{{ $user->pivot->description }}</p>
                            @endif
                            @if($user->phone)
                                <p class="text-xs text-gray-500">{{ $user->phone }}</p>
                            @endif
                        </div>
                        <div>
                            @php
                                $statusLabels = [
                                    'activo' => 'bg-green-100 text-green-800',
                                    'pendiente' => 'bg-amber-100 text-amber-800',
                                    'deshabilitado' => 'bg-red-100 text-red-800',
                                ];
                                $cs = $user->client_status ?? 'activo';
                                $statusStyle = $statusLabels[$cs] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded {{ $statusStyle }}">{{ $cs }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-2">Ningún cliente asignado.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Especialistas asignados</h3>
                <p class="text-xs text-gray-500 mt-0.5">Usuarios del panel que gestionan esta empresa</p>
            </div>
            <div class="p-4">
                @forelse($assignedAgents as $user)
                    <div class="flex items-center justify-between py-2 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                        <div>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600">
                                <a href="mailto:{{ $user->email }}" class="text-teal-600 hover:underline">{{ $user->email }}</a>
                            </p>
                            @if($user->phone)
                                <p class="text-xs text-gray-500">{{ $user->phone }}</p>
                            @endif
                        </div>
                        <div>
                            @php
                                $roleNames = $user->getRoleNames();
                                $roleLabel = $roleNames->isNotEmpty() ? $roleNames->first() : '-';
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800">{{ $roleLabel }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-2">Ningún agente asignado.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- SECCIÓN A: Tarjetas por paso del flujo (KPIs) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100">
                    <i class="fas fa-folder-open text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Casos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $total_processes }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-gray-100">
                    <i class="fas fa-clipboard-list text-gray-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Recolección</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['recoleccion'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-teal-100">
                    <i class="fas fa-paper-plane text-teal-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Sometimiento</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['sometimiento'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100">
                    <i class="fas fa-stamp text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Radicado</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['radicado'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-orange-100">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">AUTO (En Requerimiento)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['requerimiento'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-green-100">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Finalizados</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['finalizado'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN B: Semáforo de Procesos --}}
    <div class="mb-8 bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado general</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <strong>{{ $stats['radicado'] ?? 0 }}</strong> proceso(s) están en Radicación
            </li>
            @if(($alerts ?? 0) > 0)
                <li class="flex items-center gap-2 text-amber-700">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <strong>{{ $alerts }}</strong> proceso(s) en Requerimiento (requieren atención)
                </li>
            @endif
        </ul>
    </div>

    {{-- SECCIÓN C: Consolidado de Procesos (Tabla Maestra) --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-lg font-semibold text-gray-900">Consolidado de Procesos</h3>
            <form method="GET" action="{{ route('admin.companies.show', $company) }}" class="flex flex-wrap items-center gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por solicitud, trámite, producto..."
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-48 focus:ring-teal-500 focus:border-teal-500">
                <select name="step_filter" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Todos los pasos</option>
                    @foreach($availableSteps ?? [] as $num => $label)
                        <option value="{{ $num }}" {{ request('step_filter') == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                @if(request('search') || request('step_filter'))
                    <a href="{{ route('admin.companies.show', $company) }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Trámite</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Hito actual</th>
                        <th class="px-4 py-3">Barra de vida</th>
                        <th class="px-4 py-3 w-28">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processes as $process)
                        @php
                            $tramite = $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '—';
                            $eventos = $process->submissions->flatMap->regulatoryEvents->sortByDesc('event_date');
                            $ultimoEvento = $eventos->first();
                            $ultimaSub = $process->submissions->sortByDesc('fecha_radicacion')->first();
                            if ($ultimoEvento) {
                                if ($ultimoEvento->event_type === \App\Models\RegulatoryEvent::EVENT_TYPE_RESOLUCION) {
                                    $hito = 'Resolución' . ($ultimoEvento->event_date ? ' (' . $ultimoEvento->event_date->format('d/m/Y') . ')' : '');
                                } else {
                                    $dias = $ultimoEvento->event_date ? $ultimoEvento->event_date->diffInDays(now()) : 0;
                                    $hito = 'Auto recibido hace ' . $dias . ' día(s)';
                                }
                            } elseif ($ultimaSub) {
                                $hito = 'Radicado' . ($ultimaSub->fecha_radicacion ? ' (' . $ultimaSub->fecha_radicacion->format('d/m/Y') . ')' : '');
                            } else {
                                $hito = $process->status ?: 'En recolección';
                            }
                            $paso = $process->getCurrentStep();
                        @endphp
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <span class="font-mono text-xs text-gray-700">#{{ $process->id }}</span>
                                @if($process->expediente_invima)
                                    <span class="text-gray-400 mx-1">·</span>
                                    <span class="text-gray-800">{{ $process->expediente_invima }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-800">{{ $tramite }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $stepStyles = [1 => 'bg-gray-100 text-gray-800', 2 => 'bg-teal-100 text-teal-800', 3 => 'bg-blue-100 text-blue-800', 4 => 'bg-orange-100 text-orange-800', 5 => 'bg-green-100 text-green-800'];
                                    $stepStyle = $stepStyles[$paso] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $stepStyle }}" title="Paso {{ $paso }}">{{ $process->getCurrentStepLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $hito }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-0.5 flex-wrap" title="Recolección → Sometimiento → Radicado → AUTO → Finalizado">
                                    @foreach([1 => 'Rec.', 2 => 'Somet.', 3 => 'Radic.', 4 => 'AUTO', 5 => 'Fin'] as $n => $short)
                                        @if($n > 1)<span class="text-gray-300">→</span>@endif
                                        <span class="px-1.5 py-0.5 text-xs rounded {{ $paso >= $n ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">{{ $short }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.processes.show', $process) }}"
                                   class="inline-flex items-center px-2 py-1 bg-teal-600 text-white text-xs font-medium rounded hover:bg-teal-700">
                                    Ver solicitud
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                @if(request('search') || request('step_filter'))
                                    No hay solicitudes que coincidan con el filtro.
                                    <a href="{{ route('admin.companies.show', $company) }}" class="text-teal-600 hover:underline ml-1">Ver todos</a>
                                @else
                                    No hay solicitudes para este cliente.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($canCompaniesEdit)
        @include('admin.companies.partials.invite-modal')
    @endif
@endsection
