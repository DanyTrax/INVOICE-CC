@extends('layouts.admin-flowbite')

@section('title', 'Historial de Expedientes - RAMS')

@section('page-title', 'Historial de Expedientes')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.processes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Expedientes</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Historial de Expedientes</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Filtros</h2>
        <form method="GET" action="{{ route('admin.processes.history') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label for="history-search" class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" id="history-search" name="search" value="{{ request('search') }}"
                       placeholder="Producto, radicado, cotización..."
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label for="history-client" class="block text-xs font-medium text-gray-600 mb-1">Cliente</label>
                <select id="history-client" name="client_id" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Todos</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="history-date-from" class="block text-xs font-medium text-gray-600 mb-1">Desde (última actualización)</label>
                <input type="date" id="history-date-from" name="date_from" value="{{ request('date_from') }}"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label for="history-date-to" class="block text-xs font-medium text-gray-600 mb-1">Hasta (última actualización)</label>
                <input type="date" id="history-date-to" name="date_to" value="{{ request('date_to') }}"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="md:col-span-2 lg:col-span-4 flex justify-end gap-2">
                <a href="{{ route('admin.processes.history') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    Limpiar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                    <i class="fas fa-filter mr-2"></i> Aplicar filtros
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Expedientes Finalizados</h2>
            <p class="text-xs text-gray-500">
                Solo se muestran expedientes en estado <strong>Finalizado</strong>. Si edita un expediente y cambia su estado,
                volverá a aparecer en el módulo de <strong>Expedientes</strong>.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">#</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo de Trámite</th>
                        <th class="px-4 py-3">Producto / Referencia</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha Último Evento</th>
                        <th class="px-4 py-3">Equipo</th>
                        <th class="px-4 py-3 whitespace-nowrap min-w-[9rem]">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.processes.partials.process-rows', ['processes' => $processes])
                </tbody>
            </table>
        </div>
        @if($processes->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $processes->withQueryString()->links() }}
            </div>
        @endif
    </div>

    @include('admin.processes.partials.process-assignment-modal')
@endsection

@push('scripts')
    @include('admin.processes.partials.process-assignment-modal-js')
@endpush

