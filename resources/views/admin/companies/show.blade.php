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
    {{-- Botones de acción --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.companies.edit', $company) }}"
           class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
            <i class="fas fa-edit mr-2"></i> Editar
        </a>
        <a href="{{ route('admin.processes.index', ['client_id' => $company->id]) }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-folder-open mr-2"></i> Ver Expedientes
        </a>
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
                <dt class="text-gray-500">Contacto</dt>
                <dd class="font-medium text-gray-900">{{ $company->contact_person_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email</dt>
                <dd class="font-medium text-gray-900">
                    @if($company->contact_person_email)
                        <a href="mailto:{{ $company->contact_person_email }}" class="text-teal-600 hover:text-teal-700">{{ $company->contact_person_email }}</a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Teléfono</dt>
                <dd class="font-medium text-gray-900">{{ $company->phone ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- SECCIÓN A: Tarjetas de Estado (KPIs) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100">
                    <i class="fas fa-folder-open text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Casos Activos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $total_processes }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-yellow-100">
                    <i class="fas fa-clipboard-list text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">En Recolección / Checklist</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['recoleccion'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-orange-100">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">En Requerimiento</p>
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
                    <p class="text-sm font-medium text-gray-500">Finalizados / Aprobados</p>
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
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Consolidado de Procesos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Origen</th>
                        <th class="px-4 py-3">Proceso / Producto</th>
                        <th class="px-4 py-3">Hito actual</th>
                        <th class="px-4 py-3">Barra de vida</th>
                        <th class="px-4 py-3 w-28">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($company->processes as $process)
                        @php
                            $origen = $process->quote?->consecutive ?? $process->quoteItem?->quote?->consecutive ?? '-';
                            $producto = $process->product_reference ?: ($process->expediente_invima ?: ($process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? 'Expediente #' . $process->id));
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
                            $tieneResolucion = $eventos->contains('event_type', \App\Models\RegulatoryEvent::EVENT_TYPE_RESOLUCION);
                            $tieneAuto = $eventos->contains('event_type', \App\Models\RegulatoryEvent::EVENT_TYPE_AUTO);
                            $paso = $tieneResolucion || $process->status === \App\Models\Process::STATUS_FINALIZADO ? 4 : ($tieneAuto ? 3 : ($process->submissions->isNotEmpty() ? 2 : 1));
                        @endphp
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $origen }}</td>
                            <td class="px-4 py-3">{{ $producto }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $hito }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-0.5" title="Inicio → Radicado → Auto → Fin">
                                    <span class="px-1.5 py-0.5 text-xs rounded {{ $paso >= 1 ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">Inicio</span>
                                    <span class="text-gray-300">→</span>
                                    <span class="px-1.5 py-0.5 text-xs rounded {{ $paso >= 2 ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">Radicado</span>
                                    <span class="text-gray-300">→</span>
                                    <span class="px-1.5 py-0.5 text-xs rounded {{ $paso >= 3 ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">Auto</span>
                                    <span class="text-gray-300">→</span>
                                    <span class="px-1.5 py-0.5 text-xs rounded {{ $paso >= 4 ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }}">Fin</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.processes.show', $process) }}"
                                   class="inline-flex items-center px-2 py-1 bg-teal-600 text-white text-xs font-medium rounded hover:bg-teal-700">
                                    Ver Expediente
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay expedientes para este cliente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
