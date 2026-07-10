@php($associate = $associate ?? null)

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-3xl">
    <form action="{{ $action }}" method="POST" class="space-y-4">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Nombre completo *</label>
                <input type="text" name="full_name" value="{{ old('full_name', $associate?->full_name) }}" required class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">ID / NIT *</label>
                <input type="text" name="document_id" value="{{ old('document_id', $associate?->document_id) }}" required class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Categoría *</label>
                <select name="category" required class="w-full border border-gray-300 rounded-lg p-2.5">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $associate?->category) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $associate?->phone) }}" class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Correo</label>
                <input type="email" name="email" value="{{ old('email', $associate?->email) }}" class="w-full border border-gray-300 rounded-lg p-2.5">
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $associate?->is_active ?? true))>
                    Activo
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg">Guardar</button>
            <a href="{{ route('admin.associates.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Cancelar</a>
        </div>
    </form>
</div>
