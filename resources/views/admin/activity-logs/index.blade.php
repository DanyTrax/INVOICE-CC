@extends('layouts.admin-flowbite')

@section('title', 'Registros de Actividad - RAMS')

@section('page-title', 'Registros de Actividad')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Registros de Actividad</span>
        </div>
    </li>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
            <div class="min-w-[180px]">
                <label for="user_id" class="block text-xs font-medium text-gray-700 mb-1">Usuario</label>
                <select name="user_id" id="user_id" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label for="action" class="block text-xs font-medium text-gray-700 mb-1">Acción</label>
                <select name="action" id="action" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($actionLabels as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-sm">
            </div>
            <div class="min-w-[140px]">
                <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
            @if(request('user_id') || request('action') || request('date_from') || request('date_to'))
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    <i class="fas fa-times mr-2"></i> Limpiar
                </a>
            @endif
        </form>
        @if($canDeleteAll)
            <form method="POST" action="{{ route('admin.activity-logs.destroy-all') }}" class="shrink-0"
                  onsubmit='return confirm(@json($hasActivityFilters
                      ? "Se eliminarán solo los registros que coinciden con el listado filtrado actual (mismas filas que ves en la tabla). ¿Continuar?"
                      : "Se eliminarán todos los registros visibles según tu jerarquía (sin filtros en pantalla). ¿Continuar?"
                  ));'>
                @csrf
                @method('DELETE')
                @if(request('user_id'))
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @endif
                @if(request('action'))
                    <input type="hidden" name="action" value="{{ request('action') }}">
                @endif
                @if(request('date_from'))
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                @endif
                @if(request('date_to'))
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                @endif
                <button type="submit" class="w-full lg:w-auto px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm" title="Borra exactamente lo que muestra la tabla con los filtros actuales">
                    <i class="fas fa-trash-alt mr-2"></i>
                    @if(!empty($hasActivityFilters))
                        Eliminar listado filtrado
                    @else
                        Eliminar todo (visible)
                    @endif
                </button>
            </form>
        @endif
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Fecha / Hora</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Acción</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">IP</th>
                        <th class="px-4 py-3">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($log->user)
                                    <a href="{{ route('admin.activity-logs.show', $log->user) }}" class="text-teal-600 hover:text-teal-700 font-medium">{{ $log->user->name }}</a>
                                    <span class="text-gray-500 block text-xs">{{ $log->user->email }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($log->action === 'login') bg-green-100 text-green-800
                                    @elseif($log->action === 'logout') bg-gray-100 text-gray-800
                                    @elseif($log->action === 'created') bg-blue-100 text-blue-800
                                    @elseif($log->action === 'updated') bg-amber-100 text-amber-800
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                    @elseif($log->action === 'sent_email') bg-teal-100 text-teal-800
                                    @elseif($log->action === 'mutation') bg-indigo-100 text-indigo-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($log->user)
                                    <a href="{{ route('admin.activity-logs.show', $log->user) }}" class="text-teal-600 hover:text-teal-700" title="Línea de tiempo del usuario">
                                        <i class="fas fa-stream"></i>
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                                No hay registros de actividad con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
