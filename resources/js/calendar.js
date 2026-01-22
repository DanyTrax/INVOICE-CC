// FullCalendar initialization
window.initFullCalendar = function(calendarId, events) {
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar no está disponible');
        return;
    }
    
    var calendarEl = document.getElementById(calendarId);
    if (!calendarEl) {
        console.error('Elemento calendario no encontrado:', calendarId);
        return;
    }
    
    if (calendarEl.dataset.initialized === 'true') {
        return;
    }
    
    calendarEl.dataset.initialized = 'true';
    
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
    }
};
