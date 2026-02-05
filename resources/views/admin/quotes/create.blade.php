@extends('layouts.admin-flowbite')

@section('title', 'Nueva Cotización - RAMS')

@section('page-title', 'Nueva Cotización')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.quotes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Cotizaciones</a>
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
            <p class="font-medium mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Corrija los errores antes de enviar.</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.quotes.store') }}" method="POST" id="form-quote">
        @csrf

        {{-- Cabecera --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la cotización</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="client_id" class="block mb-2 text-sm font-medium text-gray-900">Cliente <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-900">Moneda <span class="text-red-500">*</span></label>
                    <select name="currency" id="currency" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="COP" {{ old('currency', 'COP') === 'COP' ? 'selected' : '' }}>COP</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div>
                    <label for="consecutive" class="block mb-2 text-sm font-medium text-gray-900">Consecutivo <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutive" id="consecutive" value="{{ old('consecutive') }}" placeholder="Ej: 006-25" required maxlength="32"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
            </div>
        </div>

        {{-- Detalle de ítems (tabla dinámica) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalle de ítems</h3>
                <button type="button" id="btn-add-row" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                    <i class="fas fa-plus mr-2"></i> Agregar fila
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700" id="items-table">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-3 py-2">Tipo de trámite</th>
                            <th class="px-3 py-2">Descripción</th>
                            <th class="px-3 py-2">Valor honorarios</th>
                            <th class="px-3 py-2">Código tasa INVIMA</th>
                            <th class="px-3 py-2">Valor tasa INVIMA</th>
                            <th class="px-3 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @php $oldItems = old('items', [['service_type_id' => '', 'description' => '', 'fee_value' => '', 'invima_rate_code' => '', 'invima_rate_value' => '']]); @endphp
                        @foreach($oldItems as $idx => $item)
                            <tr class="item-row border-b border-gray-200">
                                <td class="px-3 py-2">
                                    <select name="items[{{ $idx }}][service_type_id]" class="item-service-type border border-gray-300 rounded-lg p-2 w-full text-sm" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($serviceTypes as $st)
                                            <option value="{{ $st->id }}" {{ ($item['service_type_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Descripción" maxlength="500"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[{{ $idx }}][fee_value]" value="{{ $item['fee_value'] ?? '' }}" placeholder="0" min="0" step="0.01" required
                                           class="item-fee border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $idx }}][invima_rate_code]" value="{{ $item['invima_rate_code'] ?? '' }}" placeholder="Ej: 4001-37" maxlength="32"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[{{ $idx }}][invima_rate_value]" value="{{ $item['invima_rate_value'] ?? '' }}" placeholder="0" min="0" step="0.01"
                                           class="item-invima border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sección financiera (footer) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen financiero</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total honorarios</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-fees">0</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total tasas INVIMA</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-invima">0</p>
                </div>
                <div>
                    <label for="total_loans" class="block mb-2 text-sm font-medium text-gray-900">Préstamos DV</label>
                    <p class="text-xs text-gray-500 mb-1">Monto que Doble Vía presta para la tasa (ej. cotización 6-25)</p>
                    <input type="number" name="total_loans" id="total_loans" value="{{ old('total_loans', 0) }}" min="0" step="0.01"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-save mr-2"></i> Guardar cotización
            </button>
            <a href="{{ route('admin.quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                Cancelar
            </a>
        </div>
    </form>

    <template id="row-template">
        <tr class="item-row border-b border-gray-200">
            <td class="px-3 py-2">
                <select name="items[__INDEX__][service_type_id]" class="item-service-type border border-gray-300 rounded-lg p-2 w-full text-sm" required>
                    <option value="">Seleccione...</option>
                    @foreach($serviceTypes as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="text" name="items[__INDEX__][description]" placeholder="Descripción" maxlength="500"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[__INDEX__][fee_value]" placeholder="0" min="0" step="0.01" required
                       class="item-fee border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-3 py-2">
                <input type="text" name="items[__INDEX__][invima_rate_code]" placeholder="Ej: 4001-37" maxlength="32"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[__INDEX__][invima_rate_value]" placeholder="0" min="0" step="0.01"
                       class="item-invima border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-3 py-2">
                <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
    (function() {
        const tbody = document.getElementById('items-tbody');
        const template = document.getElementById('row-template');
        const btnAdd = document.getElementById('btn-add-row');
        let rowIndex = {{ count($oldItems) }};

        function getServiceTypesHtml() {
            return @json($serviceTypes->map(fn($st) => ['id' => $st->id, 'name' => $st->name])->values());
        }

        function addRow() {
            const html = template.innerHTML.replace(/__INDEX__/g, rowIndex);
            tbody.insertAdjacentHTML('beforeend', html);
            rowIndex++;
            bindRowEvents(tbody.lastElementChild);
            updateTotals();
        }

        function bindRowEvents(row) {
            const removeBtn = row.querySelector('.btn-remove-row');
            if (removeBtn) removeBtn.addEventListener('click', function() {
                if (tbody.querySelectorAll('.item-row').length <= 1) return;
                row.remove();
                reindexRows();
                updateTotals();
            });
            row.querySelectorAll('.item-fee, .item-invima').forEach(function(input) {
                input.addEventListener('input', updateTotals);
            });
        }

        function reindexRows() {
            const rows = tbody.querySelectorAll('.item-row');
            rowIndex = 0;
            rows.forEach(function(row) {
                row.querySelectorAll('[name^="items["]').forEach(function(el) {
                    el.name = el.name.replace(/items\[\d+\]/, 'items[' + rowIndex + ']');
                });
                rowIndex++;
            });
        }

        function updateTotals() {
            let totalFees = 0, totalInvima = 0;
            tbody.querySelectorAll('.item-row').forEach(function(row) {
                const fee = row.querySelector('.item-fee');
                const invima = row.querySelector('.item-invima');
                totalFees += parseFloat(fee?.value || 0) || 0;
                totalInvima += parseFloat(invima?.value || 0) || 0;
            });
            document.getElementById('display-total-fees').textContent = formatNum(totalFees);
            document.getElementById('display-total-invima').textContent = formatNum(totalInvima);
        }

        function formatNum(n) {
            return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        }

        btnAdd.addEventListener('click', addRow);

        tbody.querySelectorAll('.item-row').forEach(bindRowEvents);
        document.getElementById('total_loans').addEventListener('input', function() {
            document.getElementById('display-total-fees').textContent; // no change, just ensure footer visible
        });
        updateTotals();
    })();
    </script>
    @endpush
@endsection
