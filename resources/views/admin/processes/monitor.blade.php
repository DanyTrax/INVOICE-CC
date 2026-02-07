@extends('layouts.admin-flowbite')

@section('title', 'Monitor de Operaciones - RAMS')

@section('page-title', 'Monitor de Operaciones')

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
            <span class="text-sm font-medium text-gray-500">Monitor de Operaciones</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Filtros</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label for="monitor-search" class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" id="monitor-search" name="search" value="{{ request('search') }}"
                       placeholder="Producto, radicado, cotización..."
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label for="monitor-client" class="block text-xs font-medium text-gray-600 mb-1">Cliente</label>
                <select id="monitor-client" name="client_id" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Todos</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="monitor-status" class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select id="monitor-status" name="status" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Process::statuses() as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="monitor-date-from" class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                <input type="date" id="monitor-date-from" name="date_from" value="{{ request('date_from') }}"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label for="monitor-date-to" class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                <input type="date" id="monitor-date-to" name="date_to" value="{{ request('date_to') }}"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden relative">
        <div id="monitor-spinner" class="hidden absolute inset-0 bg-white/70 z-10 flex items-center justify-center">
            <div class="flex flex-col items-center gap-2">
                <svg class="animate-spin h-10 w-10 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-600">Cargando...</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Cotización</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo de Trámite</th>
                        <th class="px-4 py-3">Producto / Referencia</th>
                        <th class="px-4 py-3">Radicado / ID</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha Último Evento</th>
                        <th class="px-4 py-3 w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody id="monitor-tbody">
                    @include('admin.processes.partials.process-rows', ['processes' => $processes])
                </tbody>
            </table>
        </div>
        @if($processes->hasPages())
            <div id="monitor-pagination" class="px-4 py-3 border-t border-gray-200">
                {{ $processes->withQueryString()->links() }}
            </div>
        @else
            <div id="monitor-pagination"></div>
        @endif
    </div>

    @push('scripts')
    <script>
    (function() {
        var monitorUrl = '{{ route('admin.processes.monitor') }}';
        var tbody = document.getElementById('monitor-tbody');
        var spinner = document.getElementById('monitor-spinner');
        var paginationEl = document.getElementById('monitor-pagination');

        function getFilterParams() {
            return {
                search: document.getElementById('monitor-search').value.trim() || undefined,
                client_id: document.getElementById('monitor-client').value || undefined,
                status: document.getElementById('monitor-status').value || undefined,
                date_from: document.getElementById('monitor-date-from').value || undefined,
                date_to: document.getElementById('monitor-date-to').value || undefined,
            };
        }

        function buildQueryString(params) {
            var parts = [];
            for (var k in params) {
                if (params[k] !== undefined && params[k] !== '') {
                    parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
                }
            }
            return parts.length ? '?' + parts.join('&') : '';
        }

        function loadRows() {
            var params = getFilterParams();
            var qs = buildQueryString(params);
            var url = monitorUrl + qs;

            spinner.classList.remove('hidden');
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                tbody.innerHTML = data.rows || '';
                if (paginationEl) paginationEl.innerHTML = data.pagination || '';
                spinner.classList.add('hidden');
            })
            .catch(function() {
                spinner.classList.add('hidden');
                tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-red-500">Error al cargar. Recargue la página.</td></tr>';
            });
        }

        var inputs = ['monitor-search', 'monitor-client', 'monitor-status', 'monitor-date-from', 'monitor-date-to'];
        var timeoutId = null;

        function scheduleLoad() {
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(loadRows, 300);
        }

        inputs.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', scheduleLoad);
                el.addEventListener('input', function() {
                    if (id === 'monitor-search') scheduleLoad();
                });
            }
        });
    })();
    </script>
    @endpush
@endsection
