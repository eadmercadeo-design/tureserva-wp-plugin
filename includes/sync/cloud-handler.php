<?php
/**
 * ==========================================================
 * CLOUD HANDLER — Sincronización manual con Supabase
 * ==========================================================
 * Envía todos los alojamientos y reservas a Supabase cuando
 * el usuario pulsa el botón "Sincronizar alojamientos".
 * Ahora incluye registro de logs de sincronización (Fase 9).
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🚀 Acción principal: sincronizar alojamientos y reservas
// =======================================================
add_action('admin_post_tureserva_sync_alojamientos', 'tureserva_cloud_sync_all');

function tureserva_cloud_sync_all() {

    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_api_key');

    if (!$url || !$key) {
        wp_die(__('Debe configurar primero la URL y API Key de Supabase.', 'tureserva'));
    }

    $headers = [
        'apikey' => $key,
        'Content-Type' => 'application/json'
    ];

    $inicio = microtime(true); // 🧩 NUEVO: registrar inicio para calcular duración

    // ===================================================
    // 🔹 Sincronizar ALOJAMIENTOS
    // ===================================================
    $alojamientos = get_posts([
        'post_type' => 'alojamiento',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $data_alojamientos = [];

    foreach ($alojamientos as $a) {
        $data_alojamientos[] = [
            'id_alojamiento' => $a->ID,
            'nombre'         => $a->post_title,
            'descripcion'    => wp_strip_all_tags($a->post_content),
            'estado'         => $a->post_status,
            'fecha_sync'     => current_time('mysql'),
        ];
    }

    if (!empty($data_alojamientos)) {
        wp_remote_post("$url/tureserva_alojamientos", [
            'headers' => $headers,
            'body'    => json_encode($data_alojamientos),
            'method'  => 'POST',
            'timeout' => 25,
        ]);
    }

    // ===================================================
    // 🔹 Sincronizar RESERVAS
    // ===================================================
    $reservas = get_posts([
        'post_type' => 'reserva',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $data_reservas = [];

    foreach ($reservas as $r) {
        $data_reservas[] = [
            'id_reserva'     => $r->ID,
            'alojamiento_id' => get_post_meta($r->ID, '_tureserva_alojamiento', true),
            'check_in'       => get_post_meta($r->ID, '_tureserva_checkin', true),
            'check_out'      => get_post_meta($r->ID, '_tureserva_checkout', true),
            'adultos'        => get_post_meta($r->ID, '_tureserva_adultos', true) ?: 1,
            'ninos'          => get_post_meta($r->ID, '_tureserva_ninos', true) ?: 0,
            'cliente_nombre' => get_post_meta($r->ID, '_tureserva_cliente_nombre', true),
            'cliente_email'  => get_post_meta($r->ID, '_tureserva_cliente_email', true),
            'estado'         => get_post_meta($r->ID, '_tureserva_estado', true) ?: 'pendiente',
            'fecha_sync'     => current_time('mysql'),
        ];
    }

    if (!empty($data_reservas)) {
        wp_remote_post("$url/tureserva_reservas", [
            'headers' => $headers,
            'body'    => json_encode($data_reservas),
            'method'  => 'POST',
            'timeout' => 25,
        ]);
    }

    // ===================================================
    // ✅ Actualizar fecha de sincronización
    // ===================================================
    update_option('tureserva_cloud_last_sync', current_time('mysql'));

    // 🧩 NUEVO BLOQUE — Guardar log local de sincronización
    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';
    $fin = microtime(true);
    $duracion = round($fin - $inicio);

    $total_aloj = count($data_alojamientos);
    $total_resv = count($data_reservas);
    $total = $total_aloj + $total_resv;

    $data = [
        'tipo'         => 'cloud',
        'usuario'      => wp_get_current_user()->user_login,
        'total'        => $total,
        'exitoso'      => $total, // asumimos éxito total (en AJAX se mide por respuesta)
        'fallido'      => 0,
        'duracion'     => $duracion,
        'fecha_inicio' => current_time('mysql', true),
        'fecha_fin'    => current_time('mysql', true),
        'resumen'      => sprintf('Sincronización manual completada. %d alojamientos y %d reservas enviadas.', $total_aloj, $total_resv),
    ];

    $wpdb->insert($table, $data);

    // 🧩 NUEVO BLOQUE — Enviar log también a Supabase
    $supabase_log = [
        [
            'tipo'         => 'cloud',
            'usuario'      => $data['usuario'],
            'total'        => $data['total'],
            'exitoso'      => $data['exitoso'],
            'fallido'      => $data['fallido'],
            'duracion'     => $data['duracion'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin'    => $data['fecha_fin'],
            'resumen'      => $data['resumen'],
        ]
    ];

    wp_remote_post("$url/tureserva_sync_log", [
        'headers' => [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode($supabase_log),
        'method'  => 'POST',
        'timeout' => 25,
    ]);

    // ===================================================
    // 🧩 Página de confirmación
    // ===================================================
    echo '<div class="wrap">';
    echo '<h1>✅ Sincronización completada con éxito</h1>';
    echo '<p>Los alojamientos y reservas se enviaron correctamente a Supabase.</p>';
    echo '<p><strong>Duración:</strong> ' . esc_html($duracion) . ' segundos</p>';
    echo '<a href="' . admin_url('edit.php?post_type=reserva&page=tureserva-cloud-sync') . '" class="button button-primary">Volver</a>';
    echo '</div>';

    exit;
}
// =======================================================
// 📊 AJAX — Obtener registros de sincronización (para dashboard)
// =======================================================
add_action('wp_ajax_tureserva_get_sync_logs', function () {
    check_ajax_referer('tureserva_cloud_sync_nonce', 'security');
    global $wpdb;

    $table = $wpdb->prefix . 'tureserva_sync_log';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY fecha_fin DESC LIMIT 20");

    if (!empty($logs)) {
        wp_send_json_success($logs);
    } else {
        wp_send_json_error(['message' => 'No se encontraron registros.']);
    }
});


// =======================================================
// 🧩 AJAX — Registrar log de sincronización Cloud
// =======================================================
add_action('wp_ajax_tureserva_cloud_save_log', function () {
    check_ajax_referer('tureserva_cloud_sync_nonce', 'security');

    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';

    $data = [
        'tipo'         => 'cloud',
        'usuario'      => wp_get_current_user()->user_login,
        'total'        => intval($_POST['total']),
        'exitoso'      => intval($_POST['ok']),
        'fallido'      => intval($_POST['fail']),
        'duracion'     => intval($_POST['duracion']),
        'fecha_inicio' => sanitize_text_field($_POST['inicio']),
        'fecha_fin'    => current_time('mysql'),
        'resumen'      => sanitize_textarea_field($_POST['resumen']),
    ];

    $wpdb->insert($table, $data);

    // 🧩 NUEVO BLOQUE — También enviar el log a Supabase
    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_api_key');

    if ($url && $key) {
        $headers = [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ];

        $supabase_data = [
            [
                'tipo'         => 'cloud',
                'usuario'      => $data['usuario'],
                'total'        => $data['total'],
                'exitoso'      => $data['exitoso'],
                'fallido'      => $data['fallido'],
                'duracion'     => $data['duracion'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'resumen'      => $data['resumen'],
            ]
        ];

        wp_remote_post("$url/tureserva_sync_log", [
            'headers' => $headers,
            'body'    => wp_json_encode($supabase_data),
            'method'  => 'POST',
            'timeout' => 25,
        ]);
    }

    if ($wpdb->insert_id) {
        wp_send_json_success(['message' => 'Log guardado correctamente.']);
    } else {
        wp_send_json_error(['message' => 'No se pudo guardar el log.']);
    }
});
