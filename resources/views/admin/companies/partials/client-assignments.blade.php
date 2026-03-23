@php
    $assignments = $assignments ?? [['user_id' => '', 'description' => '']];
    if (! is_array($assignments) || count($assignments) === 0) {
        $assignments = [['user_id' => '', 'description' => '']];
    }
@endphp
<div class="md:col-span-2 border border-gray-200 rounded-lg p-4 bg-gray-50/50">
    <h3 class="text-sm font-semibold text-gray-900 mb-1">Clientes de la empresa</h3>
    <p class="text-xs text-gray-600 mb-3">
        Opcional. Elija uno o más usuarios con rol <strong>Cliente</strong> y, si desea, una nota (ej. contabilidad, gestor del expediente).
        Puede guardar la empresa sin asignar clientes.
    </p>
    @if($clientUsers->isEmpty())
        <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            No hay usuarios con rol Cliente en el sistema. Cree clientes desde <strong>Directorio → Clientes</strong> o continúe sin asignar.
        </p>
    @endif
    <div id="client-assignments-rows" class="space-y-3">
        @foreach($assignments as $idx => $row)
            @php
                $uid = is_array($row) ? ($row['user_id'] ?? '') : '';
                $desc = is_array($row) ? ($row['description'] ?? '') : '';
            @endphp
            <div class="client-assignment-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end border border-gray-100 rounded-lg p-3 bg-white">
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="client_assignments[{{ $idx }}][user_id]"
                            class="client-assignment-select w-full border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500 p-2.5 bg-white">
                        <option value="">— Seleccionar —</option>
                        @foreach($clientUsers as $cu)
                            <option value="{{ $cu->id }}" {{ (string) $uid === (string) $cu->id ? 'selected' : '' }}>
                                {{ $cu->name }} · {{ $cu->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nota / rol (opcional)</label>
                    <input type="text"
                           name="client_assignments[{{ $idx }}][description]"
                           value="{{ $desc }}"
                           maxlength="500"
                           placeholder="Ej. Contacto contabilidad, gestor expediente…"
                           class="w-full border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500 p-2.5">
                </div>
                <div class="md:col-span-1 flex justify-end">
                    <button type="button" class="remove-client-row text-red-600 hover:bg-red-50 p-2 rounded-lg" title="Quitar fila" @if($loop->first && count($assignments) === 1) style="visibility:hidden" @endif>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" id="add-client-assignment-row" class="mt-3 text-sm text-teal-700 hover:text-teal-900 font-medium">
        <i class="fas fa-plus mr-1"></i> Añadir otro cliente
    </button>
</div>

<template id="client-assignment-row-template">
    <div class="client-assignment-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end border border-gray-100 rounded-lg p-3 bg-white">
        <div class="md:col-span-5">
            <label class="block text-xs font-medium text-gray-700 mb-1">Cliente</label>
            <select name="client_assignments[__INDEX__][user_id]"
                    class="client-assignment-select w-full border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500 p-2.5 bg-white">
                <option value="">— Seleccionar —</option>
                @foreach($clientUsers as $cu)
                    <option value="{{ $cu->id }}">{{ $cu->name }} · {{ $cu->email }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-6">
            <label class="block text-xs font-medium text-gray-700 mb-1">Nota / rol (opcional)</label>
            <input type="text"
                   name="client_assignments[__INDEX__][description]"
                   maxlength="500"
                   placeholder="Ej. Contacto contabilidad…"
                   class="w-full border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500 p-2.5">
        </div>
        <div class="md:col-span-1 flex justify-end">
            <button type="button" class="remove-client-row text-red-600 hover:bg-red-50 p-2 rounded-lg" title="Quitar fila">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
(function() {
    var container = document.getElementById('client-assignments-rows');
    var template = document.getElementById('client-assignment-row-template');
    var addBtn = document.getElementById('add-client-assignment-row');
    if (!container || !template || !addBtn) return;

    function nextIndex() {
        var rows = container.querySelectorAll('.client-assignment-row');
        return rows.length;
    }

    function wireRemove(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('.client-assignment-row');
            if (!row || container.querySelectorAll('.client-assignment-row').length <= 1) return;
            row.remove();
            renumber();
        });
    }

    function renumber() {
        var rows = container.querySelectorAll('.client-assignment-row');
        rows.forEach(function(row, i) {
            row.querySelectorAll('[name^="client_assignments"]').forEach(function(el) {
                el.name = el.name.replace(/client_assignments\[\d+\]/, 'client_assignments[' + i + ']');
            });
        });
    }

    container.querySelectorAll('.remove-client-row').forEach(wireRemove);

    addBtn.addEventListener('click', function() {
        var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var row = wrap.firstElementChild;
        container.appendChild(row);
        wireRemove(row.querySelector('.remove-client-row'));
    });
})();
</script>
@endpush
