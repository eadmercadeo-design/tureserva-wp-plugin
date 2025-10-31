/**
 * ==========================================================
 * JS — Sincronización Cloud (Fase 7)
 * ==========================================================
 * Controla la sincronización AJAX con barra de progreso,
 * contador dinámico y estado visual en tiempo real.
 * ==========================================================
 */

jQuery(document).ready(function ($) {
    const $button = $('#tureserva-sync-cloud');
    const $progress = $('#tureserva-sync-progress');
    const $status = $('#tureserva-sync-status');
    const $lastSyncField = $('input[readonly][value*="-"]'); // Campo "Última sincronización"

    $button.on('click', function (e) {
        e.preventDefault();

        $button.prop('disabled', true).text('Sincronizando...');
        $progress.css('width', '0%');
        $status.text('Obteniendo alojamientos...');

        // 🔹 1. Obtener lista de alojamientos
        $.ajax({
            url: tureserva_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'tureserva_cloud_get_alojamientos',
                security: tureserva_ajax.nonce
            },
            success: function (response) {
                if (!response.success || !response.data.length) {
                    $status.text('⚠️ No se encontraron alojamientos para sincronizar.');
                    $button.text('Sincronizar alojamientos').prop('disabled', false);
                    return;
                }

                const alojamientos = response.data;
                const total = alojamientos.length;
                let current = 0;

                $status.text(`Sincronizando alojamiento 1 de ${total}...`);

                // 🔁 2. Enviar uno por uno
                function syncNext() {
                    if (current >= total) {
                        // ✅ Finalizado
                        animateProgress(100);
                        $status.text('✅ Sincronización completada con éxito.');
                        $button.text('Sincronizar alojamientos').prop('disabled', false);

                        // 🔄 Actualizar "Última sincronización" sin recargar
                        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
                        $lastSyncField.val(now);
                        return;
                    }

                    const alojamiento = alojamientos[current];
                    const percent = Math.round(((current + 1) / total) * 100);
                    $status.text(`Sincronizando alojamiento ${current + 1} de ${total}...`);
                    animateProgress(percent);

                    $.ajax({
                        url: tureserva_ajax.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'tureserva_cloud_sync_single',
                            security: tureserva_ajax.nonce,
                            alojamiento_id: alojamiento.ID
                        },
                        success: function (res) {
                            if (res.success) {
                                console.log(`✔️ ${alojamiento.post_title} sincronizado.`);
                            } else {
                                console.warn(`❌ Error con ${alojamiento.post_title}`);
                            }
                            current++;
                            syncNext();
                        },
                        error: function () {
                            console.error(`❌ Error de conexión con ${alojamiento.post_title}`);
                            current++;
                            syncNext();
                        }
                    });
                }

                // Iniciar proce
