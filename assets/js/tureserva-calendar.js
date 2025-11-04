document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('tureserva-calendar');
    if (!calendarEl) return;

    const yearInput = document.getElementById('tureserva_year');
    const alojInput = document.getElementById('tureserva_alojamiento');
    const estadoInput = document.getElementById('tureserva_estado');
    const filtrarBtn = document.getElementById('tureserva-filtrar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        locale: 'es',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                action: 'tureserva_get_calendar',
                security: tureservaCalendar.nonce,
                year: yearInput.value,
                alojamiento: alojInput.value,
                estado: estadoInput.value
            });

            fetch(`${tureservaCalendar.ajax_url}?${params}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        console.error('Error en respuesta:', response);
                        failureCallback();
                    }
                })
                .catch(error => {
                    console.error('Error de red:', error);
                    failureCallback();
                });
        },
        eventDidMount: function (info) {
            const e = info.event.extendedProps;
            tippy(info.el, {
                content: `
                    <strong>${e.cliente || 'Sin cliente'}</strong><br>
                    🏨 ${e.alojamiento || 'Sin alojamiento'}<br>
                    <em>${e.estado || ''}</em>
                    ${e.motivo ? '<br><small>' + e.motivo + '</small>' : ''}
                `,
                allowHTML: true,
                placement: 'top',
                theme: 'light-border'
            });
        },
        eventClick: function (info) {
            const link = info.event.extendedProps.link;
            if (link) window.open(link, '_blank');
        }
    });

    calendar.render();

    filtrarBtn.addEventListener('click', () => calendar.refetchEvents());
});
