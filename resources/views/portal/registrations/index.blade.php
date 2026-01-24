@extends('layouts.portal')

@section('title', 'Mis Expedientes')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Mis Expedientes</h2>
        <form method="GET" action="{{ route('portal.registrations.index') }}" class="flex gap-2">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar producto o registro..."
                       class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('portal.registrations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($registrations->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p>No tienes expedientes asignados.</p>
                @if(request('search'))
                    <a href="{{ route('portal.registrations.index') }}" class="text-teal-600 hover:text-teal-700 mt-2 inline-block text-sm">Ver todos</a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500">
                        <tr>
                            <th class="p-4">Producto</th>
                            <th class="p-4">No. Registro</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Vencimiento</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($registrations as $r)
                        <tr class="hover:bg-gray-50 {{ $r->status === 'tramite' ? 'bg-yellow-50/30' : '' }}">
                            <td class="p-4 font-bold text-gray-800">{{ $r->product_name }}</td>
                            <td class="p-4 font-mono text-gray-600 text-xs">{{ $r->registration_number ?? '-' }}</td>
                            <td class="p-4">
                                @php
                                    $badge = match($r->status) {
                                        'vigente' => 'bg-green-100 text-green-700 border-green-200',
                                        'tramite' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'requerimiento' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'vencido' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $badge }}">{{ $r->status }}</span>
                            </td>
                            <td class="p-4 text-gray-600">{{ $r->expiration_date?->format('d M, Y') ?? '-' }}</td>
                            <td class="p-4 text-center">
                                <a href="{{ route('portal.registrations.show', $r) }}"
                                   class="inline-flex items-center gap-1.5 text-teal-600 hover:text-teal-800 font-medium text-xs border border-teal-600 px-3 py-1.5 rounded hover:bg-teal-50 transition-colors">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($registrations->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $registrations->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
