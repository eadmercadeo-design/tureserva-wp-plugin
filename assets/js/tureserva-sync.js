/**
 * ==========================================================
 * TuReserva – Sincronización Cloud (Dashboard Pro)
 * ==========================================================
 * Control avanzado del panel de sincronización con:
 * - Spinner animado
 * - Barra de progreso
 * - Log en tiempo real
 * ==========================================================
 */

jQuery(document).ready(function ($) {

    const form = $('#tureserva-form-sync');
    const resultado = $('#tureserva-sync-resultado');
    const logBox = $('#tureserva-sync-log');

    const btnGuardar = $('#tureserva-guardar-sync');
    const btnProbar = $('#tureserva-probar-sync');
    const btnSync = $('#tureserva-enviar-alojamientos');
    const progressBar = $('#tureserva-sync-progress-bar');

    // ------------------------------------------
    // 🧠 Utilidades visuales
    // ------------------------------------------
    function log(text, type = 'info') {
        const color = {
            info: '#2271b1',
            success: '#008000',
            error: '#b32d2e'
        }[type] || '#000';
        const line = $('<div>').text(text).css({ color, marginBottom: '3px' });
        logBox.append(line);
        logBox.scrollTop(logBox.prop('scrollHeight'));
    }

    function toggleLoading(state) {
        if (state) {
            progressBar.css('width', '10%').addClass('active');
        } else {
            progressBar.removeClass('active').css('width', '0%');
        }
    }

    function updateProgress(percent) {
        progressBar.css('width', percent + '%');
    }

    // ------------------------------------------
    // 💾 Guardar configuración
    // ------------------------------------------
    btnGuardar.on('click', function (e) {
        e.preventDefault();
        toggleLoading(true);
        logBox.empty();
        log('💾 Guardando configuración...', 'info');

        $.post(tureservaSync.ajax_url, {
            action: 'tureserva_guardar_sync',
            nonce: tureservaSync.nonce,
            url: $('#tureserva_supabase_url').val(),
            key: $('#tureserva_supabase_key').val()
        }).done((resp) => {
            if (resp.success) {
                updateProgress(50);
                log(resp.data.mensaje, 'success');
                setTimeout(() => updateProgress(100), 500);
            } else {
                log(resp.data?.mensaje || '❌ Error al guardar.', 'error');
            }
        }).fail(() => {
            log('❌ No se pudo conectar con el servidor.', 'error');
        }).always(() => {
            setTimeout(() => toggleLoading(false), 1200);
        });
    });

    // ------------------------------------------
    // 🔍 Probar conexión
    // ------------------------------------------
    btnProbar.on('click', function (e) {
        e.preventDefault();
        toggleLoading(true);
        logBox.empty();
        log('🔍 Probando conexión con Supabase...', 'info');

        $.post(tureservaSync.ajax_url, {
            action: 'tureserva_probar_sync',
            nonce: tureservaSync.nonce
        }).done((resp) => {
            if (resp.success) {
                updateProgress(70);
                log(resp.data.mensaje, 'success');
                setTimeout(() => updateProgress(100), 400);
            } else {
                log(resp.data?.mensaje || '❌ Error de conexión.', 'error');
            }
        }).fail(() => {
            log('❌ No se pudo contactar con el servidor.', 'error');
        }).always(() => {
            setTimeout(() => toggleLoading(false), 1200);
        });
    });

    // ------------------------------------------
    // ☁️ Sincronizar alojamientos manualmente
    // ------------------------------------------
    btnSync.on('click', function (e) {
        e.preventDefault();
        toggleLoading(true);
        logBox.empty();
        log('☁️ Iniciando sincronización de alojamientos...', 'info');

        let steps = [20, 40, 60, 80, 100];
        let stepIndex = 0;

        const interval = setInterval(() => {
            if (stepIndex < steps.length) {
                updateProgress(steps[stepIndex]);
                stepIndex++;
            }
        }, 300);

        $.post(tureservaSync.ajax_url, {
            action: 'tureserva_sync_alojamientos',
            nonce: tureservaSync.nonce
        }).done((resp) => {
            if (resp.success) {
                log(resp.data.mensaje, 'success');
            } else {
                log(resp.data?.mensaje || '❌ Error durante la sincronización.', 'error');
            }
        }).fail(() => {
            log('❌ Error de comunicación con el servidor.', 'error');
        }).always(() => {
            clearInterval(interval);
            setTimeout(() => toggleLoading(false), 1200);
        });
    });
});
