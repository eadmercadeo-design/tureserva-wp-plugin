jQuery(document).ready(function($){

    // ========================================
    // 🔘 PROBAR CONEXIÓN
    // ========================================
    $('#tureserva-probar-conexion').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $btn.text('Conectando...').prop('disabled', true);

        $.post(ajaxurl, { action: 'tureserva_test_supabase_connection' }, function(response){
            if(response.success){
                alert('✅ ' + response.data);
            } else {
                alert('❌ ' + response.data);
            }
            $btn.text('Probar conexión').prop('disabled', false);
        }).fail(function(){
            alert('⚠️ Error de comunicación con el servidor.');
            $btn.text('Probar conexión').prop('disabled', false);
        });
    });

    // ========================================
    // 💾 GUARDAR CONFIGURACIÓN
    // ========================================
    $('#tureserva-guardar-supabase').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        const url = $('#tureserva_supabase_url').val();
        const key = $('#tureserva_supabase_key').val();

        if(!url || !key){
            alert('Por favor completa los campos URL y API Key.');
            return;
        }

        $btn.text('Guardando...').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'tureserva_save_supabase_settings',
            url: url,
            key: key
        }, function(response){
            if(response.success){
                alert('✅ Configuración guardada correctamente.');
            } else {
                alert('❌ ' + response.data);
            }
            $btn.text('Guardar configuración').prop('disabled', false);
        }).fail(function(){
            alert('⚠️ Error de comunicación con el servidor.');
            $btn.text('Guardar configuración').prop('disabled', false);
        });
    });

});
