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
        <input type="hidden" name="show_prev_license_column" id="input-show-prev-license" value="0">
        <input type="hidden" name="show_raa_column" id="input-show-raa" value="0">

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
                            <option value="{{ $c->id }}" data-allows-loans="{{ $c->allows_loans ? '1' : '0' }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-900">Moneda de la Oferta <span class="text-red-500">*</span></label>
                    <select name="currency" id="currency" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="COP" {{ old('currency', 'COP') === 'COP' ? 'selected' : '' }}>COP</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div>
                    <label for="consecutive" class="block mb-2 text-sm font-medium text-gray-900">COTIZACIÓN No. <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutive" id="consecutive"
                           value="{{ old('consecutive', $suggestedConsecutive ?? '') }}"
                           placeholder="Ej: 001-26" required maxlength="32"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-500">Se sugiere automáticamente el siguiente número de la forma NNN-AA (número + último dígito del año), pero puedes ajustarlo si lo necesitas.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="exchange_rate" class="block mb-2 text-sm font-medium text-gray-900">Tasa de cambio (si aplica)</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate') }}" min="0" step="0.000001"
                           placeholder="Ej: 4000.50 para ofertas en USD"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-500">Use este campo cuando la moneda sea USD para registrar la tasa COP/USD usada en la oferta.</p>
                </div>
            </div>
        </div>

        {{-- Detalle de ítems (tabla dinámica) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalle de ítems</h3>
                <div class="flex gap-2">
                    <button type="button" id="btn-add-row" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                        <i class="fas fa-plus mr-2"></i> Agregar fila
                    </button>
                    <button type="button" id="btn-add-loan-row" class="hidden inline-flex items-center px-3 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700" title="Ítem de préstamo (suplido)">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Agregar ítem de préstamo
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 mb-3 text-sm text-gray-700">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-prev-license" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span>Usar columna Expediente / INVIMA</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-raa" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span>Usar columna RAA</span>
                </label>
                <label class="inline-flex items-center gap-2 ml-4">
                    <input type="checkbox" name="apply_tax" id="toggle-apply-tax" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span>Aplicar impuesto (IVA)</span>
                </label>
                <span id="tax-pct-wrap" class="hidden">
                    <label for="tax_percentage" class="sr-only">Porcentaje IVA</label>
                    <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', '19') }}" min="0" max="100" step="0.01" placeholder="%"
                           class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm"> %
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 min-w-[900px]" id="items-table">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-2 py-2 w-12">#</th>
                            <th class="px-2 py-2">Tipo de trámite</th>
                            <th class="px-2 py-2">Producto / Descripción</th>
                            <th class="px-2 py-2" data-col="prev-license">Expediente / INVIMA</th>
                            <th class="px-2 py-2 w-20" data-col="raa">RAA</th>
                            <th class="px-2 py-2">Alcance</th>
                            <th class="px-2 py-2 w-28">Valor</th>
                            <th class="px-2 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @php
                            $oldItems = old('items', []);
                            if (empty($oldItems)) {
                                $oldItems = [array_merge([
                                    'item_position' => 1, 'service_type_id' => '', 'description' => '', 'previous_license' => '', 'raa_code' => '', 'scope' => '',
                                    'fee_value' => '', 'invima_rate_code' => '', 'invima_rate_value' => '', 'is_loan' => 0,
                                ], [])];
                            }
                        @endphp
                        @foreach($oldItems as $idx => $item)
                            <tr class="item-row border-b border-gray-200 {{ !empty($item['is_loan']) ? 'bg-amber-50' : '' }}" data-is-loan="{{ !empty($item['is_loan']) ? '1' : '0' }}">
                                <td class="px-2 py-2 item-num">{{ $idx + 1 }}</td>
                                <td class="px-2 py-2">
                                    <input type="hidden" name="items[{{ $idx }}][item_position]" value="{{ $idx + 1 }}">
                                    <input type="hidden" name="items[{{ $idx }}][is_loan]" value="{{ !empty($item['is_loan']) ? '1' : '0' }}" class="item-is-loan-input">
                                    <textarea
                                        name="items[{{ $idx }}][service_type_name]"
                                        list="service_types_datalist"
                                        rows="2"
                                        placeholder="Tipo de trámite"
                                        class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y">{{ $item['service_type_name'] ?? '' }}</textarea>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Producto / Descripción" maxlength="500"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="prev-license">
                                    <input type="text" name="items[{{ $idx }}][previous_license]" value="{{ $item['previous_license'] ?? '' }}" placeholder="Ej: 2021DM-0006049" maxlength="64"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="raa">
                                    <input type="text" name="items[{{ $idx }}][raa_code]" value="{{ $item['raa_code'] ?? '' }}" placeholder="Ej: 141153" maxlength="64"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    <textarea name="items[{{ $idx }}][scope]" rows="2" placeholder="Alcance" maxlength="1000"
                                              class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y">{{ $item['scope'] ?? '' }}</textarea>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" name="items[{{ $idx }}][fee_value]" value="{{ $item['fee_value'] ?? '' }}" placeholder="0" min="0" step="0.01" required
                                           class="item-value border border-gray-300 rounded-lg p-2 w-full text-sm">
                                    <input type="hidden" name="items[{{ $idx }}][invima_rate_code]" value="{{ $item['invima_rate_code'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $idx }}][invima_rate_value]" value="{{ $item['invima_rate_value'] ?? '0' }}">
                                </td>
                                <td class="px-2 py-2">
                                    <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumen financiero (footer) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen financiero</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total honorarios</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-fees">0</p>
                </div>
                <div class="bg-amber-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total suplidos / Préstamos</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-loans">0</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Tasas INVIMA</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-invima">0</p>
                </div>
            </div>
            <div id="resumen-sin-iva" class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">Total</p>
                <p class="text-2xl font-bold text-teal-800" id="display-grand-total">0</p>
            </div>
            <div id="resumen-con-iva" class="mt-4 pt-4 border-t border-gray-200 hidden">
                <p class="text-sm text-gray-600">Sub-total</p>
                <p class="text-xl font-semibold text-gray-900" id="display-subtotal">0</p>
                <p class="text-sm text-gray-600 mt-2">IVA (<span id="display-iva-pct">0</span>%)</p>
                <p class="text-xl font-semibold text-gray-900" id="display-iva-amount">0</p>
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800" id="display-total-with-tax">0</p>
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

    {{-- Sugerencias de tipos de trámite (datalist) --}}
    <datalist id="service_types_datalist">
        @foreach($serviceTypes as $st)
            <option value="{{ $st->name }}"></option>
        @endforeach
    </datalist>

    <template id="row-template-normal">
        <tr class="item-row border-b border-gray-200" data-is-loan="0">
            <td class="px-2 py-2 item-num"></td>
            <td class="px-2 py-2">
                <input type="hidden" name="items[__INDEX__][item_position]" value="0" class="item-position-input">
                <input type="hidden" name="items[__INDEX__][is_loan]" value="0" class="item-is-loan-input">
                <textarea
                    name="items[__INDEX__][service_type_name]"
                    list="service_types_datalist"
                    rows="2"
                    placeholder="Tipo de trámite"
                    class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y"></textarea>
            </td>
            <td class="px-2 py-2">
                <input type="text" name="items[__INDEX__][description]" placeholder="Producto / Descripción" maxlength="500"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="prev-license">
                <input type="text" name="items[__INDEX__][previous_license]" placeholder="Ej: 2021DM-0006049" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="raa">
                <input type="text" name="items[__INDEX__][raa_code]" placeholder="Ej: 141153" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2">
                <textarea name="items[__INDEX__][scope]" rows="2" placeholder="Alcance" maxlength="1000"
                          class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y"></textarea>
            </td>
            <td class="px-2 py-2">
                <input type="number" name="items[__INDEX__][fee_value]" placeholder="0" min="0" step="0.01" required
                       class="item-value border border-gray-300 rounded-lg p-2 w-full text-sm">
                <input type="hidden" name="items[__INDEX__][invima_rate_code]" value="">
                <input type="hidden" name="items[__INDEX__][invima_rate_value]" value="0">
            </td>
            <td class="px-2 py-2">
                <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>
    <template id="row-template-loan">
        <tr class="item-row border-b border-gray-200 bg-amber-50" data-is-loan="1">
            <td class="px-2 py-2 item-num"></td>
            <td class="px-2 py-2">
                <input type="hidden" name="items[__INDEX__][item_position]" value="0" class="item-position-input">
                <input type="hidden" name="items[__INDEX__][is_loan]" value="1" class="item-is-loan-input">
                <textarea
                    name="items[__INDEX__][service_type_name]"
                    list="service_types_datalist"
                    rows="2"
                    placeholder="Tipo de trámite (préstamo)"
                    class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm bg-amber-50 resize-y"></textarea>
            </td>
            <td class="px-2 py-2">
                <input type="text" name="items[__INDEX__][description]" placeholder="Préstamo / Suplido" maxlength="500"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm bg-amber-50">
            </td>
            <td class="px-2 py-2" data-col="prev-license">
                <input type="text" name="items[__INDEX__][previous_license]" placeholder="-" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="raa">
                <input type="text" name="items[__INDEX__][raa_code]" placeholder="-" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2">
                <textarea name="items[__INDEX__][scope]" rows="2" placeholder="Alcance" maxlength="1000"
                          class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y"></textarea>
            </td>
            <td class="px-2 py-2">
                <input type="number" name="items[__INDEX__][fee_value]" placeholder="0" min="0" step="0.01" required
                       class="item-value border border-gray-300 rounded-lg p-2 w-full text-sm">
                <input type="hidden" name="items[__INDEX__][invima_rate_code]" value="">
                <input type="hidden" name="items[__INDEX__][invima_rate_value]" value="0">
            </td>
            <td class="px-2 py-2">
                <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
    (function() {
        const tbody = document.getElementById('items-tbody');
        const templateNormal = document.getElementById('row-template-normal');
        const templateLoan = document.getElementById('row-template-loan');
        const btnAdd = document.getElementById('btn-add-row');
        const btnAddLoan = document.getElementById('btn-add-loan-row');
        const clientSelect = document.getElementById('client_id');
        const togglePrev = document.getElementById('toggle-prev-license');
        const toggleRaa = document.getElementById('toggle-raa');
        const toggleApplyTax = document.getElementById('toggle-apply-tax');
        const taxPctWrap = document.getElementById('tax-pct-wrap');
        const inputShowPrevLicense = document.getElementById('input-show-prev-license');
        const inputShowRaa = document.getElementById('input-show-raa');
        let rowIndex = {{ count($oldItems) }};

        function syncColumnHiddenInputs() {
            if (inputShowPrevLicense) inputShowPrevLicense.value = togglePrev?.checked ? '1' : '0';
            if (inputShowRaa) inputShowRaa.value = toggleRaa?.checked ? '1' : '0';
        }

        function updateTaxSectionVisibility() {
            const applyTax = toggleApplyTax?.checked;
            if (taxPctWrap) taxPctWrap.classList.toggle('hidden', !applyTax);
            const sinIva = document.getElementById('resumen-sin-iva');
            const conIva = document.getElementById('resumen-con-iva');
            if (sinIva) sinIva.classList.toggle('hidden', !!applyTax);
            if (conIva) conIva.classList.toggle('hidden', !applyTax);
            updateTotals();
        }

        function updateLoanButtonVisibility() {
            const opt = clientSelect.options[clientSelect.selectedIndex];
            const allowsLoans = opt && opt.getAttribute('data-allows-loans') === '1';
            if (allowsLoans) {
                btnAddLoan.classList.remove('hidden');
            } else {
                btnAddLoan.classList.add('hidden');
            }
        }

        function setColumnEnabled(key, enabled) {
            document.querySelectorAll('[data-col=\"' + key + '\"]').forEach(function(cell) {
                if (enabled) {
                    cell.classList.remove('hidden');
                } else {
                    cell.classList.add('hidden');
                }
                cell.querySelectorAll('input,textarea,select').forEach(function(el) {
                    el.disabled = !enabled;
                });
            });
        }

        function updateColumnVisibility() {
            if (!togglePrev || !toggleRaa) return;
            setColumnEnabled('prev-license', togglePrev.checked);
            setColumnEnabled('raa', toggleRaa.checked);
            syncColumnHiddenInputs();
        }

        function addRow(isLoan) {
            const template = isLoan ? templateLoan : templateNormal;
            const html = template.innerHTML.replace(/__INDEX__/g, rowIndex);
            tbody.insertAdjacentHTML('beforeend', html);
            const row = tbody.lastElementChild;
            const posInput = row.querySelector('.item-position-input');
            if (posInput) posInput.value = rowIndex + 1;
            rowIndex++;
            updateRowNumbers();
            bindRowEvents(row);
            updateColumnVisibility(); // aplicar visibilidad de columnas a la nueva fila
            updateTotals();
        }

        function autoResize(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight || 0) + 'px';
        }

        function bindRowEvents(row) {
            const removeBtn = row.querySelector('.btn-remove-row');
            if (removeBtn) removeBtn.addEventListener('click', function() {
                if (tbody.querySelectorAll('.item-row').length <= 1) return;
                row.remove();
                reindexRows();
                updateTotals();
            });
            row.querySelector('.item-value').addEventListener('input', updateTotals);
            row.querySelectorAll('textarea.js-autoresize').forEach(function(ta) {
                autoResize(ta);
                ta.addEventListener('input', function() { autoResize(ta); });
            });
        }

        function updateRowNumbers() {
            tbody.querySelectorAll('.item-row').forEach(function(row, i) {
                const numCell = row.querySelector('.item-num');
                if (numCell) numCell.textContent = i + 1;
            });
        }

        function reindexRows() {
            const rows = tbody.querySelectorAll('.item-row');
            rowIndex = 0;
            rows.forEach(function(row) {
                row.querySelectorAll('[name^="items["]').forEach(function(el) {
                    el.name = el.name.replace(/items\[\d+\]/, 'items[' + rowIndex + ']');
                });
                const posInput = row.querySelector('.item-position-input');
                if (posInput) posInput.value = rowIndex + 1;
                rowIndex++;
            });
            updateRowNumbers();
            updateTotals();
        }

        function updateTotals() {
            let totalFees = 0, totalLoans = 0, totalInvima = 0;
            tbody.querySelectorAll('.item-row').forEach(function(row) {
                const val = parseFloat(row.querySelector('.item-value')?.value || 0) || 0;
                const invimaInput = row.querySelector('input[name$="[invima_rate_value]"]');
                if (invimaInput) totalInvima += parseFloat(invimaInput.value || 0) || 0;
                const isLoan = row.getAttribute('data-is-loan') === '1';
                if (isLoan) totalLoans += val;
                else totalFees += val;
            });
            const subtotal = totalFees + totalLoans + totalInvima;
            const applyTax = toggleApplyTax?.checked;
            const taxPct = parseFloat(document.getElementById('tax_percentage')?.value || 0) || 0;
            const ivaAmount = applyTax ? Math.round(subtotal * taxPct / 100 * 100) / 100 : 0;
            const totalWithTax = subtotal + ivaAmount;

            const feesEl = document.getElementById('display-total-fees');
            const loansEl = document.getElementById('display-total-loans');
            const invimaEl = document.getElementById('display-total-invima');
            if (feesEl) feesEl.textContent = formatNum(totalFees);
            if (loansEl) loansEl.textContent = formatNum(totalLoans);
            if (invimaEl) invimaEl.textContent = formatNum(totalInvima);

            const grandEl = document.getElementById('display-grand-total');
            const subtotalEl = document.getElementById('display-subtotal');
            const ivaPctEl = document.getElementById('display-iva-pct');
            const ivaAmountEl = document.getElementById('display-iva-amount');
            const totalWithTaxEl = document.getElementById('display-total-with-tax');
            if (applyTax) {
                if (subtotalEl) subtotalEl.textContent = formatNum(subtotal);
                if (ivaPctEl) ivaPctEl.textContent = formatNum(taxPct);
                if (ivaAmountEl) ivaAmountEl.textContent = formatNum(ivaAmount);
                if (totalWithTaxEl) totalWithTaxEl.textContent = formatNum(totalWithTax);
            } else {
                if (grandEl) grandEl.textContent = formatNum(subtotal);
            }
        }

        function formatNum(n) {
            return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        }

        clientSelect.addEventListener('change', updateLoanButtonVisibility);
        btnAdd.addEventListener('click', function() { addRow(false); });
        btnAddLoan.addEventListener('click', function() { addRow(true); });
        if (togglePrev) togglePrev.addEventListener('change', updateColumnVisibility);
        if (toggleRaa) toggleRaa.addEventListener('change', updateColumnVisibility);
        if (toggleApplyTax) toggleApplyTax.addEventListener('change', updateTaxSectionVisibility);
        document.getElementById('tax_percentage')?.addEventListener('input', updateTotals);
        document.getElementById('form-quote').addEventListener('submit', syncColumnHiddenInputs);

        tbody.querySelectorAll('.item-row').forEach(bindRowEvents);
        updateRowNumbers();
        updateLoanButtonVisibility();
        updateColumnVisibility();
        updateTaxSectionVisibility();
        updateTotals();
    })();
    </script>
    @endpush
@endsection
