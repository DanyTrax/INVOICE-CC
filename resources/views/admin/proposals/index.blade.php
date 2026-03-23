@extends('layouts.admin-flowbite')

@section('title', 'Propuestas - RAMS')

@section('page-title', 'Propuestas')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Propuestas</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.proposals.index') }}" class="flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Propuesta No. o cliente..."
                       class="block w-full min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500">
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente" {{ request('status') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Aprobada" {{ request('status') === 'Aprobada' ? 'selected' : '' }}>Aprobada</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700"><i class="fas fa-search mr-2"></i> Buscar</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.proposals.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Limpiar</a>
                @endif
            </form>
        </div>
        <a href="{{ route('admin.proposals.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
            <i class="fas fa-plus mr-2"></i> Nueva propuesta
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Propuesta No.</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3 w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $proposal)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="{{ route('admin.proposals.show', $proposal) }}" class="text-teal-600 hover:underline">{{ $proposal->consecutive }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $proposal->client->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $proposal->date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'Pendiente' => 'bg-amber-100 text-amber-900',
                                        'Aprobada' => 'bg-green-100 text-green-800',
                                    ];
                                    $style = $statusStyles[$proposal->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $proposal->status }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $proposal->currency }} {{ number_format($proposal->total_with_tax, 2) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.proposals.show', $proposal) }}" class="text-teal-600 hover:text-teal-700" title="Ver"><i class="fas fa-eye"></i></a>
                                <form action="{{ route('admin.proposals.destroy', $proposal) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar esta propuesta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay propuestas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($proposals->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $proposals->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
