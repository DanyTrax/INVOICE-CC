@php($invoice = $invoice ?? null)

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-3xl">
    <form action="{{ $action }}" method="POST" class="space-y-4">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Asociado *</label>
                <select name="associate_id" required class="w-full border border-gray-300 rounded-lg p-2.5">
                    <option value="">Seleccionar...</option>
                    @foreach($associates as $associate)
                        <option value="{{ $associate->id }}" @selected(old('associate_id', $invoice?->associate_id) == $associate->id)>
                            {{ $associate->full_name }} ({{ $associate->category }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Concepto *</label>
                <select name="concept_id" required class="w-full border border-gray-300 rounded-lg p-2.5">
                    <option value="">Seleccionar...</option>
                    @foreach($concepts as $concept)
                        <option value="{{ $concept->id }}" @selected(old('concept_id', $invoice?->concept_id) == $concept->id)>
                            {{ $concept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Fecha elaboración *</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', optional($invoice?->issue_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Fecha vencimiento *</label>
                <input type="date" name="due_date" value="{{ old('due_date', optional($invoice?->due_date)->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            @if(isset($statuses))
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Estado *</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg p-2.5">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $invoice?->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <p class="text-xs text-gray-500">El valor total se calcula automáticamente según la categoría del asociado y el concepto seleccionado.</p>

        <div class="flex gap-3">
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg">Guardar</button>
            <a href="{{ $invoice ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Cancelar</a>
        </div>
    </form>
</div>
