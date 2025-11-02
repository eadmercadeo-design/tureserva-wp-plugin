/**
 * ==========================================================
 * JS — Sincronización de Calendarios
 * ==========================================================
 * Simulación visual del proceso de sincronización iCal.
 * (Preparado para conexión real a Airbnb/Booking/Google)
 * ==========================================================
 */
jQuery(document).ready(function ($) {
    const $button = $('#tureserva-sync-calendar');
    const $bar = $('#tureserva-calendar-progress-bar');
    const $status = $('#tureserva-calendar-status');

    $button.on('click', function (e) {
        e.preventDefault();
        $button.prop('disabled', true).text('Sincronizando...');
        $bar.css('width', '0%');
        $status.text('Iniciando sincronización...');

        let progress = 0;
        const interval = setInterval(() => {
            progress += 5;
            $bar.css('width', progress + '%');
            if (progress >= 100) {
                clearInterval(interval);
                $button.prop('disabled', false).text('🔄 Sincronizar ahora');
                $status.text('✅ Sincronización completada correctamente.');
            }
        }, 100);
    });
});
