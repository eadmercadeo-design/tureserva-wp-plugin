<?php
/**
 * ==========================================================
 * ADMIN: Panel de Configuración Supabase — TuReserva
 * ==========================================================
 * Permite ingresar y guardar las claves de Supabase desde
 * el dashboard de WordPress, con prueba de conexión en vivo.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 Registrar página de menú dentro de "Reservas"
// =======================================================
add_action( 'admin_menu', 'tureserva_supabase_menu' );
function tureserva_supabase_menu() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva', // Menú principal: Reservas
        'Sincronización Cloud',
        'Sincronización Cloud',
        'manage_options',
        'tureserva-supabase',
        'tureserva_supabase_panel_render'
    );
}

// =======================================================
// 🧩 Renderizar el panel
// =======================================================
function tureserva_supabase_panel_render() {

    // Guardar datos si se envía el formulario
    if ( isset( $_POST['tureserva_guardar_supabase'] ) && check_admin_referer( 'tureserva_supabase_guardar' ) ) {
        update_option( 'tureserva_supabase_url', sanitize_text_field( $_POST['tureserva_supabase_url'] ) );
        update_option( 'tureserva_supabase_key', sanitize_text_field( $_POST['tureserva_supabase_key'] ) );

        echo '<div class="updated notice"><p>✅ Configuración guardada correctamente.</p></div>';
    }

    $url = get_option( 'tureserva_supabase_url', '' );
    $key = get_option( 'tureserva_supabase_key', '' );
    ?>
    <div class="wrap">
        <h1>☁️ Sincronización Cloud – Supabase</h1>
        <p>Configura la conexión con tu base de datos Supabase para sincronizar reservas y alojamientos.</p>
        <form method="POST">
            <?php wp_nonce_field( 'tureserva_supabase_guardar' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_supabase_url">🔗 URL del Proyecto Supabase</label></th>
                    <td>
                        <input type="text" id="tureserva_supabase_url" name="tureserva_supabase_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" placeholder="https://tu-proyecto.supabase.co/rest/v1">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="tureserva_supabase_key">🔑 API Key</label></th>
                    <td>
                        <input type="password" id="tureserva_supabase_key" name="tureserva_supabase_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text" placeholder="tu_clave_api_publica">
                        <p class="description">Utiliza tu clave pública (anon) de Supabase.</p>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" name="tureserva_guardar_supabase" class="button button-primary">💾 Guardar configuración</button>
                <button type="button" id="tureserva-test-connection" class="button">🔍 Probar conexión</button>
                <span id="tureserva-test-result" style="margin-left:10px;"></span>
            </p>
        </form>
    </div>
    <?php
}

// =======================================================
// ⚙️ AJAX – Probar conexión a Supabase
// =======================================================
add_action( 'wp_ajax_tureserva_test_supabase', 'tureserva_ajax_test_supabase' );

function tureserva_ajax_test_supabase() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'No autorizado' );
    }

    if ( ! function_exists( 'tureserva_sync_test_connection' ) ) {
        require_once TURESERVA_PATH . 'core/core-sync.php';
    }

    $resultado = tureserva_sync_test_connection();
    wp_send_json_success( array( 'mensaje' => $resultado ) );
}

// =======================================================
// 🧩 Script JS para el botón AJAX
// =======================================================
add_action( 'admin_footer', function() {
    $screen = get_current_screen();
    if ( $screen && $screen->id === 'tureserva_reserva_page_tureserva-supabase' ) : ?>
        <script>
        jQuery(document).ready(function($){
            $('#tureserva-test-connection').on('click', function(){
                $('#tureserva-test-result').text('⏳ Probando conexión...');
                $.post(ajaxurl, { action: 'tureserva_test_supabase' }, function(response){
                    if(response.success){
                        $('#tureserva-test-result').html('<strong>' + response.data.mensaje + '</strong>');
                    } else {
                        $('#tureserva-test-result').html('❌ Error en la conexión.');
                    }
                });
            });
        });
        </script>
    <?php endif;
});
