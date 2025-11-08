document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('tureserva-calendar-timeline');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineMonth',
        locale: 'es',
        height: 'auto',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth'
        },
        resources: function (fetchInfo, successCallback, failureCallback) {
            fetch(`${tureservaCalendar.ajax_url}?action=tureserva_get_resources&security=${tureservaCalendar.nonce}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) successCallback(response.data);
                    else failureCallback();
                })
                .catch(err => failureCallback(err));
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                action: 'tureserva_get_calendar',
                security: tureservaCalendar.nonce,
                year: new Date().getFullYear()
            });
            fetch(`${tureservaCalendar.ajax_url}?${params}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) successCallback(response.data);
                    else failureCallback();
                })
                .catch(err => failureCallback(err));
        },
        eventDidMount: function (info) {
            const e = info.event.extendedProps;
            tippy(info.el, {
                content: `
                    <strong>${e.cliente || 'Sin cliente'}</strong><br>
                    🏨 ${e.alojamiento}<br>
                    <em>${e.estado || ''}</em>
                `,
                allowHTML: true,
                theme: 'light-border'
            });
        },
        eventClick: function (info) {
            const link = info.event.extendedProps.link;
            if (link) window.open(link, '_blank');
        }
    });

    calendar.render();
});
