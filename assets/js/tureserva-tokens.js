jQuery(document).ready(function ($) {

    const crearBtn = $('#tureserva-crear-token');
    const tabla = $('#tureserva-tabla-tokens');
    const msg = $('#tureserva-msg-token');

    // Crear nuevo token
    crearBtn.on('click', function () {
        const nombre = $('#tureserva-nombre-token').val() || 'Token sin nombre';
        crearBtn.prop('disabled', true).text('Generando...');

        $.post(tureservaTokens.ajax_url, {
            action: 'tureserva_crear_token',
            nombre: nombre,
            nonce: tureservaTokens.nonce
        }, function (resp) {
            crearBtn.prop('disabled', false).text('➕ Generar nuevo token');
            if (!resp.success) return alert('Error al crear el token.');

            const t = resp.data;
            msg.html('<div class="updated"><p>✅ Token generado correctamente: <strong>' + t.key + '</strong></p></div>').show();

            tabla.append(`
                <tr data-key="${t.key}">
                    <td>${t.nombre}</td>
                    <td><code class="token-code">${t.key.substring(0,10)}...</code> <button class="button button-small copiar-token" data-token="${t.key}">Copiar</button></td>
                    <td>${new Date().toLocaleString()}</td>
                    <td><span class="status activo">Activo</span></td>
                    <td><button class="button button-secondary revocar-token" data-token="${t.key}">Revocar</button></td>
                </tr>
            `);
        });
    });

    // Revocar token
    tabla.on('click', '.revocar-token', function () {
        const btn = $(this);
        const key = btn.data('token');
        if (!confirm('¿Seguro que deseas revocar este token?')) return;

        $.post(tureservaTokens.ajax_url, {
            action: 'tureserva_revocar_token',
            key: key,
            nonce: tureservaTokens.nonce
        }, function (resp) {
            if (resp.success) {
                btn.closest('tr').find('.status').removeClass('activo').addClass('inactivo').text('Revocado');
                btn.replaceWith('—');
                msg.html('<div class="updated"><p>🔒 Token revocado correctamente.</p></div>').show();
            } else {
                msg.html('<div class="error"><p>❌ Error al revocar el token.</p></div>').show();
            }
        });
    });

    // Copiar token
    tabla.on('click', '.copiar-token', function () {
        const token = $(this).data('token');
        navigator.clipboard.writeText(token);
        alert('Token copiado al portapapeles.');
    });
});
