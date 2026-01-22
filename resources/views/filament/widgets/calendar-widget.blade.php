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
            
            <div id="calendar" class="bg-white rounded-lg border border-gray-200 p-4"></div>
        </div>
    </x-filament::section>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var events = @json($this->getEvents());
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    firstDay: 1, // Lunes como primer día
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día'
                    },
                    events: events,
                    eventDisplay: 'block',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false
                    },
                    height: 'auto',
                    dayMaxEvents: 3,
                    moreLinkText: 'más',
                    eventClick: function(info) {
                        // Opcional: mostrar detalles del evento
                        console.log('Evento:', info.event.title);
                    },
                    dayCellClassNames: function(date) {
                        // Resaltar fines de semana
                        var day = date.getDay();
                        if (day === 0 || day === 6) {
                            return ['weekend-day'];
                        }
                        return [];
                    }
                });
                
                calendar.render();
                
                // Recargar cuando Livewire actualice
                Livewire.hook('morph.updated', () => {
                    calendar.refetchEvents();
                });
            });
        </script>
        <style>
            .fc {
                font-family: inherit;
            }
            .fc-header-toolbar {
                margin-bottom: 1rem;
            }
            .fc-button {
                background-color: #0f766e;
                border-color: #0f766e;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                font-weight: 500;
            }
            .fc-button:hover {
                background-color: #0d9488;
                border-color: #0d9488;
            }
            .fc-button-active {
                background-color: #14b8a6;
                border-color: #14b8a6;
            }
            .fc-today-button {
                background-color: #64748b;
                border-color: #64748b;
            }
            .fc-daygrid-day-top {
                flex-direction: row;
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
            }
            .fc-event:hover {
                opacity: 0.9;
            }
            .fc-daygrid-event {
                margin: 0.125rem 0;
            }
            .weekend-day {
                background-color: #fef2f2;
            }
            .fc-daygrid-day-frame {
                min-height: 100px;
            }
        </style>
    @endpush
</x-filament-widgets::widget>
