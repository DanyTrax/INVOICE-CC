@php
    $calendarId = 'calendar-' . uniqid();
    $prevBtnId = 'prev-month-' . uniqid();
    $nextBtnId = 'next-month-' . uniqid();
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-calendar-alt mr-2 text-teal-600"></i>
            Calendario de Vencimientos
        </h3>
        @if($showNavigation)
            <div class="flex items-center space-x-2">
                <button id="{{ $prevBtnId }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-chevron-left mr-1"></i> Mes Anterior
                </button>
                <button id="{{ $nextBtnId }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
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
    
    <div id="{{ $calendarId }}" class="w-full" style="min-height: 400px;"></div>
</div>

@push('styles')
<style>
    #{{ $calendarId }} {
        max-width: 100%;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 0.375rem;
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
    .fc-day-sat, .fc-day-sun {
        background-color: #fef2f2;
    }
    .fc-button {
        background-color: #0f766e !important;
        border-color: #0f766e !important;
        color: white !important;
    }
    .fc-button:hover {
        background-color: #0d9488 !important;
        border-color: #0d9488 !important;
    }
    .fc-today-button {
        background-color: #64748b !important;
        border-color: #64748b !important;
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    var calendarId = '{{ $calendarId }}';
    var prevBtnId = '{{ $prevBtnId }}';
    var nextBtnId = '{{ $nextBtnId }}';
    var events = @json($events);
    var calendar = null;
    var initAttempts = 0;
    var maxAttempts = 50; // 5 segundos máximo
    
    function initCalendar() {
        initAttempts++;
        
        // Verificar que FullCalendar esté cargado
        if (typeof FullCalendar === 'undefined') {
            if (initAttempts < maxAttempts) {
                setTimeout(initCalendar, 100);
            } else {
                console.error('❌ FullCalendar no se cargó después de', maxAttempts, 'intentos');
                var calendarEl = document.getElementById(calendarId);
                if (calendarEl) {
                    calendarEl.innerHTML = '<div class="p-4 text-red-600 text-center">Error: FullCalendar no está disponible. Por favor, recarga la página.</div>';
                }
            }
            return;
        }
        
        var calendarEl = document.getElementById(calendarId);
        if (!calendarEl) {
            if (initAttempts < maxAttempts) {
                setTimeout(initCalendar, 100);
            }
            return;
        }
        
        // Si ya está inicializado, no hacer nada
        if (calendarEl.dataset.initialized === 'true') {
            return;
        }
        
        calendarEl.dataset.initialized = 'true';
        
        try {
            // Verificar que FullCalendar.Calendar existe
            if (typeof FullCalendar.Calendar === 'undefined') {
                throw new Error('FullCalendar.Calendar no está definido');
            }
            
            calendar = new FullCalendar.Calendar(calendarEl, {
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
                events: events || [],
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
                    } else {
                        alert(message);
                    }
                },
                dayCellClassNames: function(arg) {
                    try {
                        // FullCalendar pasa un objeto con propiedades date, dayEl, etc.
                        // arg.date es un objeto DateInfo, necesitamos acceder a la fecha correctamente
                        var dateObj = null;
                        
                        if (arg.date) {
                            // Si arg.date es un objeto DateInfo, puede tener una propiedad date o ser directamente un Date
                            if (arg.date instanceof Date) {
                                dateObj = arg.date;
                            } else if (arg.date.date instanceof Date) {
                                dateObj = arg.date.date;
                            } else if (typeof arg.date === 'string') {
                                dateObj = new Date(arg.date);
                            } else {
                                // Intentar convertir a Date
                                dateObj = new Date(arg.date);
                            }
                        } else if (arg instanceof Date) {
                            dateObj = arg;
                        } else {
                            return [];
                        }
                        
                        if (!dateObj || isNaN(dateObj.getTime())) {
                            return [];
                        }
                        
                        var day = dateObj.getDay();
                        return (day === 0 || day === 6) ? ['weekend-day'] : [];
                    } catch (e) {
                        console.warn('Error en dayCellClassNames:', e);
                        return [];
                    }
                }
            });
            
            calendar.render();
            console.log('✅ Calendario inicializado:', calendarId, 'Eventos:', events.length);
            
            // Navegación de meses
            var prevBtn = document.getElementById(prevBtnId);
            var nextBtn = document.getElementById(nextBtnId);
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (calendar) calendar.prev();
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (calendar) calendar.next();
                });
            }
        } catch (error) {
            console.error('❌ Error al inicializar calendario:', error);
            var calendarEl = document.getElementById(calendarId);
            if (calendarEl) {
                calendarEl.innerHTML = '<div class="p-4 text-red-600 text-center">Error al cargar el calendario: ' + error.message + '<br>Por favor, recarga la página.</div>';
            }
        }
    }
    
    // Esperar a que FullCalendar esté cargado
    function waitForFullCalendar() {
        // Verificar múltiples formas de acceso a FullCalendar
        var fcAvailable = typeof FullCalendar !== 'undefined' || 
                         (typeof window !== 'undefined' && window.FullCalendar) ||
                         (typeof global !== 'undefined' && global.FullCalendar);
        
        if (fcAvailable && typeof FullCalendar !== 'undefined' && typeof FullCalendar.Calendar !== 'undefined') {
            // Esperar un poco más para asegurar que esté completamente cargado
            setTimeout(function() {
                initCalendar();
            }, 300);
        } else {
            if (initAttempts < maxAttempts) {
                setTimeout(waitForFullCalendar, 100);
            } else {
                console.error('❌ FullCalendar no disponible después de', maxAttempts, 'intentos');
                var calendarEl = document.getElementById(calendarId);
                if (calendarEl) {
                    calendarEl.innerHTML = '<div class="p-4 text-red-600 text-center">Error: FullCalendar no se pudo cargar. Verifica tu conexión a internet y recarga la página.</div>';
                }
            }
        }
    }
    
    // Iniciar espera cuando el DOM y scripts estén listos
    function startInit() {
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            waitForFullCalendar();
        } else {
            window.addEventListener('load', waitForFullCalendar);
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(waitForFullCalendar, 500);
            });
        }
    }
    
    // Esperar a que el script esté en el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInit);
    } else {
        startInit();
    }
})();
</script>
@endpush
