@extends('layouts.admin-flowbite')

@section('title', 'Expedientes INVIMA - RAMS')

@section('page-title', 'Expedientes')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Expedientes</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="{{ route('admin.processes.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i> Nuevo Proceso
        </a>
        <div class="flex-1 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.processes.index') }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cliente o expediente INVIMA..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                </div>
                <select name="status" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                    <option value="">Todos los estados</option>
                    <option value="Recolección" {{ request('status') === 'Recolección' ? 'selected' : '' }}>Recolección</option>
                    <option value="Radicado" {{ request('status') === 'Radicado' ? 'selected' : '' }}>Radicado</option>
                    <option value="En Requerimiento" {{ request('status') === 'En Requerimiento' ? 'selected' : '' }}>En Requerimiento</option>
                    <option value="Finalizado" {{ request('status') === 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
                <select name="client_id" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                    <option value="">Todos los clientes</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
                @if(request('search') || request('status') || request('client_id'))
                    <a href="{{ route('admin.processes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo servicio</th>
                        <th class="px-4 py-3">Expediente INVIMA</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 w-28">Facturable</th>
                        <th class="px-4 py-3">Actualizado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processes as $process)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $process->client->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $process->expediente_invima ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'Recolección' => 'bg-gray-100 text-gray-800',
                                        'Radicado' => 'bg-blue-100 text-blue-800',
                                        'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                        'Finalizado' => 'bg-green-100 text-green-800',
                                    ];
                                    $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($process->quote_item_id === null)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800" title="Sin cotización asociada – facturable directo">Facturable</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $process->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-700 font-medium">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay expedientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($processes->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $processes->links() }}
            </div>
        @endif
    </div>
@endsection
