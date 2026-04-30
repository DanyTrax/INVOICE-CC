@extends('layouts.admin-flowbite')

@section('title', 'Solicitudes INVIMA - RAMS')

@section('page-title', 'Solicitudes')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Solicitudes</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        @processCan('edit')
        <a href="{{ route('admin.processes.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i> Nueva solicitud
        </a>
        @endprocessCan
    </div>

    {{-- Sección superior: Acordeones por cotización --}}
    @if($grouped_quotes->isNotEmpty())
        <div class="space-y-2 mb-10">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Solicitudes por cotización</h2>
            @foreach($grouped_quotes as $quote)
                <details id="quote-{{ $quote->id }}" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden group">
                    <summary class="flex items-center justify-between px-4 py-3 cursor-pointer list-none bg-gray-50 hover:bg-gray-100 transition-colors">
                        <span class="font-medium text-gray-900">
                            Cotización #{{ $quote->consecutive ?? $quote->id }} | Cliente: {{ $quote->client->name ?? '-' }}
                        </span>
                        <span class="text-teal-600 text-sm font-medium group-open:rotate-180 transition-transform inline-flex items-center gap-1">
                            Ver Detalles <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </summary>
                    <div class="border-t border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 whitespace-nowrap">#</th>
                                        <th class="px-4 py-3">Cliente</th>
                                        <th class="px-4 py-3">Tipo de trámite</th>
                                        <th class="px-4 py-3">Estado</th>
                                        <th class="px-4 py-3">Fecha Último Evento</th>
                                        <th class="px-4 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $processIdsFromItems = $quote->quoteItems->map(fn($i) => $i->process?->id)->filter()->values();
                                    @endphp
                                    @foreach($quote->quoteItems as $item)
                                        @if($item->process)
                                            @php $process = $item->process; @endphp
                                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                                <td class="px-4 py-3 font-mono text-sm text-gray-900" title="{{ $process->expediente_invima ? 'INVIMA: '.$process->expediente_invima.' · ' : '' }}ID interno: {{ $process->id }}">{{ $process->displayReference() }}</td>
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                                                <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                                                <td class="px-4 py-3">
                                                    @php
                                                        $statusStyles = [
                                                            'Recolección' => 'bg-gray-100 text-gray-800',
                                                            'Radicado' => 'bg-blue-100 text-blue-800',
                                                            'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                                            'Finalizado' => 'bg-green-100 text-green-800',
                                                        ];
                                                        $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                                                </td>
                                                <td class="px-4 py-3">{{ $process->updated_at->format('d/m/Y H:i') }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="inline-flex items-center gap-1">
                                                        <a href="{{ route('admin.processes.show', $process) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-teal-200 bg-white text-teal-600 hover:bg-teal-50" title="Ver solicitud">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta solicitud? No se puede deshacer.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Eliminar">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @foreach($quote->processes->whereNotIn('id', $processIdsFromItems) as $process)
                                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-4 py-3 font-mono text-sm text-gray-900" title="{{ $process->expediente_invima ? 'INVIMA: '.$process->expediente_invima.' · ' : '' }}ID interno: {{ $process->id }}">{{ $process->displayReference() }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $statusStyles = [
                                                        'Recolección' => 'bg-gray-100 text-gray-800',
                                                        'Radicado' => 'bg-blue-100 text-blue-800',
                                                        'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                                        'Finalizado' => 'bg-green-100 text-green-800',
                                                    ];
                                                    $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                                            </td>
                                            <td class="px-4 py-3">{{ $process->updated_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3">
                                                <div class="inline-flex items-center gap-1">
                                                    <a href="{{ route('admin.processes.show', $process) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-teal-200 bg-white text-teal-600 hover:bg-teal-50" title="Ver solicitud">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @processCan('delete')
                                                    <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta solicitud? No se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                    @endprocessCan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    @endif

    {{-- Sección inferior: Procesos sin asignar / Huérfanos --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <h2 class="text-lg font-semibold text-gray-900 px-4 py-3 border-b border-gray-200 bg-amber-50/50">
            Procesos Sin Asignar / Huérfanos
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">#</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo de trámite</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha Último Evento</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orphan_processes as $process)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm text-gray-900" title="{{ $process->expediente_invima ? 'INVIMA: '.$process->expediente_invima.' · ' : '' }}ID interno: {{ $process->id }}">{{ $process->displayReference() }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $process->serviceType?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $process->product_reference ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'Recolección' => 'bg-gray-100 text-gray-800',
                                        'Radicado' => 'bg-blue-100 text-blue-800',
                                        'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                        'Finalizado' => 'bg-green-100 text-green-800',
                                    ];
                                    $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $process->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.processes.show', $process) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-teal-200 bg-white text-teal-600 hover:bg-teal-50" title="Ver solicitud">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @processCan('delete')
                                    <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta solicitud? No se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endprocessCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay procesos huérfanos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            var params = new URLSearchParams(window.location.search);
            var openQuote = params.get('open_quote');
            if (openQuote) {
                var el = document.getElementById('quote-' + openQuote);
                if (el) {
                    el.setAttribute('open', 'open');
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        })();
    </script>
    @endpush
@endsection
