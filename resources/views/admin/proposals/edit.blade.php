@extends('layouts.admin-flowbite')

@section('title', 'Editar propuesta - RAMS')

@section('page-title', 'Editar propuesta')

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
            <a href="{{ route('admin.proposals.show', $proposal) }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">{{ $proposal->consecutive }}</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Editar</span>
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

    <form action="{{ route('admin.proposals.update', $proposal) }}" method="POST" id="form-proposal">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la propuesta</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="client_id" class="block mb-2 text-sm font-medium text-gray-900">Cliente <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('client_id', $proposal->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', $proposal->date?->format('Y-m-d')) }}" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-900">Moneda <span class="text-red-500">*</span></label>
                    <select name="currency" id="currency" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                        <option value="COP" {{ old('currency', $proposal->currency) === 'COP' ? 'selected' : '' }}>COP</option>
                        <option value="USD" {{ old('currency', $proposal->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div>
                    <label for="consecutive" class="block mb-2 text-sm font-medium text-gray-900">Propuesta No. <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutive" id="consecutive" value="{{ old('consecutive', $proposal->consecutive) }}" required maxlength="32" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div class="md:col-span-2">
                    <label for="exchange_rate" class="block mb-2 text-sm font-medium text-gray-900">Tasa de cambio (si aplica)</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate', $proposal->exchange_rate) }}" min="0" step="0.000001" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="apply_tax" value="1" id="toggle-apply-tax" class="rounded border-gray-300 text-teal-600" {{ old('apply_tax', $proposal->apply_tax) ? 'checked' : '' }}>
                    <span>Aplicar impuesto (IVA)</span>
                </label>
                <span id="tax-pct-wrap" class="{{ old('apply_tax', $proposal->apply_tax) ? '' : 'hidden' }}">
                    <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', $proposal->tax_percentage ?? 19) }}" min="0" max="100" step="0.01" class="w-24 border rounded-lg px-2 py-1 text-sm"> %
                </span>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="apply_bank_fee" value="1" id="toggle-bank-fee" class="rounded border-gray-300 text-teal-600" {{ old('apply_bank_fee', $proposal->apply_bank_fee) ? 'checked' : '' }}>
                    <span>Gasto bancario</span>
                </label>
                <span id="bank-fee-wrap" class="{{ old('apply_bank_fee', $proposal->apply_bank_fee) ? '' : 'hidden' }}">
                    <input type="number" name="bank_fee_value" id="bank_fee_value" value="{{ old('bank_fee_value', $proposal->bank_fee_value) }}" min="0" step="0.01" class="w-32 border rounded-lg px-2 py-1 text-sm">
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalle de ítems</h3>
                <button type="button" id="btn-add-proposal-row" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                    <i class="fas fa-plus mr-2"></i> Agregar fila
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left w-48">Desde catálogo</th>
                            <th class="px-3 py-2 text-left">Concepto <span class="text-red-500">*</span></th>
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
                <i class="fas fa-save mr-2"></i> Guardar cambios
            </button>
            <a href="{{ route('admin.proposals.show', $proposal) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Cancelar</a>
        </div>
    </form>
@endsection

@php
    $catalogJs = $conceptCatalog->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'scope' => $c->scope,
        'default_fee' => $c->default_fee,
    ])->values();
    $existingItems = $proposal->proposalItems->map(fn ($it) => [
        'id' => $it->id,
        'concept_catalog_id' => $it->concept_catalog_id,
        'item_position' => $it->item_position,
        'concept' => $it->concept,
        'scope' => $it->scope,
        'fee_value' => $it->fee_value,
    ])->values();
@endphp

@push('scripts')
<script>
(function() {
    var PROPOSAL_CATALOG = @json($catalogJs);
    var EXISTING_ITEMS = @json($existingItems);
    var rowIndex = 0;
    var tbody = document.getElementById('proposal-rows');

    function esc(s) {
        if (s == null) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function buildCatalogOptions(selectedId) {
        var html = '<option value="">— Manual / sin catálogo —</option>';
        PROPOSAL_CATALOG.forEach(function(c) {
            var sel = selectedId && String(selectedId) === String(c.id) ? ' selected' : '';
            html += '<option value="' + c.id + '"' + sel + '>' + esc(c.name) + '</option>';
        });
        return html;
    }

    function addRow(data) {
        data = data || {};
        var i = rowIndex++;
        var tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100 proposal-row';
        var idHidden = data.id ? '<input type="hidden" name="items[' + i + '][id]" value="' + data.id + '">' : '';
        tr.innerHTML =
            '<td class="px-3 py-2 align-top">' +
                '<select name="items[' + i + '][concept_catalog_id]" class="catalog-select w-full border border-gray-300 rounded-lg text-sm p-2">' + buildCatalogOptions(data.concept_catalog_id) + '</select>' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                idHidden +
                '<input type="hidden" name="items[' + i + '][item_position]" value="' + (data.item_position || (i + 1)) + '">' +
                '<input type="text" name="items[' + i + '][concept]" required maxlength="500" class="w-full border border-gray-300 rounded-lg text-sm p-2">' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<textarea name="items[' + i + '][scope]" rows="2" maxlength="5000" class="w-full border border-gray-300 rounded-lg text-sm p-2"></textarea>' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<input type="number" name="items[' + i + '][fee_value]" required min="0" step="0.01" class="w-full border border-gray-300 rounded-lg text-sm p-2 text-right">' +
            '</td>' +
            '<td class="px-3 py-2 align-top">' +
                '<button type="button" class="btn-remove text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-times"></i></button>' +
            '</td>';
        tbody.appendChild(tr);
        tr.querySelector('input[name$="[concept]"]').value = data.concept || '';
        tr.querySelector('textarea[name$="[scope]"]').value = data.scope || '';
        var feeIn = tr.querySelector('input[name$="[fee_value]"]');
        if (data.fee_value != null && data.fee_value !== '') {
            feeIn.value = data.fee_value;
        }
        tr.querySelector('.catalog-select').addEventListener('change', onCatalogChange);
        tr.querySelector('.btn-remove').addEventListener('click', function() {
            if (tbody.querySelectorAll('tr').length <= 1) {
                alert('Debe haber al menos un ítem.');
                return;
            }
            tr.remove();
        });
    }

    function onCatalogChange(e) {
        var sel = e.target;
        var id = sel.value;
        var tr = sel.closest('tr');
        if (!id) return;
        var c = PROPOSAL_CATALOG.find(function(x) { return String(x.id) === String(id); });
        if (!c) return;
        tr.querySelector('input[name$="[concept]"]').value = c.name || '';
        tr.querySelector('textarea[name$="[scope]"]').value = c.scope || '';
        var feeIn = tr.querySelector('input[name$="[fee_value]"]');
        if (c.default_fee != null && c.default_fee !== '') {
            feeIn.value = c.default_fee;
        }
    }

    document.getElementById('btn-add-proposal-row').addEventListener('click', function() { addRow(); });

    document.getElementById('toggle-apply-tax').addEventListener('change', function() {
        document.getElementById('tax-pct-wrap').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('toggle-bank-fee').addEventListener('change', function() {
        document.getElementById('bank-fee-wrap').classList.toggle('hidden', !this.checked);
    });

    if (EXISTING_ITEMS.length) {
        EXISTING_ITEMS.forEach(function(it) { addRow(it); });
    } else {
        addRow();
    }
})();
</script>
@endpush
