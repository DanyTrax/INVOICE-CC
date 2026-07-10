@extends('layouts.admin-flowbite')

@section('title', 'Conceptos de cobro')
@section('page-title', 'Conceptos de cobro')

@section('content')
    @include('admin.partials.flash')

    <div class="flex justify-between items-center mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="border border-gray-300 rounded-lg p-2 text-sm" placeholder="Buscar concepto">
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Filtrar</button>
        </form>
        <a href="{{ route('admin.concepts.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Nuevo concepto</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left">Valores por categoría</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($concepts as $concept)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3 font-medium">{{ $concept->name }}</td>
                        <td class="px-4 py-3">
                            @foreach($concept->prices as $price)
                                <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1">{{ $price->category }}: ${{ number_format($price->amount, 0, ',', '.') }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">{{ $concept->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.concepts.edit', $concept) }}" class="text-teal-700">Editar</a>
                            <form action="{{ route('admin.concepts.destroy', $concept) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este concepto?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay conceptos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $concepts->links() }}</div>
@endsection
