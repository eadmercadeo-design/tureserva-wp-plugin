<?php
/**
 * ==========================================================
 * ADMIN PAGE — Cloud Sync (Supabase)
 * ==========================================================
 * Sincronización en la nube con Supabase.
 * Incluye barra de progreso, logs y dashboard visual.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🖥️ INTERFAZ PRINCIPAL DE CLOUD SYNC
// =======================================================
function tureserva_cloud_sync_page() {
    $supabase_url = get_option('tureserva_supabase_url', '');
    $supabase_key = get_option('tureserva_supabase_api_key', '');
    $last_sync    = get_option('tureserva_cloud_last_sync', '—');
    ?>
    <div class="wrap">
        <h1><span style="color:#2271b1;">☁</span> <?php _e('Cloud Sync — TuReserva', 'tureserva'); ?></h1>
        <p><?php _e('Conecte TuReserva con Supabase para mantener copias de seguridad y análisis externos.', 'tureserva'); ?></p>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="max-width:700px;background:#fff;padding:25px 30px;border:1px solid #ddd;border-radius:10px;">
            <?php wp_nonce_field('tureserva_save_supabase_settings'); ?>
            <input type="hidden" name="action" value="tureserva_save_supabase_settings">

            <label><strong>URL de Supabase</strong></label>
            <input type="url" name="tureserva_supabase_url" value="<?php echo esc_attr($supabase_url); ?>" placeholder="https://TU_SUPABASE_URL.supabase.co/rest/v1" style="width:100%;margin-bottom:10px;">

            <label><strong>API Key</strong></label>
            <input type="password" name="tureserva_supabase_api_key" value="<?php echo esc_attr($supabase_key); ?>" placeholder="••••••••••••••" style="width:100%;margin-bottom:10px;">

            <p><strong>Última sincronización:</strong> <?php echo esc_html($last_sync); ?></p>

            <button type="submit" class="button button-primary">Guardar configuración</button>
            <a href="<?php echo admin_url('admin-post.php?action=tureserva_test_supabase_connection'); ?>" class="button">Probar conexión</a>
            <button type="button" id="tureserva-sync-cloud" class="button button-secondary">Sincronizar alojamientos</button>
        </form>

        <div style="margin-top:30px;width:100%;max-width:400px;background:#eee;border-radius:6px;height:10px;">
            <div id="tureserva-sync-progress" style="width:0%;height:10px;background:#2271b1;transition:width .3s;"></div>
        </div>
        <p id="tureserva-sync-status" style="margin-top:10px;font-weight:500;color:#444;"></p>

        <div id="tureserva-sync-log" style="margin-top:25px;padding:15px 20px;background:#f9f9f9;border:1px solid #ddd;border-radius:6px;max-height:250px;overflow-y:auto;font-size:13px;">
            <p style="margin:0;font-weight:600;">Registros de sincronización:</p>
            <ul id="tureserva-log-list" style="margin-top:10px;list-style:none;padding:0;"></ul>
        </div>
    </div>
    <?php
    wp_enqueue_script('tureserva-cloud-sync', TURESERVA_URL . 'assets/js/cloud-sync.js', ['jquery'], TURESERVA_VERSION, true);
    wp_localize_script('tureserva-cloud-sync', 'tureserva_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_cloud_sync_nonce'),
    ]);
}
