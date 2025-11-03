<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * WIDGET: Estado de Sincronización Cloud — TuReserva
 * ==========================================================
 * Muestra el estado actual de la conexión con Supabase
 * y permite lanzar una sincronización manual.
 * ==========================================================
 */

function tureserva_widget_cloud_sync_render() {

    // ======================================================
    // ⚙️ CONFIGURACIÓN BÁSICA
    // ======================================================
    $ultima_sync  = get_option('tureserva_last_cloud_sync');
    $estado_cloud = get_option('tureserva_cloud_status', 'offline'); // 'online' o 'offline'

    if (!$ultima_sync) {
        $ultima_sync = current_time('mysql');
        update_option('tureserva_last_cloud_sync', $ultima_sync);
    }

    $estado_color = $estado_cloud === 'online' ? '#00b894' : '#d63031';
    $estado_texto = $estado_cloud === 'online' ? 'Conectado' : 'Desconectado';
    ?>

    <style>
    .tureserva-sync-container {
        font-family: "Inter", system-ui, sans-serif;
        font-size: 14px;
        color: #2d3436;
    }
    .tureserva-sync-status {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .tureserva-sync-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: <?php echo esc_attr($estado_color); ?>;
    }
    .tureserva-sync-button {
        display: inline-block;
        background-color: #0984e3;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
    }
    .tureserva-sync-button:hover {
        background-color: #74b9ff;
    }
    .tureserva-sync-log {
        background-color: #f1f2f6;
        border-radius: 5px;
        padding: 10px;
        font-size: 13px;
        color: #636e72;
        margin-top: 10px;
        max-height: 150px;
        overflow-y: auto;
    }
    </style>

    <div class="tureserva-sync-container">
        <div class="tureserva-sync-status">
            <div class="tureserva-sync-dot"></div>
            <strong>Estado:</strong> <?php echo esc_html($estado_texto); ?>
        </div>

        <div>
            <strong>Última sincronización:</strong><br>
            <?php echo esc_html(date_i18n('d M Y H:i', strtotime($ultima_sync))); ?>
        </div>

        <form method="post" style="margin-top:10px;">
            <?php wp_nonce_field('tureserva_sync_action', 'tureserva_sync_nonce'); ?>
            <button type="submit" name="tureserva_cloud_sync_now" class="tureserva-sync-button">
                🔄 Sincronizar ahora
            </button>
        </form>

        <?php
        if (
            isset($_POST['tureserva_cloud_sync_now'])
            && check_admin_referer('tureserva_sync_action', 'tureserva_sync_nonce')
        ) :
        ?>
            <div class="tureserva-sync-log">
                <?php tureserva_cloud_sync_now(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * ==========================================================
 * FUNCIÓN: Sincronización manual (placeholder o real)
 * ==========================================================
 */
function tureserva_cloud_sync_now() {

    echo '<strong>Ejecutando sincronización con Supabase...</strong><br>';

    // 🔧 Reemplaza con tu endpoint real
    $url = 'https://YOUR-SUPABASE-FUNCTION-URL';
    $api_key = 'YOUR_SUPABASE_API_KEY';

    $response = wp_remote_post($url, [
        'headers' => [
            'apikey' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'action' => 'sync_full',
            'site'   => get_bloginfo('name'),
            'time'   => current_time('mysql')
        ])
    ]);

    if (is_wp_error($response)) {
        echo '<span style="color:#d63031;">❌ Error de conexión: ' . esc_html($response->get_error_message()) . '</span><br>';
        update_option('tureserva_cloud_status', 'offline');
        return;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($code === 200 && isset($data['success']) && $data['success'] === true) {
        echo '✔ Sincronización exitosa: ' . esc_html($data['message'] ?? 'Datos actualizados.') . '<br>';
        update_option('tureserva_cloud_status', 'online');
    } else {
        echo '<span style="color:#e17055;">⚠ Hubo un problema en la sincronización.</span><br>';
        if (!empty($body)) echo '<pre>' . esc_html($body) . '</pre>';
        update_option('tureserva_cloud_status', 'offline');
    }

    update_option('tureserva_last_cloud_sync', current_time('mysql'));

    echo '<br><strong style="color:#00b894;">Sincronización completada.</strong>';
}
