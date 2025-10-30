jQuery(document).ready(function ($) {
    const msg = $('#tureserva-cron-resultado');
    const btnGuardar = $('#tureserva-guardar-cron');
    const btnEjecutar = $('#tureserva-ejecutar-cron');

    // Guardar frecuencia
    btnGuardar.on('click', function () {
        msg.hide();
        btnGuardar.prop('disabled', true).text('Guardando...');
        $.post(ajaxurl, {
            action: 'tureserva_guardar_cron',
            intervalo: $('#tureserva_sync_interval').val()
        }, function (resp) {
            btnGuardar.prop('disabled', false).text('💾 Guardar configuración');
            msg.show().html('<div class="updated"><p>' + resp.data.mensaje + '</p></div>');
        });
    });

    // Sincronización manual
    btnEjecutar.on('click', function () {
        msg.hide();
        btnEjecutar.prop('disabled', true).text('Sincronizando...');
        $.post(ajaxurl, { action: 'tureserva_cron_manual' }, function (resp) {
            btnEjecutar.prop('disabled', false).text('☁️ Ejecutar ahora');
            msg.show().html('<div class="updated"><p>' + resp.data.mensaje + '</p></div>');
        });
    });
});
