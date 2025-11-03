/**
 * ==========================================================
 * JS: Ajustes Globales — TuReserva
 * ==========================================================
 * Maneja el envío AJAX del formulario de ajustes globales.
 * ==========================================================
 */

jQuery(document).ready(function ($) {
    const btn = $('#tureserva-guardar-ajustes');
    const msg = $('#tureserva-resultado-ajax');
    const form = $('#tureserva-form-ajustes');

    btn.on('click', function () {
        // Serializar datos del formulario
        const datos = form.serialize();

        // Estado inicial (deshabilitado y texto de carga)
        btn.prop('disabled', true).text('⏳ Guardando...');
        msg.slideUp(150);

        $.ajax({
            url: tureservaAjustes.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'tureserva_guardar_ajustes',
                security: tureservaAjustes.nonce,
                data: datos
            },
            success: function (resp) {
                btn.prop('disabled', false).text('💾 Guardar ajustes');
                msg.hide().html('');

                if (resp.success) {
                    msg.html(
                        `<div class="updated"><p>${resp.data.mensaje || '✅ Ajustes guardados correctamente.'}</p></div>`
                    ).fadeIn(300).delay(2500).fadeOut(400);
                } else {
                    msg.html(
                        `<div class="error"><p>❌ ${resp.data || 'Error al guardar los ajustes.'}</p></div>`
                    ).fadeIn(300);
                }
            },
            error: function () {
                btn.prop('disabled', false).text('💾 Guardar ajustes');
                msg.hide().html(
                    `<div class="error"><p>❌ Error inesperado. Verifique su conexión o recargue la página.</p></div>`
                ).fadeIn(300);
            }
        });
    });
});
