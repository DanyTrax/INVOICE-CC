@extends('layouts.admin-flowbite')

@section('title', 'Asociados')

@section('page-title', 'Asociados')

@section('content')
    @include('admin.partials.flash')

    <div class="flex flex-wrap gap-3 justify-between items-center mb-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" class="border border-gray-300 rounded-lg p-2 text-sm" placeholder="Nombre, documento, correo">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Categoría</label>
                <select name="category" class="border border-gray-300 rounded-lg p-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Filtrar</button>
        </form>
        @if(app(\App\Services\PermissionService::class)->userHasPermission('associates', 'edit'))
            <a href="{{ route('admin.associates.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Nuevo asociado</a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left">ID / NIT</th>
                    <th class="px-4 py-3 text-left">Categoría</th>
                    <th class="px-4 py-3 text-left">Correo</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($associates as $associate)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">{{ $associate->full_name }}</td>
                        <td class="px-4 py-3">{{ $associate->document_id }}</td>
                        <td class="px-4 py-3">{{ $associate->category }}</td>
                        <td class="px-4 py-3">{{ $associate->email ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $associate->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.associates.edit', $associate) }}" class="text-teal-700">Editar</a>
                            <form action="{{ route('admin.associates.destroy', $associate) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este asociado?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay asociados registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $associates->links() }}</div>
@endsection
