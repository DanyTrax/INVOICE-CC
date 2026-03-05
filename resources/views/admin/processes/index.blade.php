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
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="{{ route('admin.processes.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i> Nuevo Expediente
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
                                        <th class="px-4 py-3">Expediente</th>
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
                                                <td class="px-4 py-3">
                                                    <span class="font-mono text-xs text-gray-700">#{{ $process->id }}</span>
                                                    @if($process->expediente_invima)
                                                        <span class="text-gray-400 mx-1">·</span>
                                                        <span class="text-gray-800">{{ $process->expediente_invima }}</span>
                                                    @endif
                                                </td>
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
                                                    <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium"><i class="fas fa-eye mr-1"></i> Ver</a>
                                                    <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar este expediente? No se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium"><i class="fas fa-trash-alt mr-1"></i> Eliminar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @foreach($quote->processes->whereNotIn('id', $processIdsFromItems) as $process)
                                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs text-gray-700">#{{ $process->id }}</span>
                                                @if($process->expediente_invima)
                                                    <span class="text-gray-400 mx-1">·</span>
                                                    <span class="text-gray-800">{{ $process->expediente_invima }}</span>
                                                @endif
                                            </td>
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
                                                <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium"><i class="fas fa-eye mr-1"></i> Ver</a>
                                                <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar este expediente? No se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium"><i class="fas fa-trash-alt mr-1"></i> Eliminar</button>
                                                </form>
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
                        <th class="px-4 py-3">Expediente</th>
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
                                <span class="font-mono text-xs text-gray-700">#{{ $process->id }}</span>
                                @if($process->expediente_invima)
                                    <span class="text-gray-400 mx-1">·</span>
                                    <span class="text-gray-800">{{ $process->expediente_invima }}</span>
                                @endif
                            </td>
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
                                <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium mr-3"><i class="fas fa-eye mr-1"></i> Ver</a>
                                <button type="button"
                                        onclick="openAssignModal({{ $process->id }}, '{{ addslashes($process->client->name ?? 'Expediente') }}')"
                                        class="text-amber-700 hover:text-amber-800 font-medium mr-3">
                                    <i class="fas fa-link mr-1"></i> Asignar a Cotización
                                </button>
                                <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este expediente? No se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium"><i class="fas fa-trash-alt mr-1"></i> Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay procesos huérfanos.</td>
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
                <form id="form-assign-quote" method="post" action="" data-action-template="{{ str_replace('999', ':id', route('admin.processes.link-to-quote', ['process' => 999])) }}"
                      data-quotes="{{ $quotes_for_assign->map(fn($q) => ['id' => $q->id, 'consecutive' => $q->consecutive ?? (string)$q->id, 'client' => $q->client->name ?? '-', 'status' => $q->status ?? '-'])->values()->toJson() }}">
                    @csrf
                    <input type="hidden" name="quote_id" id="assign-quote-id" value="">
                    <div class="mb-3">
                        <label for="quote-search-input" class="block text-sm font-medium text-gray-700 mb-1">Número de cotización</label>
                        <input type="text"
                               id="quote-search-input"
                               autocomplete="off"
                               placeholder="Ej: 002-26 o 002"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div id="quote-search-results" class="mb-4 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-gray-50 hidden">
                        {{-- Se llena por JS al escribir --}}
                    </div>
                    <div id="quote-selected-display" class="mb-4 hidden p-3 rounded-lg bg-teal-50 border border-teal-200">
                        <p class="text-xs font-medium text-teal-800 uppercase tracking-wide mb-1">Seleccionado</p>
                        <p id="quote-selected-text" class="text-sm font-medium text-gray-900"></p>
                        <button type="button" onclick="clearQuoteSelection()" class="mt-2 text-xs text-teal-600 hover:text-teal-800">Cambiar cotización</button>
                    </div>
                    @if($quotes_for_assign->isEmpty())
                        <p class="mb-4 text-xs text-amber-600">No hay cotizaciones creadas.</p>
                    @endif
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeAssignModal()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" id="assign-quote-submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed" @if($quotes_for_assign->isEmpty()) disabled @endif>
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
            clearQuoteSelection();
            document.getElementById('quote-search-input').value = '';
            document.getElementById('modal-assign-quote').classList.remove('hidden');
        }
        function closeAssignModal() {
            document.getElementById('modal-assign-quote').classList.add('hidden');
        }
        var quotesData = [];
        var formEl = document.getElementById('form-assign-quote');
        if (formEl && formEl.getAttribute('data-quotes')) {
            try { quotesData = JSON.parse(formEl.getAttribute('data-quotes')); } catch (e) {}
        }
        var searchInput = document.getElementById('quote-search-input');
        var resultsDiv = document.getElementById('quote-search-results');
        var selectedDisplay = document.getElementById('quote-selected-display');
        var selectedText = document.getElementById('quote-selected-text');
        var hiddenQuoteId = document.getElementById('assign-quote-id');
        var submitBtn = document.getElementById('assign-quote-submit');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var q = (this.value || '').trim().toLowerCase();
                hiddenQuoteId.value = '';
                selectedDisplay.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = true;
                if (!q) {
                    resultsDiv.classList.add('hidden');
                    resultsDiv.innerHTML = '';
                    return;
                }
                var matches = quotesData.filter(function(quote) {
                    var consec = (quote.consecutive || '').toLowerCase();
                    var client = (quote.client || '').toLowerCase();
                    return consec.indexOf(q) !== -1 || (quote.id + '').indexOf(q) !== -1 || client.indexOf(q) !== -1;
                });
                resultsDiv.innerHTML = '';
                if (matches.length === 0) {
                    resultsDiv.classList.remove('hidden');
                    resultsDiv.innerHTML = '<p class="p-3 text-sm text-gray-500">No hay cotizaciones que coincidan.</p>';
                } else {
                    resultsDiv.classList.remove('hidden');
                    matches.forEach(function(quote) {
                        var row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'w-full text-left px-3 py-2 text-sm hover:bg-teal-100 border-b border-gray-100 last:border-0';
                        row.textContent = '#' + quote.consecutive + ' | Cliente: ' + quote.client + ' | Estado: ' + quote.status;
                        row.dataset.quoteId = quote.id;
                        row.dataset.label = '#' + quote.consecutive + ' | Cliente: ' + quote.client + ' | Estado: ' + quote.status;
                        row.addEventListener('click', function() {
                            hiddenQuoteId.value = this.dataset.quoteId;
                            selectedText.textContent = this.dataset.label;
                            selectedDisplay.classList.remove('hidden');
                            resultsDiv.classList.add('hidden');
                            resultsDiv.innerHTML = '';
                            searchInput.value = '';
                            if (submitBtn) submitBtn.disabled = false;
                        });
                        resultsDiv.appendChild(row);
                    });
                }
            });
            searchInput.addEventListener('focus', function() {
                if (this.value.trim() && !hiddenQuoteId.value) {
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        }
        function clearQuoteSelection() {
            hiddenQuoteId.value = '';
            selectedDisplay.classList.add('hidden');
            selectedText.textContent = '';
            resultsDiv.classList.add('hidden');
            resultsDiv.innerHTML = '';
            if (searchInput) searchInput.value = '';
            if (submitBtn) submitBtn.disabled = true;
        }
    </script>
    @endpush
@endsection
