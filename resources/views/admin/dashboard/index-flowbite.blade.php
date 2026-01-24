@extends('layouts.admin-flowbite')

@section('title', 'Dashboard - RAMS')

@section('page-title', 'Resumen General')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
    <!-- Estadísticas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Expedientes Activos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">EXPEDIENTES ACTIVOS</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['active_registrations']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.registrations.index') }}" class="text-sm text-blue-600 hover:text-blue-700 mt-4 inline-flex items-center">
                Total de registros activos <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Vencen Este Mes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">VENCEN ESTE MES</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($stats['expiring_this_month']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.registrations.index', ['filter' => 'expiring']) }}" class="text-sm text-red-600 hover:text-red-700 mt-4 inline-flex items-center">
                Requieren atención <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- En Trámite INVIMA -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">EN TRÁMITE INVIMA</p>
                    <p class="text-3xl font-bold text-teal-600">{{ number_format($stats['in_process_invima']) }}</p>
                </div>
                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-teal-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.registrations.index', ['status' => 'en_tramite']) }}" class="text-sm text-teal-600 hover:text-teal-700 mt-4 inline-flex items-center">
                Pendientes de respuesta <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Empresas Totales -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">EMPRESAS TOTALES</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_companies']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-gray-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.companies.index') }}" class="text-sm text-gray-600 hover:text-gray-700 mt-4 inline-flex items-center">
                Empresas registradas <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <!-- Calendario de Vencimientos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-calendar-alt mr-2 text-teal-600"></i>
                Calendario de Vencimientos
            </h3>
            <div class="flex items-center space-x-2">
                <button id="prev-month" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-chevron-left mr-1"></i> Mes Anterior
                </button>
                <button id="next-month" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Mes Siguiente <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
        
        <div class="mb-4 flex items-center space-x-4">
            <span class="inline-flex items-center text-sm text-gray-600">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                Vencimientos
            </span>
            <span class="inline-flex items-center text-sm text-gray-600">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                Límites de Respuesta
            </span>
        </div>
        
        <div id="calendar" class="w-full"></div>
    </div>
@endsection

@push('styles')
<style>
    #calendar {
        max-width: 100%;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 0.375rem;
        padding: 0.125rem 0.25rem;
    }
    .fc-day-sat, .fc-day-sun {
        background-color: #fef2f2;
    }
    .fc-button {
        background-color: #0f766e !important;
        border-color: #0f766e !important;
    }
    .fc-button:hover {
        background-color: #0d9488 !important;
        border-color: #0d9488 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = @json($events);
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: events,
        eventClick: function(info) {
            var type = info.event.extendedProps.type === 'expiration' ? 'Vencimiento' : 'Límite de Respuesta';
            var message = '<strong>' + type + '</strong><br>' +
                         'Producto: ' + info.event.title + '<br>' +
                         'Cliente: ' + info.event.extendedProps.company + '<br>' +
                         'Fecha: ' + info.event.start.toLocaleDateString('es-ES');
            
            Swal.fire({
                title: 'Detalles del Evento',
                html: message,
                icon: 'info',
                confirmButtonText: 'Ver Expediente',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#0f766e'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/admin/registrations/' + info.event.extendedProps.registration_id + '/edit';
                }
            });
        },
        dayCellClassNames: function(date) {
            var day = date.getDay();
            return (day === 0 || day === 6) ? ['weekend-day'] : [];
        }
    });
    
    calendar.render();
    
    // Navegación de meses
    document.getElementById('prev-month').addEventListener('click', function() {
        calendar.prev();
    });
    
    document.getElementById('next-month').addEventListener('click', function() {
        calendar.next();
    });
});
</script>
@endpush
