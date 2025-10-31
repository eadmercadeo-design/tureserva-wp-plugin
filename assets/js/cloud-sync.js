/**
 * ==========================================================
 * JS — Sincronización Cloud (Fase 9)
 * ==========================================================
 * Barra de progreso + contador + log visual de resultados +
 * registro automático de logs en base de datos y Supabase.
 * ==========================================================
 */

jQuery(document).ready(function ($) {

    // =======================================================
    // 🔧 Variables base de los elementos del panel
    // =======================================================
    const $button = $('#tureserva-sync-cloud');
    const $progress = $('#tureserva-sync-progress');
    const $status = $('#tureserva-sync-status');
    const $lastSyncField = $('input[readonly][value*="-"]'); // Campo "Última sincronización"
    const $logBox = $('#tureserva-log-list'); // Contenedor del log visual

    // =======================================================
    // 🚀 Evento principal: clic en "Sincronizar alojamientos"
    // =======================================================
    $button.on('click', function (e) {
        e.preventDefault();

        // Reset visual
        $logBox.empty();
        $button.prop('disabled', true).text('Sincronizando...');
        $progress.css('width', '0%');
        $status.text('Obteniendo alojamientos...');

        const inicioProceso = Date.now(); // 🕓 Nuevo: para calcular duración

        // =======================================================
        // 🔹 1. Obtener lista de alojamientos
        // =======================================================
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
                    appendLog('⚠️ No se encontraron alojamientos para sincronizar.');
                    $button.text('Sincronizar alojamientos').prop('disabled', false);
                    return;
                }

                const alojamientos = response.data;
                const total = alojamientos.length;
                let current = 0;
                let ok = 0;
                let fail = 0;

                $status.text(`Sincronizando alojamiento 1 de ${total}...`);
                appendLog(`🔄 Iniciando sincronización de ${total} alojamientos...`);

                // =======================================================
                // 🔁 2. Enviar uno por uno y actualizar barra/log
                // =======================================================
                function syncNext() {

                    // ✅ FINALIZADO
                    if (current >= total) {
                        animateProgress(100);
                        $status.text('✅ Sincronización completada con éxito.');
                        appendLog(`✅ Finalizado. Correctos: ${ok} | Fallidos: ${fail}`);
                        $button.text('Sincronizar alojamientos').prop('disabled', false);

                        // 🔄 Actualizar "Última sincronización"
                        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
                        $lastSyncField.val(now);

                        // 🕓 Calcular duración y guardar log automáticamente
                        const duracion = Math.round((Date.now() - inicioProceso) / 1000);
                        const resumen = `Sincronización completada. ${ok} correctos y ${fail} fallidos.`;
                        saveLog(total, ok, fail, duracion, inicioProceso, resumen);

                        return;
                    }

                    // 🔄 Procesar siguiente alojamiento
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
                                ok++;
                                appendLog(`🟢 ${alojamiento.post_title} sincronizado correctamente.`);
                            } else {
                                fail++;
                                appendLog(`🔴 ${alojamiento.post_title}: ${res.data || 'Error en Supabase.'}`);
                            }
                            current++;
                            syncNext();
                        },
                        error: function () {
                            fail++;
                            appendLog(`🔴 ${alojamiento.post_title}: error de conexión.`);
                            current++;
                            syncNext();
                        }
                    });
                }

                // Iniciar proceso
                syncNext();
            },
            error: function () {
                $status.text('❌ Error al obtener alojamientos.');
                appendLog('❌ Error al obtener alojamientos desde WordPress.');
                $button.text('Reintentar').prop('disabled', false);
            }
        });
    });

    // =======================================================
    // 🎨 Animación de barra de progreso
    // =======================================================
    function animateProgress(target) {
        $progress.css('width', target + '%');
    }

    // =======================================================
    // 🧾 Añadir mensajes al log visual
    // =======================================================
    function appendLog(message) {
        const $li = $('<li/>').text(message).css({
            marginBottom: '4px',
            fontFamily: 'monospace'
        });
        $logBox.append($li);
        $logBox.parent().scrollTop($logBox[0].scrollHeight);
    }

    // =======================================================
    // 🧩 Guardar log en base de datos y Supabase (AJAX)
    // =======================================================
    function saveLog(total, ok, fail, duracion, inicio, resumen) {
        appendLog('🗃️ Guardando registro de sincronización...');

        $.ajax({
            url: tureserva_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'tureserva_cloud_save_log',
                security: tureserva_ajax.nonce,
                total: total,
                ok: ok,
                fail: fail,
                duracion: duracion,
                inicio: new Date(inicio).toISOString().slice(0, 19).replace('T', ' '),
                resumen: resumen
            },
            success: function (res) {
                if (res.success) {
                    appendLog(`✅ Log guardado correctamente en la base de datos.`);
                } else {
                    appendLog('⚠️ Error al guardar log en base de datos.');
                }
            },
            error: function () {
                appendLog('⚠️ No se pudo registrar el log en la base de datos.');
            }
        });
    }
});
