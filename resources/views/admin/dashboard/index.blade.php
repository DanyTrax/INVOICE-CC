@extends('layouts.admin')

@section('title', 'Dashboard - RAMS')

@section('page-title', 'Resumen General')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['active_registrations']) }}</h3>
                    <p>EXPEDIENTES ACTIVOS</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="{{ route('admin.registrations.index') }}" class="small-box-footer">
                    Total de registros activos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['expiring_this_month']) }}</h3>
                    <p>VENCEN ESTE MES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.registrations.index', ['filter' => 'expiring']) }}" class="small-box-footer">
                    Requieren atención <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['in_process_invima']) }}</h3>
                    <p>EN TRÁMITE INVIMA</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <a href="{{ route('admin.registrations.index', ['status' => 'en_tramite']) }}" class="small-box-footer">
                    Pendientes de respuesta <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_companies']) }}</h3>
                    <p>CLIENTES TOTALES</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="small-box-footer">
                    Empresas registradas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Calendario de Vencimientos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Calendario de Vencimientos
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="prev-month">
                                <i class="fas fa-chevron-left"></i> Mes Anterior
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="next-month">
                                Mes Siguiente <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge badge-danger mr-2">
                            <i class="fas fa-circle"></i> Vencimientos
                        </span>
                        <span class="badge badge-primary">
                            <i class="fas fa-circle"></i> Límites de Respuesta
                        </span>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-day-sat, .fc-day-sun {
        background-color: #fef2f2;
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
                cancelButtonText: 'Cerrar'
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
