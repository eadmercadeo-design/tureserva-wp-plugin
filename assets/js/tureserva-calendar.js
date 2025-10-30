document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('tureserva-calendar');
    if (!calendarEl) return;

    const yearInput = document.getElementById('tureserva_year');
    const alojInput = document.getElementById('tureserva_alojamiento');
    const estadoInput = document.getElementById('tureserva_estado');
    const filtrarBtn = document.getElementById('tureserva-filtrar');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        locale: 'es',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridYear'
        },
        eventDisplay: 'block',
        eventSources: [
            {
                url: tureservaCalendar.ajax_url,
                method: 'GET',
                extraParams: function () {
                    return {
                        action: 'tureserva_get_calendar',
                        year: yearInput.value,
                        alojamiento: alojInput.value,
                        estado: estadoInput.value
                    };
                },
                failure: function () {
                    alert('Error al cargar los eventos.');
                }
            }
        ],
        eventClick: function (info) {
            let e = info.event.extendedProps;
            let msg = e.tipo === 'bloqueo'
                ? `🛑 ${e.alojamiento}\nMotivo: ${e.motivo}`
                : `🏨 ${e.alojamiento}\nCliente: ${e.cliente}\nEstado: ${e.estado}`;
            alert(msg);
        }
    });

    calendar.render();

    filtrarBtn.addEventListener('click', function () {
        calendar.refetchEvents();
    });
});
