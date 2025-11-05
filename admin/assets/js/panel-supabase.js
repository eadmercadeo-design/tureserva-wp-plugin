jQuery(document).ready(function($){

    // =======================================================
    // 🧰 Función auxiliar: mostrar mensajes tipo "notice"
    // =======================================================
    function showNotice(type, message) {
        const notice = $('<div>')
            .addClass('notice is-dismissible')
            .addClass(type === 'error' ? 'notice-error' : 'notice-success')
            .append(`<p><strong>${message}</strong></p>`);
        $('.wrap h1').after(notice);
        setTimeout(() => notice.fadeOut(400, () => notice.remove()), 5000);
    }

    // =======================================================
    // 💾 GUARDAR CONFIGURACIÓN
    // =======================================================
    $('#tureserva-guardar-supabase').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        const url = $('#tureserva_supabase_url').val().trim();
        const key = $('#tureserva_supabase_key').val().trim();

        if(!url || !key){
            showNotice('error', 'Por favor completa los campos URL y API Key.');
            return;
        }

        $btn.text('Guardando...').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'tureserva_save_supabase_settings',
            url: url,
            key: key
        }, function(response){
            if(response.success){
                showNotice('success', '✅ Configuración guardada correctamente.');
            } else {
                showNotice('error', '❌ ' + response.data);
            }
        }).fail(function(){
            showNotice('error', '⚠️ Error de comunicación con el servidor.');
        }).always(function(){
            $btn.text('💾 Guardar configuración').prop('disabled', false);
        });
    });

    // =======================================================
    // 🔌 PROBAR CONEXIÓN
    // =======================================================
    $('#tureserva-probar-conexion').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $btn.text('Conectando...').prop('disabled', true);

        $.post(ajaxurl, { action: 'tureserva_test_supabase_connection' }, function(response){
            if(response.success){
                showNotice('success', '✅ ' + response.data);
            } else {
                showNotice('error', '❌ ' + response.data);
            }
        }).fail(function(){
            showNotice('error', '⚠️ Error de comunicación con el servidor.');
        }).always(function(){
            $btn.text('🧪 Probar conexión').prop('disabled', false);
        });
    });

    // =======================================================
    // 🔁 SINCRONIZAR ALOJAMIENTOS
    // =======================================================
    $('#tureserva-sync-alojamientos').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $btn.text('Sincronizando...').prop('disabled', true);

        $.post(ajaxurl, { action: 'tureserva_sync_alojamientos' }, function(response){
            if(response.success){
                showNotice('success', response.data || '✅ Sincronización completada.');
            } else {
                showNotice('error', '❌ ' + (response.data || 'No se pudo sincronizar.'));
            }
        }).fail(function(){
            showNotice('error', '⚠️ Error de conexión con el servidor.');
        }).always(function(){
            $btn.text('🔁 Sincronizar alojamientos').prop('disabled', false);
        });
    });

    // =======================================================
    // 💳 SINCRONIZAR PAGOS COMPLETADOS
    // =======================================================
    $('#tureserva-sync-pagos-manual').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $btn.text('Sincronizando...').prop('disabled', true);

        $.post(ajaxurl, { action: 'tureserva_sync_pagos_manual_panel' }, function(response){
            if(response.success){
                showNotice('success', '✅ ' + response.data);
            } else {
                showNotice('error', '❌ ' + (response.data || 'No se pudieron sincronizar los pagos.'));
            }
        }).fail(function(){
            showNotice('error', '⚠️ Error de conexión con el servidor.');
        }).always(function(){
            $btn.text('💳 Sincronizar pagos completados').prop('disabled', false);
        });
    });

    // =======================================================
    // 📥 DESCARGAR PAGOS DESDE SUPABASE
    // =======================================================
    $('#tureserva-sync-pagos-from-supabase').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $btn.text('Descargando...').prop('disabled', true);

        $.post(ajaxurl, { action: 'tureserva_sync_pagos_from_supabase', limit: 50 }, function(response){
            if(response.success){
                showNotice('success', '✅ ' + response.data);
            } else {
                showNotice('error', '❌ ' + (response.data || 'No se pudieron descargar los pagos.'));
            }
        }).fail(function(){
            showNotice('error', '⚠️ Error de conexión con el servidor.');
        }).always(function(){
            $btn.text('📥 Descargar pagos desde Supabase').prop('disabled', false);
        });
    });

});
