<?php
/**
 * ==========================================================
 * ADMIN PAGE: TuReserva Cloud — Supabase (versión consolidada)
 * ==========================================================
 * - Configuración Supabase (URL y API Key)
 * - Dashboard con pagos sincronizados en tiempo real
 * - Integración con core-sync.php (para conexión y sincronización)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧭 Registrar submenú (slug correcto y permisos)
// =======================================================
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=reserva',              // 🔹 Menú padre
        __('Cloud Sync (Supabase)', 'tureserva'),  // Título de la página
        __('Cloud Sync (Supabase)', 'tureserva'),  // Título del menú
        'manage_options',                          // 🔐 Permiso
        'tureserva-cloud-sync',                    // ✅ Slug definitivo
        'tureserva_render_supabase_dashboard_page' // Callback
    );
});

// =======================================================
// ⚙️ Renderizado principal — Tabs Dashboard / Configuración
// =======================================================
function tureserva_render_supabase_dashboard_page() {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    ?>
    <div class="wrap">
       <h2 class="nav-tab-wrapper">
    <a href="?page=tureserva-cloud-sync&tab=dashboard"
       class="nav-tab <?php echo $tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">📊 Dashboard</a>

    <a href="?page=tureserva-cloud-sync&tab=settings"
       class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">⚙️ Configuración</a>
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
    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_key');
    ?>
    <form id="tureserva-supabase-form" method="post" style="margin-top:20px;">
        <table class="form-table">
            <tr>
                <th><label for="tureserva_supabase_url">Supabase URL</label></th>
                <td>
                    <input type="text" id="tureserva_supabase_url" name="tureserva_supabase_url"
                        value="<?php echo esc_attr($url); ?>"
                        class="regular-text"
                        placeholder="https://xyzcompany.supabase.co/rest/v1"
                        required>
                </td>
            </tr>
            <tr>
                <th><label for="tureserva_supabase_key">Supabase API Key</label></th>
                <td>
                    <input type="password" id="tureserva_supabase_key" name="tureserva_supabase_key"
                        value="<?php echo esc_attr($key); ?>"
                        class="regular-text"
                        required>
                </td>
            </tr>
        </table>

        <p>
            <button type="button" id="tureserva-guardar-supabase" class="button button-primary">
                💾 Guardar configuración
            </button>
            <button type="button" id="tureserva-probar-conexion" class="button button-secondary">
                🧪 Probar conexión
            </button>
            <button type="button" id="tureserva-sync-alojamientos" class="button">
                🔁 Sincronizar alojamientos
            </button>
        </p>

        <div id="tureserva-supabase-status" style="margin-top:10px;color:#555;font-weight:bold;"></div>
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
                                    <td><span style="color:${p.estado === 'pagado' ? 'green' : '#999'};">${p.estado}</span></td>
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
                }).fail(function(){
                    tableBody.html('<tr><td colspan="6" style="color:red;text-align:center;">Error de comunicación con el servidor.</td></tr>');
                    statusBox.text('⚠️ Error de conexión');
                });
            }

            refreshButton.on('click', loadData);
            loadData();
            setInterval(loadData, 15000);
        })(jQuery);
        </script>
    </div>
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

// =======================================================
// 🧩 AJAX: Probar conexión (usa core-sync.php)
// =======================================================
add_action('wp_ajax_tureserva_test_supabase_connection', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos suficientes.');
    }

    if (!function_exists('tureserva_sync_test_connection')) {
        wp_send_json_error('La función de prueba de conexión no está disponible.');
    }

    $resultado = tureserva_sync_test_connection();
    if (strpos($resultado, '✅') !== false) {
        wp_send_json_success($resultado);
    } else {
        wp_send_json_error($resultado);
    }
});

// =======================================================
// 🔁 AJAX: Sincronizar alojamientos (usa core-sync.php)
// =======================================================
add_action('wp_ajax_tureserva_sync_alojamientos', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos suficientes.');
    }

    if (!function_exists('tureserva_sync_alojamientos')) {
        wp_send_json_error('La función de sincronización no está disponible.');
    }

    $ok = tureserva_sync_alojamientos();
    if ($ok) {
        wp_send_json_success('✅ Alojamiento sincronizado correctamente con Supabase.');
    } else {
        wp_send_json_error('❌ Error al conectar con Supabase.');
    }
});

// =======================================================
// 💾 AJAX: Guardar configuración Supabase
// =======================================================
add_action('wp_ajax_tureserva_save_supabase_settings', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos suficientes.');
    }

    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';

    if (empty($url) || empty($key)) {
        wp_send_json_error('Campos incompletos.');
    }

    update_option('tureserva_supabase_url', $url);
    update_option('tureserva_supabase_key', $key);

    wp_send_json_success('Configuración guardada correctamente.');
});

// =======================================================
// 📜 Encolar JS solo en el panel Supabase
// =======================================================
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'tureserva_page_tureserva-cloud-sync') !== false) {
        wp_enqueue_script(
            'tureserva-panel-supabase',
            TURESERVA_URL . 'admin/assets/js/panel-supabase.js',
            ['jquery'],
            TURESERVA_VERSION,
            true
        );
    }
});
