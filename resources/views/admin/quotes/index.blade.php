@extends('layouts.admin-flowbite')

@section('title', 'Cotizaciones - RAMS')

@section('page-title', 'Cotizaciones')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Cotizaciones</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.quotes.index') }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="COTIZACIÓN No. o cliente..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                </div>
                <select name="status" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                    <option value="">Todos los estados</option>
                    <option value="Borrador" {{ request('status') === 'Borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="Enviada" {{ request('status') === 'Enviada' ? 'selected' : '' }}>Enviada</option>
                    <option value="Aprobada" {{ request('status') === 'Aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="Rechazada" {{ request('status') === 'Rechazada' ? 'selected' : '' }}>Rechazada</option>
                    <option value="Anulada" {{ request('status') === 'Anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>
        @quoteCan('edit')
        <a href="{{ route('admin.quotes.create') }}" class="shrink-0 inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
            <i class="fas fa-plus mr-2"></i> Nueva Cotización
        </a>
        @endquoteCan
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">COTIZACIÓN No.</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3 w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="text-teal-600 hover:underline">{{ $quote->consecutive ?? '-' }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $quote->client->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $quote->date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'Borrador' => 'bg-gray-100 text-gray-800',
                                        'Enviada' => 'bg-blue-100 text-blue-800',
                                        'Aprobada' => 'bg-green-100 text-green-800',
                                        'Rechazada' => 'bg-red-100 text-red-800',
                                        'Anulada' => 'bg-red-100 text-red-800',
                                    ];
                                    $style = $statusStyles[$quote->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $quote->status ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $quote->currency ?? 'COP' }} {{ number_format($quote->total_with_tax, 2) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="inline-flex items-center gap-1 text-teal-600 hover:text-teal-700 font-medium" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.processes.monitor', ['quote_id' => $quote->id]) }}" class="inline-flex items-center gap-1 text-teal-600 hover:text-teal-700 font-medium ml-2" title="Ver expedientes vinculados a esta cotización"><i class="fas fa-folder-open"></i></a>
                                @quoteCan('delete')
                                <form action="{{ route('admin.quotes.destroy', $quote) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar esta cotización? No se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                @endquoteCan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay cotizaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
@endsection
