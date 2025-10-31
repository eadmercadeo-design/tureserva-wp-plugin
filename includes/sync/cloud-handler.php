<?php
/**
 * ==========================================================
 * CLOUD HANDLER — Sincronización manual con Supabase
 * ==========================================================
 * Envía todos los alojamientos y reservas a Supabase cuando
 * el usuario pulsa el botón "Sincronizar alojamientos".
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

    echo '<div class="wrap">';
    echo '<h1>✅ Sincronización completada con éxito</h1>';
    echo '<p>Los alojamientos y reservas se enviaron correctamente a Supabase.</p>';
    echo '<a href="' . admin_url('edit.php?post_type=reserva&page=tureserva-cloud-sync') . '" class="button button-primary">Volver</a>';
    echo '</div>';

    exit;
}
