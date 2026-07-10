@php($concept = $concept ?? null)
@php($existingPrices = $concept?->prices?->keyBy('category') ?? collect())

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-4xl">
    <form action="{{ $action }}" method="POST" class="space-y-4">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div>
            <label class="block text-sm font-medium mb-1">Nombre del cobro *</label>
            <input type="text" name="name" value="{{ old('name', $concept?->name) }}" required class="w-full border border-gray-300 rounded-lg p-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5">{{ old('description', $concept?->description) }}</textarea>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Valor por categoría *</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($categories as $index => $cat)
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="prices[{{ $index }}][category]" value="{{ $cat }}">
                        <span class="w-28 text-sm text-gray-700">{{ $cat }}</span>
                        <input type="number" step="0.01" min="0" name="prices[{{ $index }}][amount]"
                               value="{{ old('prices.'.$index.'.amount', $existingPrices->get($cat)?->amount) }}"
                               class="flex-1 border border-gray-300 rounded-lg p-2.5" required>
                    </div>
                @endforeach
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $concept?->is_active ?? true))>
            Activo
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg">Guardar</button>
            <a href="{{ route('admin.concepts.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Cancelar</a>
        </div>
    </form>
</div>
