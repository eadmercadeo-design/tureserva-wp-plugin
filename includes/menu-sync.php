<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Sincronización Cloud — TuReserva
 * ==========================================================
 * Permite configurar la conexión con Supabase y ejecutar
 * sincronizaciones manuales de alojamientos o reservas.
 * Toda la lógica se maneja desde core/core-sync.php.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_sync' );
function tureserva_menu_sync() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        'Sincronización Cloud',
        'Sincronización Cloud',
        'manage_options',
        'tureserva_sync',
        'tureserva_vista_sync'
    );
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS
// =======================================================
add_action( 'admin_enqueue_scripts', 'tureserva_sync_assets' );
function tureserva_sync_assets( $hook ) {
    if ( strpos( $hook, 'tureserva_sync' ) === false ) return;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script(
        'tureserva-sync-js',
        TURESERVA_URL . 'assets/js/tureserva-sync.js',
        array( 'jquery' ),
        TURESERVA_VERSION,
        true
    );

    wp_localize_script( 'tureserva-sync-js', 'tureservaSync', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tureserva_sync_nonce' ),
    ));

    wp_enqueue_style(
        'tureserva-sync-css',
        TURESERVA_URL . 'assets/css/tureserva-sync.css',
        array(),
        TURESERVA_VERSION
    );
}

// =======================================================
// ⚙️ INTERFAZ DEL PANEL DE SINCRONIZACIÓN
// =======================================================
function tureserva_vista_sync() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $url    = get_option( 'tureserva_supabase_url', '' );
    $key    = get_option( 'tureserva_supabase_key', '' );
    $ultimo = get_option( 'tureserva_ultima_sync', '—' );
    ?>
    <div class="wrap">
        <h1>☁️ Sincronización Cloud — TuReserva</h1>
        <p>
            Configura la conexión entre <strong>TuReserva</strong> y <strong>Supabase</strong>
            para mantener copias en la nube y análisis externos.
        </p>

        <form id="tureserva-form-sync">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_supabase_url">URL de Supabase</label></th>
                    <td>
                        <input type="text" id="tureserva_supabase_url" name="tureserva_supabase_url"
                               value="<?php echo esc_attr( $url ); ?>" class="regular-text" placeholder="https://xxxxx.supabase.co/rest/v1">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_supabase_key">API Key</label></th>
                    <td>
                        <input type="password" id="tureserva_supabase_key" name="tureserva_supabase_key"
                               value="<?php echo esc_attr( $key ); ?>" class="regular-text" placeholder="sk-...">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Última sincronización</th>
                    <td><strong><?php echo esc_html( $ultimo ); ?></strong></td>
                </tr>
            </table>

            <p class="submit">
                <button type="button" id="tureserva-guardar-sync" class="button button-primary">💾 Guardar configuración</button>
                <button type="button" id="tureserva-probar-sync" class="button">🔍 Probar conexión</button>
                <button type="button" id="tureserva-enviar-alojamientos" class="button button-secondary">☁️ Sincronizar alojamientos</button>
            </p>
        </form>

        <div id="tureserva-sync-resultado" style="display:none;margin-top:15px;"></div>
    </div>
    <?php
}

// =======================================================
// 🔧 AJAX: Guardar configuración de Supabase
// =======================================================
add_action( 'wp_ajax_tureserva_guardar_sync', 'tureserva_guardar_sync' );
function tureserva_guardar_sync() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );
    check_ajax_referer( 'tureserva_sync_nonce', 'nonce' );

    update_option( 'tureserva_supabase_url', sanitize_text_field( $_POST['url'] ?? '' ) );
    update_option( 'tureserva_supabase_key', sanitize_text_field( $_POST['key'] ?? '' ) );

    wp_send_json_success( array( 'mensaje' => '✅ Configuración guardada correctamente.' ) );
}

// =======================================================
// 🔍 AJAX: Probar conexión con Supabase
// =======================================================
add_action( 'wp_ajax_tureserva_probar_sync', 'tureserva_probar_sync' );
function tureserva_probar_sync() {
    $url = get_option( 'tureserva_supabase_url' );
    $key = get_option( 'tureserva_supabase_key' );

    if ( empty( $url ) || empty( $key ) ) {
        wp_send_json_error( array( 'mensaje' => '⚠️ Faltan credenciales de Supabase.' ) );
    }

    $response = wp_remote_get( $url, array(
        'headers' => array(
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ),
        'timeout' => 10,
    ));

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'mensaje' => '❌ Error de conexión: ' . $response->get_error_message() ) );
    }

    $code = wp_remote_retrieve_response_code( $response );

    if ( $code === 200 ) {
        wp_send_json_success( array( 'mensaje' => '✅ Conexión exitosa con Supabase.' ) );
    } else {
        wp_send_json_error( array( 'mensaje' => '❌ Supabase respondió con código ' . $code ) );
    }
}

// =======================================================
// 🚫 IMPORTANTE
// =======================================================
// No declarar aquí funciones del núcleo como:
// - tureserva_sync_default_options()
// - tureserva_sync_to_supabase()
// - tureserva_sync_alojamientos()
// Esas pertenecen únicamente a core/core-sync.php
