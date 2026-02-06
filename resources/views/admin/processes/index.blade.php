@extends('layouts.admin-flowbite')

@section('title', 'Expedientes INVIMA - RAMS')

@section('page-title', 'Expedientes')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Expedientes</span>
        </div>
    </li>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="{{ route('admin.processes.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i> Nuevo Proceso
        </a>
    </div>

    {{-- Sección superior: Acordeones por cotización --}}
    @if($grouped_quotes->isNotEmpty())
        <div class="space-y-2 mb-10">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Expedientes por cotización</h2>
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
                                        <th class="px-4 py-3">Cliente</th>
                                        <th class="px-4 py-3">Tipo servicio</th>
                                        <th class="px-4 py-3">Expediente INVIMA</th>
                                        <th class="px-4 py-3">Estado</th>
                                        <th class="px-4 py-3">Actualizado</th>
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
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                                                <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                                                <td class="px-4 py-3">{{ $process->expediente_invima ?? '-' }}</td>
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
                                                    <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium">
                                                        <i class="fas fa-eye mr-1"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @foreach($quote->processes->whereNotIn('id', $processIdsFromItems) as $process)
                                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $process->expediente_invima ?? '-' }}</td>
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
                                                <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium">
                                                    <i class="fas fa-eye mr-1"></i> Ver
                                                </a>
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
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo de trámite</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orphan_processes as $process)
                        <tr class="bg-white border-b hover:bg-gray-50">
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
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium mr-3">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                                <button type="button"
                                        onclick="openAssignModal({{ $process->id }}, '{{ addslashes($process->client->name ?? 'Expediente') }}')"
                                        class="text-amber-700 hover:text-amber-800 font-medium">
                                    <i class="fas fa-link mr-1"></i> Asignar a Cotización
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay procesos huérfanos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal: Asignar proceso a cotización --}}
    <div id="modal-assign-quote" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeAssignModal()"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Asignar a Cotización</h4>
                <p id="modal-assign-process-label" class="text-sm text-gray-600 mb-4"></p>
                <form id="form-assign-quote" method="post" action="" data-action-template="{{ str_replace('999', ':id', route('admin.processes.link-to-quote', ['process' => 999])) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="assign-quote-id" class="block text-sm font-medium text-gray-700 mb-1">Cotización</label>
                        <select name="quote_id" id="assign-quote-id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Seleccione una cotización...</option>
                            @foreach($quotes_for_assign as $q)
                                <option value="{{ $q->id }}">#{{ $q->consecutive ?? $q->id }} | Cliente: {{ $q->client->name ?? '-' }} | Estado: {{ $q->status ?? '-' }}</option>
                            @endforeach
                        </select>
                        @if($quotes_for_assign->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">No hay cotizaciones creadas.</p>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeAssignModal()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700" @if($quotes_for_assign->isEmpty()) disabled @endif>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
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
        function openAssignModal(processId, processLabel) {
            var form = document.getElementById('form-assign-quote');
            form.action = form.getAttribute('data-action-template').replace(':id', processId);
            document.getElementById('modal-assign-process-label').textContent = 'Expediente: ' + processLabel;
            document.getElementById('modal-assign-quote').classList.remove('hidden');
        }
        function closeAssignModal() {
            document.getElementById('modal-assign-quote').classList.add('hidden');
        }
    </script>
    @endpush
@endsection
