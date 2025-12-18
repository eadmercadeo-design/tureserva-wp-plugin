jQuery(document).ready(function ($) {

    // UI Elements
    const modal = $('#modal-create-token');
    const openBtn = $('#btn-open-create-token');
    const closeBtn = $('.close-modal');
    const generateBtn = $('#btn-generate-token');
    const resultContainer = $('#token-result-container');
    const inputToken = $('#generated-token-value');
    const inputName = $('#new-token-name');
    const tableBody = $('#tureserva-tabla-tokens');

    // Open Modal
    openBtn.on('click', function () {
        // Reset form
        inputName.val('');
        resultContainer.hide();
        generateBtn.show();
        $('input[name="scopes[]"]').prop('checked', false);
        $('input[name="scopes[]"][value="read:reservas"]').prop('checked', true); // Default
        modal.show();
    });

    // Close Modal
    closeBtn.on('click', function () {
        modal.hide();
        // If token was generated, reload to show new list row properly (optional, but good for sync)
        if (resultContainer.is(':visible')) {
            location.reload();
        }
    });

    // Generate Request
    generateBtn.on('click', function () {
        const name = inputName.val();
        if (!name) {
            alert('Por favor ingresa un nombre para el token.');
            return;
        }

        const scopes = [];
        $('input[name="scopes[]"]:checked').each(function () {
            scopes.push($(this).val());
        });

        generateBtn.prop('disabled', true).text('Generando...');

        $.post(tureservaTokens.ajax_url, {
            action: 'tureserva_create_token',
            nonce: tureservaTokens.nonce,
            name: name,
            scopes: scopes
        }, function (resp) {
            generateBtn.prop('disabled', false).text('Generar Token');

            if (resp.success) {
                const data = resp.data;
                // Show result
                inputToken.val(data.token);
                resultContainer.show();
                generateBtn.hide(); // Hide button to prevent double creation and force user to copy

                // Add to table mostly for visual, but reload is better to get IDs right if needed for revoke immediately
                // But let's append visually
                const row = `
                    <tr class="new-row" style="background:#f0fafe">
                        <td><strong>${data.name}</strong></td>
                        <td><code>${data.prefix}...</code></td>
                        <td><span class="scopes-badge">${data.scopes}</span></td>
                        <td><span class="status-badge status-active">Activo</span></td>
                        <td>—</td>
                        <td>${data.created_at}</td>
                        <td>(Recarga para gestionar)</td>
                    </tr>
                `;
                if (tableBody.find('.no-items').length) {
                    tableBody.empty();
                }
                tableBody.prepend(row);

            } else {
                alert('Error: ' + resp.data);
            }
        });
    });

    // Copy Token
    $('#btn-copy-token').on('click', function () {
        const token = inputToken.val();
        navigator.clipboard.writeText(token).then(() => {
            $(this).text('¡Copiado!');
            setTimeout(() => $(this).text('Copiar'), 2000);
        });
    });

    // Revoke Token
    $(document).on('click', '.revocar-token', function () {
        if (!confirm('¿Estás seguro de que quieres revocar este token? Esta acción es irreversible y cualquier integración que lo use dejará de funcionar.')) return;

        const btn = $(this);
        const id = btn.data('id');

        btn.prop('disabled', true).text('Revocando...');

        $.post(tureservaTokens.ajax_url, {
            action: 'tureserva_revoke_token',
            nonce: tureservaTokens.nonce,
            id: id
        }, function (resp) {
            if (resp.success) {
                btn.closest('tr').find('.status-badge').removeClass('status-active').addClass('status-revoked').text('Revocado');
                btn.remove();
            } else {
                btn.text('Error').prop('disabled', false);
                alert('Error al revocar.');
            }
        });
    });

});
