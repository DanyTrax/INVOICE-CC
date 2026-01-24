@extends('layouts.portal')

@section('title', 'Resumen')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Hola, {{ auth()->user()->name }} 👋</h2>
            <p class="text-gray-500 text-sm mt-1">Aquí está el estado actual de tus trámites regulatorios.</p>
        </div>
        <a href="{{ route('portal.registrations.index') }}"
           class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm inline-flex items-center gap-2">
            Ver todos mis expedientes <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Registros Vigentes</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $vigentes }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl">
                <i class="fas fa-check"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border-l-4 border-yellow-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-yellow-600">En Trámite / Proceso</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $enTramite }}</h3>
                <p class="text-xs text-gray-400 mt-1">Esperando respuesta INVIMA</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border-l-4 border-amber-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-amber-600">Requerimientos</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $requerimiento }}</h3>
                <p class="text-xs text-gray-400 mt-1">Pendientes de atender</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border-l-4 border-red-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-red-600">Próximos a Vencer</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $proximosVencer }}</h3>
                <p class="text-xs text-gray-400 mt-1">Requiere tu atención</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800">Mis Expedientes Recientes</h3>
            <a href="{{ route('portal.registrations.index') }}" class="text-sm text-teal-600 hover:text-teal-700 font-medium">
                Ver todos
            </a>
        </div>
        @if($registrations->isEmpty())
            <p class="text-gray-500 text-sm py-4">No tienes expedientes asignados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500">
                        <tr>
                            <th class="p-3">Producto</th>
                            <th class="p-3">No. Registro</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3">Vencimiento</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($registrations as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-bold text-gray-800">{{ $r->product_name }}</td>
                            <td class="p-3 font-mono text-gray-600 text-xs">{{ $r->registration_number ?? '-' }}</td>
                            <td class="p-3">
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
                            <td class="p-3 text-gray-600">{{ $r->expiration_date?->format('d M, Y') ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <a href="{{ route('portal.registrations.show', $r) }}"
                                   class="text-teal-600 hover:text-teal-800 font-medium text-xs border border-teal-600 px-3 py-1 rounded hover:bg-teal-50 transition-colors">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($calendarRegistrations->isNotEmpty())
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-gray-800 mb-4">Próximas Fechas Importantes</h3>
        <ul class="space-y-2">
            @foreach($calendarRegistrations->take(15) as $r)
            @php
                $isRequerimiento = $r->status === 'requerimiento';
                $useResponseLimit = $isRequerimiento && $r->response_limit_date;
                $fecha = $useResponseLimit ? $r->response_limit_date : ($r->expiration_date ?? $r->response_limit_date);
            @endphp
            @if($fecha)
            <li class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50">
                @if($isRequerimiento && $r->response_limit_date)
                    <span class="text-xs font-medium text-amber-600 shrink-0">{{ $r->response_limit_date->format('d/m/Y') }}</span>
                    <span class="text-sm text-gray-700">Requerimiento: {{ $r->product_name }}</span>
                @elseif($r->expiration_date)
                    <span class="text-xs font-medium text-red-600 shrink-0">{{ $r->expiration_date->format('d/m/Y') }}</span>
                    <span class="text-sm text-gray-700">Vence: {{ $r->product_name }}</span>
                @else
                    <span class="text-xs font-medium text-blue-600 shrink-0">{{ $r->response_limit_date->format('d/m/Y') }}</span>
                    <span class="text-sm text-gray-700">Límite respuesta: {{ $r->product_name }}</span>
                @endif
                <a href="{{ route('portal.registrations.show', $r) }}" class="ml-auto text-teal-600 hover:text-teal-700 text-sm font-medium shrink-0">Ver</a>
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
