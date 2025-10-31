<?php
/**
 * ==========================================================
 * ADMIN PAGE — Sincronización Cloud con Supabase
 * ==========================================================
 * Configura la conexión entre TuReserva y Supabase.
 * Ahora incluye soporte para barra de progreso AJAX.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Registrar submenú dentro del menú "Reservas"
// =======================================================
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Sincronización Cloud — TuReserva', 'tureserva'),
        __('Sincronización Cloud', 'tureserva'),
        'manage_options',
        'tureserva-cloud-sync',
        'tureserva_cloud_sync_page'
    );
});

// =======================================================
// 🖥️ Render de la página principal
// =======================================================
function tureserva_cloud_sync_page() {
    $supabase_url = get_option('tureserva_supabase_url', '');
    $supabase_key = get_option('tureserva_supabase_api_key', '');
    $last_sync    = get_option('tureserva_cloud_last_sync', '—');
    ?>

    <div class="wrap">
        <h1><?php _e('Sincronización Cloud — TuReserva', 'tureserva'); ?></h1>
        <p><?php _e('Configura la conexión entre TuReserva y Supabase para mantener copias en la nube y análisis externos.', 'tureserva'); ?></p>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="max-width:700px;background:#fff;padding:25px 30px;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <?php wp_nonce_field('tureserva_save_supabase_settings'); ?>
            <input type="hidden" name="action" value="tureserva_save_supabase_settings">

            <div style="margin-bottom:20px;">
                <label for="tureserva_supabase_url" style="font-weight:600;display:block;margin-bottom:6px;"><?php _e('URL de Supabase', 'tureserva'); ?></label>
                <input type="url" id="tureserva_supabase_url" name="tureserva_supabase_url" value="<?php echo esc_attr($supabase_url); ?>" placeholder="https://TU_SUPABASE_URL.supabase.co/rest/v1" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:4px;">
            </div>

            <div style="margin-bottom:20px;">
                <label for="tureserva_supabase_api_key" style="font-weight:600;display:block;margin-bottom:6px;"><?php _e('API Key', 'tureserva'); ?></label>
                <input type="password" id="tureserva_supabase_api_key" name="tureserva_supabase_api_key" value="<?php echo esc_attr($supabase_key); ?>" placeholder="••••••••••••••••••••••••••••" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:4px;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="font-weight:600;display:block;margin-bottom:6px;"><?php _e('Última sincronización', 'tureserva'); ?></label>
                <input type="text" value="<?php echo esc_html($last_sync); ?>" readonly style="width:100%;padding:8px 10px;border:1px solid #eee;background:#f8f8f8;border-radius:4px;color:#555;">
            </div>

            <div style="margin-top:25px;">
                <button type="submit" class="button button-primary"><?php _e('Guardar configuración', 'tureserva'); ?></button>
                <a href="<?php echo admin_url('admin-post.php?action=tureserva_test_supabase_connection'); ?>" class="button"><?php _e('Probar conexión', 'tureserva'); ?></a>

                <!-- 🆕 CAMBIO: se reemplaza el enlace por un botón AJAX -->
                <button type="button" id="tureserva-sync-cloud" class="button button-secondary">
                    <?php _e('Sincronizar alojamientos', 'tureserva'); ?>
                </button>
            </div>

            <!-- 🧩 NUEVO BLOQUE: barra de progreso y estado dinámico -->
            <div style="margin-top:25px;width:100%;max-width:400px;background:#eee;border-radius:6px;height:10px;overflow:hidden;">
                <div id="tureserva-sync-progress" style="width:0%;height:10px;background:#2271b1;transition:width .3s;"></div>
            </div>
            <p id="tureserva-sync-status" style="margin-top:10px;font-weight:500;color:#444;"></p>
        </form>
    </div>

    <?php
    // 🧩 NUEVO BLOQUE: Encolar script para AJAX + pasar variables al JS
    wp_enqueue_script('tureserva-cloud-sync', TURESERVA_URL . 'assets/js/cloud-sync.js', ['jquery'], TURESERVA_VERSION, true);

    wp_localize_script('tureserva-cloud-sync', 'tureserva_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_cloud_sync_nonce')
    ]);
}

// =======================================================
// 💾 Guardar configuración Supabase
// =======================================================
add_action('admin_post_tureserva_save_supabase_settings', function () {
    if (!current_user_can('manage_options') || !check_admin_referer('tureserva_save_supabase_settings')) return;

    update_option('tureserva_supabase_url', sanitize_text_field($_POST['tureserva_supabase_url']));
    update_option('tureserva_supabase_api_key', sanitize_text_field($_POST['tureserva_supabase_api_key']));

    wp_redirect(admin_url('edit.php?post_type=reserva&page=tureserva-cloud-sync&updated=1'));
    exit;
});

// =======================================================
// 🔍 Probar conexión con Supabase
// =======================================================
add_action('admin_post_tureserva_test_supabase_connection', function () {
    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_api_key');

    if (!$url || !$key) {
        wp_die(__('Debe configurar primero la URL y API Key de Supabase.', 'tureserva'));
    }

    $response = wp_remote_get($url, [
        'headers' => ['apikey' => $key],
        'timeout' => 15
    ]);

    if (is_wp_error($response)) {
        wp_die(__('Error al conectar con Supabase: ', 'tureserva') . $response->get_error_message());
    }

    echo '<div class="wrap"><h1>✅ Conexión exitosa con Supabase</h1>';
    echo '<p>La API respondió correctamente.</p>';
    echo '<a href="' . admin_url('edit.php?post_type=reserva&page=tureserva-cloud-sync') . '" class="button">Volver</a></div>';
    exit;
});
