<div>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <!-- cargar locales para idioma -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>

    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                firstDay: 1,
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    today: 'Hoy',
                    day: 'Dia',
                    week: 'Semana',
                    month: 'Mes'
                },
                events: '/jd/compensaciones/events',
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                nowIndicator: true,
                height: 'auto',
                eventClick: function(info) {
                    // por ahora mostrar detalles simples
                    const ev = info.event;
                    alert(ev.title + '\nMin solicitados: ' + (ev.extendedProps.minutos_solicitados || 'N/A'));
                }
            });

            calendar.render();
        });
    </script>
</div>
