@props(['events' => [], 'showNavigation' => true])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-calendar-alt mr-2 text-teal-600"></i>
            Calendario de Vencimientos
        </h3>
        @if($showNavigation)
            <div class="flex items-center space-x-2">
                <button id="prev-month" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-chevron-left mr-1"></i> Mes Anterior
                </button>
                <button id="next-month" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Mes Siguiente <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        @endif
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
    
    <div id="calendar-{{ uniqid() }}" class="w-full"></div>
</div>

@push('styles')
<style>
    #calendar-{{ uniqid() }} {
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
    var calendarId = 'calendar-{{ uniqid() }}';
    var calendarEl = document.getElementById(calendarId);
    if (!calendarEl) return;
    
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
            month: 'Mes'
        },
        events: events,
        eventClick: function(info) {
            var type = info.event.extendedProps.type === 'expiration' ? 'Vencimiento' : 'Límite de Respuesta';
            var message = '<strong>' + type + '</strong><br>' +
                         'Producto: ' + info.event.title + '<br>' +
                         'Cliente: ' + (info.event.extendedProps.company || 'N/A') + '<br>' +
                         'Fecha: ' + info.event.start.toLocaleDateString('es-ES');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Detalles del Evento',
                    html: message,
                    icon: 'info',
                    confirmButtonText: 'Ver Expediente',
                    showCancelButton: true,
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#0f766e'
                }).then((result) => {
                    if (result.isConfirmed && info.event.extendedProps.registration_id) {
                        window.location.href = '/admin/registrations/' + info.event.extendedProps.registration_id + '/edit';
                    }
                });
            }
        },
        dayCellClassNames: function(date) {
            var day = date.getDay();
            return (day === 0 || day === 6) ? ['weekend-day'] : [];
        }
    });
    
    calendar.render();
    
    // Navegación de meses
    var prevBtn = document.getElementById('prev-month');
    var nextBtn = document.getElementById('next-month');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            calendar.prev();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            calendar.next();
        });
    }
});
</script>
@endpush
