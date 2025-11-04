<?php
/**
 * ==========================================================
 * ADMIN PAGE: TuReserva Cloud — Sincronización y Configuración
 * ==========================================================
 * Panel visual con:
 * - Configuración Supabase (URL y API key)
 * - Dashboard con pagos sincronizados
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧭 Registrar submenú
// =======================================================
add_action('admin_menu', function() {
    add_submenu_page(
    'edit.php?post_type=reserva',
    __('Cloud Sync (Supabase)', 'tureserva'),
    __('Cloud Sync (Supabase)', 'tureserva'),
    'manage_options',
    'tureserva-supabase-dashboard',
    'tureserva_render_supabase_dashboard_page'
);

}); // ✅ cierre correcto


// =======================================================
// ⚙️ Renderizado principal
// =======================================================
function tureserva_render_supabase_dashboard_page() {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    ?>
    <div class="wrap">
        <h1>☁️ TuReserva Cloud — Supabase</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=tureserva-supabase-dashboard&tab=dashboard" class="nav-tab <?php echo $tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">📊 <?php _e('Dashboard', 'tureserva'); ?></a>
            <a href="?page=tureserva-supabase-dashboard&tab=settings" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">⚙️ <?php _e('Configuración', 'tureserva'); ?></a>
        </h2>
        <?php
        if ($tab === 'settings') {
            tureserva_render_supabase_settings_tab();
        } else {
            tureserva_render_supabase_dashboard_tab();
        }
        ?>
    </div>
    <?php
}

// =======================================================
// 🧱 TAB 1: Configuración de Supabase
// =======================================================
function tureserva_render_supabase_settings_tab() {
    if (isset($_POST['tureserva_supabase_nonce']) && wp_verify_nonce($_POST['tureserva_supabase_nonce'], 'tureserva_supabase_save')) {
        update_option('tureserva_supabase_url', sanitize_text_field($_POST['tureserva_supabase_url']));
        update_option('tureserva_supabase_key', sanitize_text_field($_POST['tureserva_supabase_key']));
        echo '<div class="updated"><p><strong>✅ Configuración guardada correctamente.</strong></p></div>';
    }

    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_key');
    ?>
    <form method="post">
        <?php wp_nonce_field('tureserva_supabase_save', 'tureserva_supabase_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="tureserva_supabase_url">Supabase URL</label></th>
                <td><input type="text" name="tureserva_supabase_url" value="<?php echo esc_attr($url); ?>" class="regular-text" placeholder="https://xyzcompany.supabase.co" required></td>
            </tr>
            <tr>
                <th><label for="tureserva_supabase_key">Supabase API Key</label></th>
                <td><input type="password" name="tureserva_supabase_key" value="<?php echo esc_attr($key); ?>" class="regular-text" required></td>
            </tr>
        </table>
        <p><button type="submit" class="button button-primary">💾 Guardar configuración</button></p>
    </form>
    <?php
}

// =======================================================
// 🧠 TAB 2: Dashboard — Últimos pagos sincronizados
// =======================================================
function tureserva_render_supabase_dashboard_tab() {
    ?>
    <div style="margin-top:20px;">
        <h2>📊 Últimos Pagos Sincronizados</h2>
        <p>Los datos se cargan directamente desde tu base de datos Supabase en tiempo real.</p>

        <p>
            <button id="tureserva-refresh-supabase" class="button">🔄 Actualizar ahora</button>
            <span id="tureserva-refresh-status" style="margin-left:10px;color:#555;"></span>
        </p>

        <table class="widefat fixed striped" id="tureserva-supabase-table">
            <thead>
                <tr>
                    <th><?php _e('Código', 'tureserva'); ?></th>
                    <th><?php _e('Cliente', 'tureserva'); ?></th>
                    <th><?php _e('Monto', 'tureserva'); ?></th>
                    <th><?php _e('Moneda', 'tureserva'); ?></th>
                    <th><?php _e('Estado', 'tureserva'); ?></th>
                    <th><?php _e('Fecha', 'tureserva'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="6" style="text-align:center;">⏳ Cargando datos desde Supabase...</td></tr>
            </tbody>
        </table>
    </div>

    <script>
    (function($){
        const refreshButton = $('#tureserva-refresh-supabase');
        const statusBox = $('#tureserva-refresh-status');
        const tableBody = $('#tureserva-supabase-table tbody');

        function loadData(){
            statusBox.text('Actualizando...');
            $.post(ajaxurl, { action: 'tureserva_load_supabase_payments' }, function(response){
                if(response.success){
                    const pagos = response.data;
                    let html = '';
                    if(pagos.length){
                        pagos.forEach(p => {
                            html += `<tr>
                                <td>${p.codigo || '-'}</td>
                                <td>${p.cliente || '-'}</td>
                                <td>${p.monto || 0}</td>
                                <td>${p.moneda || ''}</td>
                                <td><span style="color:${p.estado === 'pagado' ? 'green':'#999'};">${p.estado}</span></td>
                                <td>${p.fecha || ''}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="6" style="text-align:center;">Sin registros recientes</td></tr>';
                    }
                    tableBody.html(html);
                    statusBox.text('✅ Actualizado');
                } else {
                    tableBody.html('<tr><td colspan="6" style="color:red;text-align:center;">Error: ' + response.data + '</td></tr>');
                    statusBox.text('❌ Error al cargar');
                }
            });
        }

        refreshButton.on('click', loadData);
        loadData(); // Carga inicial
        setInterval(loadData, 15000); // Auto refresco cada 15s
    })(jQuery);
    </script>
    <?php
}

// =======================================================
// 🔄 AJAX: Cargar pagos desde Supabase
// =======================================================
add_action('wp_ajax_tureserva_load_supabase_payments', function() {
    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_key');

    if (empty($url) || empty($key)) {
        wp_send_json_error('Falta configuración de Supabase.');
    }

    $endpoint = trailingslashit($url) . 'rest/v1/tureserva_pagos?order=fecha.desc&limit=10';

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code >= 200 && $code < 300 && is_array($body)) {
        wp_send_json_success($body);
    } else {
        wp_send_json_error('Error HTTP ' . $code . ' desde Supabase.');
    }
});
