document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('tureserva-calendar-timeline');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineMonth',
        locale: 'es',
        height: 'auto',
        firstDay: 1,
        resourceAreaWidth: '20%',
        resourceAreaHeaderContent: 'Alojamientos',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth'
        },
        // 🏨 Cargar Recursos (Alojamientos)
        resources: function (fetchInfo, successCallback, failureCallback) {
            fetch(`${tureservaTimeline.ajax_url}?action=tureserva_get_resources&security=${tureservaTimeline.nonce}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        console.error('Error cargando recursos:', response);
                        failureCallback();
                    }
                })
                .catch(err => {
                    console.error('Error de red (recursos):', err);
                    failureCallback(err);
                });
        },
        // 📅 Cargar Eventos (Reservas)
        events: function (fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                action: 'tureserva_get_calendar',
                security: tureservaTimeline.nonce,
                year: new Date().getFullYear()
            });
            fetch(`${tureservaTimeline.ajax_url}?${params}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        // Mapear eventos para asegurar que tengan resourceId
                        const events = response.data.map(evt => ({
                            ...evt,
                            resourceId: evt.extendedProps.alojamiento_id || null // Asegurar que el backend envíe esto o mapearlo
                        }));
                        successCallback(events);
                    } else {
                        console.error('Error cargando eventos:', response);
                        failureCallback();
                    }
                })
                .catch(err => {
                    console.error('Error de red (eventos):', err);
                    failureCallback(err);
                });
        },
        eventDidMount: function (info) {
            const e = info.event.extendedProps;
            tippy(info.el, {
                content: `
                    <div style="text-align:left;">
                        <strong>${e.cliente || 'Sin cliente'}</strong><br>
                        🏨 ${e.alojamiento || 'Sin alojamiento'}<br>
                        <span class="ts-badge ts-${e.estado}">${e.estado || ''}</span>
                        ${e.motivo ? '<br><small>' + e.motivo + '</small>' : ''}
                    </div>
                `,
                allowHTML: true,
                theme: 'light-border',
                interactive: true
            });
        },
        eventClick: function (info) {
            const link = info.event.extendedProps.link;
            if (link) window.open(link, '_blank');
        }
    });

    calendar.render();
});
