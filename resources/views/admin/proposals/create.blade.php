@extends('layouts.admin-flowbite')

@section('title', 'Nueva propuesta - RAMS')

@section('page-title', 'Nueva propuesta')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.proposals.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Propuestas</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Nueva</span>
        </div>
    </li>
@endsection

@section('content')
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.proposals.store') }}" method="POST" id="form-proposal">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la propuesta</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="client_id" class="block mb-2 text-sm font-medium text-gray-900">Cliente <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($companies as $c)
                            @php
                                $clean = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($c->code_abbreviation ?? '')) ?? '');
                                $siglasOk = mb_strlen($clean) >= 2;
                            @endphp
                            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}
                                    data-siglas-ok="{{ $siglasOk ? '1' : '0' }}"
                                    data-code-abbr="{{ $clean }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p id="client_siglas_warn" class="mt-2 hidden text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2" role="status">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Esta empresa no tiene siglas válidas (2–10 letras) para numerar propuestas.
                        <a id="client_siglas_edit_link" href="{{ route('admin.companies.index') }}" class="font-medium underline hover:text-amber-900">Editar empresa</a>
                    </p>
                </div>
                <div>
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-900">Moneda <span class="text-red-500">*</span></label>
                    <select name="currency" id="currency" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                        <option value="COP" {{ old('currency', 'COP') === 'COP' ? 'selected' : '' }}>COP</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div>
                    <label for="consecutive" class="block mb-2 text-sm font-medium text-gray-900">Propuesta No. <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutive" id="consecutive" value="{{ old('consecutive', $suggestedConsecutive ?? '') }}" required maxlength="32" placeholder="Ej: PHILIPS-P-001-26" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-500">Se sugiere automáticamente con las <strong>siglas del cliente</strong> y numeración propia por empresa (ej. PHILIPS-P-001-26). Puede ajustarlo manualmente.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="exchange_rate" class="block mb-2 text-sm font-medium text-gray-900">Tasa de cambio (si aplica)</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate') }}" min="0" step="0.000001" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5" placeholder="USD → COP">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="apply_tax" value="1" id="toggle-apply-tax" class="rounded border-gray-300 text-teal-600" {{ old('apply_tax') ? 'checked' : '' }}>
                    <span>Aplicar impuesto (IVA)</span>
                </label>
                <span id="tax-pct-wrap" class="{{ old('apply_tax') ? '' : 'hidden' }}">
                    <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', '19') }}" min="0" max="100" step="0.01" class="w-24 border rounded-lg px-2 py-1 text-sm"> %
                </span>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="apply_bank_fee" value="1" id="toggle-bank-fee" class="rounded border-gray-300 text-teal-600" {{ old('apply_bank_fee') ? 'checked' : '' }}>
                    <span>Gasto bancario</span>
                </label>
                <span id="bank-fee-wrap" class="{{ old('apply_bank_fee') ? '' : 'hidden' }}">
                    <input type="number" name="bank_fee_value" id="bank_fee_value" value="{{ old('bank_fee_value') }}" min="0" step="0.01" class="w-32 border rounded-lg px-2 py-1 text-sm" placeholder="Valor">
                </span>
            </div>
        </div>

        @include('admin.partials.pdf-document-content-fields', [
            'pdfBodyHtml' => old('pdf_body_html', $defaultPdfTemplate?->body_html ?? ''),
            'pdfSideNoteHtml' => old('pdf_side_note_html', $defaultPdfTemplate?->side_note_html ?? ''),
            'pdfFooter' => old('pdf_footer', $defaultPdfTemplate?->closing_footer_html ?? ''),
        ])

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalle de ítems (concepto, alcance, honorarios)</h3>
                <button type="button" id="btn-add-proposal-row" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                    <i class="fas fa-plus mr-2"></i> Agregar fila
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-3">
                Escriba el <strong>Concepto</strong>; si hay coincidencias en el <a href="{{ route('admin.concept-catalogs.index') }}" class="text-teal-600 hover:underline">catálogo</a>, aparecerán sugerencias.
                Al elegir una, se rellenan alcance y honorarios; puede seguir editando el mismo campo (por ejemplo añadir &quot;NUEVO MODELO&quot;). También puede escribir todo a mano sin usar el catálogo.
            </p>
            <div class="w-full">
                <table class="w-full text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left min-w-[14rem]">Concepto <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2 text-left">Alcance</th>
                            <th class="px-3 py-2 text-right w-36">Honorarios <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="proposal-rows"></tbody>
                </table>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-save mr-2"></i> Guardar propuesta
            </button>
            <a href="{{ route('admin.proposals.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Cancelar</a>
        </div>
    </form>

    @include('admin.partials.client-consecutive-suggest-script', [
        'suggestUrl' => route('admin.proposals.suggest-consecutive'),
    ])
@endsection

@php
    $catalogJs = $conceptCatalog->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'scope' => $c->scope,
        'default_fee' => $c->default_fee,
    ])->values();
@endphp

@push('scripts')
<script>
(function() {
    var PROPOSAL_CATALOG = @json($catalogJs);
    var rowIndex = 0;
    var tbody = document.getElementById('proposal-rows');

    function esc(s) {
        if (s == null) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function getMatches(query) {
        var q = (query || '').trim().toLowerCase();
        if (!q || !PROPOSAL_CATALOG.length) return [];
        return PROPOSAL_CATALOG.filter(function(c) {
            return (c.name || '').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 25);
    }

    function attachConceptAutocomplete(tr) {
        var wrap = tr.querySelector('.concept-autocomplete-wrap');
        if (!wrap) return;
        var input = wrap.querySelector('.concept-input');
        var suggestions = wrap.querySelector('.concept-suggestions');
        if (!input || !suggestions) return;

        function hideSuggestions() {
            suggestions.innerHTML = '';
            suggestions.classList.add('hidden');
            wrap._matches = [];
        }

        function showSuggestions(matches) {
            if (!matches.length) {
                hideSuggestions();
                return;
            }
            wrap._matches = matches;
            suggestions.innerHTML = matches.map(function(c, idx) {
                return '<button type="button" tabindex="-1" class="concept-suggestions-item w-full text-left px-3 py-2 text-sm text-gray-800 hover:bg-teal-50 border-b border-gray-100 last:border-0" data-idx="' + idx + '">' + esc(c.name) + '</button>';
            }).join('');
            suggestions.classList.remove('hidden');
        }

        function applySelection(c) {
            input.value = c.name || '';
            var scope = tr.querySelector('textarea[name$="[scope]"]');
            var fee = tr.querySelector('input[name$="[fee_value]"]');
            if (scope) scope.value = c.scope || '';
            if (fee) {
                if (c.default_fee != null && c.default_fee !== '') {
                    fee.value = c.default_fee;
                }
            }
            hideSuggestions();
            input.focus();
            var len = input.value.length;
            try { input.setSelectionRange(len, len); } catch (e) {}
        }

        input.addEventListener('input', function() {
            var v = input.value;
            if (!v.trim()) {
                hideSuggestions();
                return;
            }
            showSuggestions(getMatches(v));
        });

        input.addEventListener('focus', function() {
            var v = input.value;
            if (v.trim()) {
                showSuggestions(getMatches(v));
            }
        });

        suggestions.addEventListener('mousedown', function(e) {
            e.preventDefault();
            var btn = e.target.closest('.concept-suggestions-item');
            if (!btn) return;
            var idx = parseInt(btn.getAttribute('data-idx'), 10);
            var c = wrap._matches[idx];
            if (c) applySelection(c);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideSuggestions();
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.concept-autocomplete-wrap')) {
            document.querySelectorAll('.concept-suggestions').forEach(function(el) {
                el.innerHTML = '';
                el.classList.add('hidden');
            });
        }
    });

    function addRow() {
        var i = rowIndex++;
        var tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100 proposal-row';
        tr.innerHTML =
            '<td class="px-3 py-2 align-top">' +
                '<div class="concept-autocomplete-wrap relative z-10">' +
                    '<input type="hidden" name="items[' + i + '][item_position]" value="' + (i + 1) + '">' +
                    '<input type="text" name="items[' + i + '][concept]" required maxlength="500" autocomplete="off"' +
                    ' class="concept-input w-full border border-gray-300 rounded-lg text-sm p-2" placeholder="Escriba o busque en el catálogo…">' +
                    '<div class="concept-suggestions hidden absolute left-0 right-0 top-full mt-0.5 max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg"></div>' +
                '</div>' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<textarea name="items[' + i + '][scope]" rows="2" maxlength="5000" class="w-full border border-gray-300 rounded-lg text-sm p-2" placeholder="Alcance"></textarea>' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<input type="number" name="items[' + i + '][fee_value]" required min="0" step="0.01" class="w-full border border-gray-300 rounded-lg text-sm p-2 text-right" placeholder="0.00">' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<button type="button" class="btn-remove text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-times"></i></button>' +
            '</td>';
        tbody.appendChild(tr);
        attachConceptAutocomplete(tr);
        tr.querySelector('.btn-remove').addEventListener('click', function() {
            if (tbody.querySelectorAll('tr').length <= 1) {
                alert('Debe haber al menos un ítem.');
                return;
            }
            tr.remove();
        });
    }

    document.getElementById('btn-add-proposal-row').addEventListener('click', addRow);

    document.getElementById('toggle-apply-tax').addEventListener('change', function() {
        document.getElementById('tax-pct-wrap').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('toggle-bank-fee').addEventListener('change', function() {
        document.getElementById('bank-fee-wrap').classList.toggle('hidden', !this.checked);
    });

    addRow();
})();
</script>
@endpush
