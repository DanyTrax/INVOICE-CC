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
                id="calendar-{{ $this->getId() }}" 
                class="bg-white rounded-lg border border-gray-200 p-4"
                data-events='@json($this->getEvents())'
            ></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@once
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
(function() {
    function initCalendars() {
        document.querySelectorAll('[id^="calendar-"]').forEach(function(calendarEl) {
            if (calendarEl.dataset.initialized === 'true') return;
            
            calendarEl.dataset.initialized = 'true';
            
            var eventsData = calendarEl.getAttribute('data-events');
            var events = eventsData ? JSON.parse(eventsData) : [];
            
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar no está cargado');
                return;
            }
            
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
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendars);
    } else {
        initCalendars();
    }
    
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', function() {
            setTimeout(initCalendars, 100);
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
@endonce
