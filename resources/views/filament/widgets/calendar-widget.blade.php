@php
    $widgetId = $this->getId();
    $calendarId = 'calendar-' . $widgetId;
    $events = $this->getEvents();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Calendario de Vencimientos
        </x-slot>
        
        <div class="space-y-4">
            <div class="flex gap-4 mb-4">
                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-3 h-3 bg-red-500 rounded"></span>
                    Vencimientos
                </span>
                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-3 h-3 bg-blue-500 rounded"></span>
                    Límites de Respuesta
                </span>
            </div>
            
            <div 
                id="{{ $calendarId }}" 
                class="bg-white rounded-lg border border-gray-200 p-4 min-h-[500px]"
                wire:ignore
                data-events='@json($events)'
            >
                <div class="flex items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div>
                        <p>Cargando calendario...</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@if(!isset($GLOBALS['fullcalendar_loaded']))
    @php $GLOBALS['fullcalendar_loaded'] = true; @endphp
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js" defer></script>
@endif

<script>
(function() {
    var calendarId = '{{ $calendarId }}';
    var events = @json($events);
    
    function loadFullCalendar() {
        if (typeof FullCalendar !== 'undefined') {
            initCalendar();
            return;
        }
        
        // Si FullCalendar no está cargado, intentar cargarlo
        if (!document.querySelector('script[src*="fullcalendar"]')) {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js';
            script.onload = function() {
                setTimeout(initCalendar, 100);
            };
            document.head.appendChild(script);
            
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css';
            document.head.appendChild(link);
        } else {
            setTimeout(loadFullCalendar, 200);
        }
    }
    
    function initCalendar() {
        var calendarEl = document.getElementById(calendarId);
        if (!calendarEl) {
            setTimeout(initCalendar, 100);
            return;
        }
        
        if (calendarEl.dataset.initialized === 'true') return;
        
        if (typeof FullCalendar === 'undefined') {
            setTimeout(loadFullCalendar, 200);
            return;
        }
        
        calendarEl.dataset.initialized = 'true';
        calendarEl.innerHTML = ''; // Limpiar loading
        
        try {
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
                eventDisplay: 'block',
                height: 'auto',
                dayMaxEvents: 3,
                moreLinkText: 'más',
                eventClick: function(info) {
                    console.log('Evento:', info.event.title);
                },
                dayCellClassNames: function(date) {
                    var day = date.getDay();
                    return (day === 0 || day === 6) ? ['weekend-day'] : [];
                }
            });
            
            calendar.render();
            console.log('✅ Calendario inicializado:', calendarId);
        } catch (error) {
            console.error('❌ Error al inicializar calendario:', error);
            calendarEl.dataset.initialized = 'false';
            calendarEl.innerHTML = '<div class="p-4 text-red-600">Error al cargar el calendario. Por favor, recarga la página.</div>';
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadFullCalendar, 300);
        });
    } else {
        setTimeout(loadFullCalendar, 300);
    }
    
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', function() {
            var calendarEl = document.getElementById(calendarId);
            if (calendarEl) {
                calendarEl.dataset.initialized = 'false';
                setTimeout(loadFullCalendar, 300);
            }
        });
    }
})();
</script>

<style>
.fc { font-family: inherit; }
.fc-header-toolbar { margin-bottom: 1rem; }
.fc-button {
    background-color: #0f766e !important;
    border-color: #0f766e !important;
    color: white !important;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
}
.fc-button:hover {
    background-color: #0d9488 !important;
    border-color: #0d9488 !important;
}
.fc-button-active {
    background-color: #14b8a6 !important;
    border-color: #14b8a6 !important;
}
.fc-today-button {
    background-color: #64748b !important;
    border-color: #64748b !important;
}
.fc-daygrid-day-number {
    padding: 0.25rem;
    font-weight: 600;
}
.fc-day-sat .fc-daygrid-day-number,
.fc-day-sun .fc-daygrid-day-number {
    color: #dc2626;
}
.fc-event {
    border-radius: 0.25rem;
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
    cursor: pointer;
    border: none !important;
}
.fc-event:hover { opacity: 0.9; }
.fc-daygrid-event { margin: 0.125rem 0; }
.weekend-day { background-color: #fef2f2; }
.fc-daygrid-day-frame { min-height: 100px; }
</style>
