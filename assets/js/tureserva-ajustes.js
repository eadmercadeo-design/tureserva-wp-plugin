jQuery(document).ready(function ($) {
    const btn = $('#tureserva-guardar-ajustes');
    const msg = $('#tureserva-resultado-ajax');

    btn.on('click', function () {
        const data = $('#tureserva-form-ajustes').serialize();
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: tureservaAjustes.ajax_url,
            method: 'POST',
            data: {
                action: 'tureserva_guardar_ajustes',
                nonce: tureservaAjustes.nonce,
                ...Object.fromEntries(new URLSearchParams(data))
            },
            success: function (resp) {
                btn.prop('disabled', false).text('💾 Guardar ajustes');
                msg.show().html('<div class="updated"><p>' + resp.data.mensaje + '</p></div>');
            },
            error: function () {
                btn.prop('disabled', false).text('💾 Guardar ajustes');
                msg.show().html('<div class="error"><p>❌ Error al guardar los ajustes.</p></div>');
            }
        });
    });
});
