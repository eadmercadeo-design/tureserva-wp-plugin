<?php
/**
 * ==========================================================
 * CORE: Sincronización con Supabase — TuReserva (Versión avanzada)
 * ==========================================================
 * Envía datos de alojamientos y reservas a Supabase.
 * Permite mantener un backup cloud, análisis externo y
 * sincronización de múltiples sitios en tiempo real.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ CONFIGURACIÓN Y OPCIONES POR DEFECTO
// =======================================================
add_action( 'tureserva_activated', 'tureserva_sync_default_options' );
function tureserva_sync_default_options() {
    if ( ! get_option( 'tureserva_supabase_url' ) ) {
        update_option( 'tureserva_supabase_url', 'https://TU_SUPABASE_URL.supabase.co/rest/v1' );
    }
    if ( ! get_option( 'tureserva_supabase_key' ) ) {
        update_option( 'tureserva_supabase_key', 'TU_API_KEY' );
    }
}

// =======================================================
// 🧩 CARGA OPCIONAL DESDE .ENV (para entornos locales)
// =======================================================
function tureserva_sync_load_env() {
    $env_file = plugin_dir_path( __DIR__ ) . '.env';
    if ( ! file_exists( $env_file ) ) return;

    $lines = file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    foreach ( $lines as $line ) {
        if ( strpos( trim( $line ), '#' ) === 0 ) continue;
        list( $key, $value ) = array_map( 'trim', explode( '=', $line, 2 ) );
        $value = trim( $value, '"' );
        $_ENV[ $key ] = $value;
    }

    // Sobrescribe las opciones si existen
    if ( ! empty( $_ENV['SUPABASE_URL'] ) ) update_option( 'tureserva_supabase_url', $_ENV['SUPABASE_URL'] );
    if ( ! empty( $_ENV['SUPABASE_ANON_KEY'] ) ) update_option( 'tureserva_supabase_key', $_ENV['SUPABASE_ANON_KEY'] );
}
add_action( 'plugins_loaded', 'tureserva_sync_load_env', 5 );

// =======================================================
// 🔄 FUNCIÓN PRINCIPAL: ENVIAR DATOS A SUPABASE
// =======================================================
function tureserva_sync_to_supabase( $tabla, $data ) {
    $url = trailingslashit( get_option( 'tureserva_supabase_url' ) ) . $tabla;
    $key = get_option( 'tureserva_supabase_key' );

    if ( empty( $url ) || empty( $key ) ) {
        error_log('[TuReserva Sync] ❌ Falta URL o clave de Supabase.');
        return false;
    }

    $response = wp_remote_post( $url, array(
        'headers' => array(
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=minimal'
        ),
        'body' => wp_json_encode( $data ),
        'timeout' => 20,
    ));

    if ( is_wp_error( $response ) ) {
        error_log('[TuReserva Sync] ❌ Error de conexión: ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( in_array( $code, array( 200, 201 ) ) ) {
        return true;
    }

    error_log('[TuReserva Sync] ⚠️ Respuesta inesperada (' . $code . '): ' . wp_remote_retrieve_body( $response ));
    return false;
}

// =======================================================
// 🧾 SINCRONIZAR NUEVAS RESERVAS AUTOMÁTICAMENTE
// =======================================================
add_action( 'tureserva_reserva_creada', 'tureserva_sync_reserva', 10, 2 );

function tureserva_sync_reserva( $reserva_id, $data ) {
    $registro = array(
        'id_reserva'     => $reserva_id,
        'alojamiento_id' => $data['alojamiento_id'] ?? '',
        'check_in'       => $data['check_in'] ?? '',
        'check_out'      => $data['check_out'] ?? '',
        'adultos'        => $data['huespedes']['adultos'] ?? 1,
        'ninos'          => $data['huespedes']['ninos'] ?? 0,
        'cliente_nombre' => $data['cliente']['nombre'] ?? '',
        'cliente_email'  => $data['cliente']['email'] ?? '',
        'estado'         => $data['estado'] ?? 'pendiente',
        'fecha_creacion' => current_time( 'mysql' ),
    );

    $ok = tureserva_sync_to_supabase( 'reservas', $registro );

    if ( $ok ) {
        error_log('[TuReserva Sync] ✅ Reserva #' . $reserva_id . ' enviada correctamente a Supabase.');
    } else {
        error_log('[TuReserva Sync] ❌ No se pudo sincronizar la reserva #' . $reserva_id);
    }
}

// =======================================================
// 🏨 SINCRONIZAR ALOJAMIENTOS (MANUAL O AUTOMÁTICO)
// =======================================================
function tureserva_sync_alojamientos() {
    $alojamientos = get_posts( array(
        'post_type'      => 'trs_alojamiento',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    ));

    $batch = array();

    foreach ( $alojamientos as $post ) {
        $batch[] = array(
            'id'           => $post->ID,
            'nombre'       => $post->post_title,
            'descripcion'  => wp_strip_all_tags( $post->post_content ),
            'precio_base'  => floatval( get_post_meta( $post->ID, '_tureserva_precio_base', true ) ),
            'capacidad'    => intval( get_post_meta( $post->ID, '_tureserva_capacidad', true ) ),
            'fecha_sync'   => current_time( 'mysql' ),
        );
    }

    return tureserva_sync_to_supabase( 'alojamientos', $batch );
}

// =======================================================
// ⚙️ AJAX: SINCRONIZACIÓN MANUAL DESDE EL ADMIN
// =======================================================
add_action( 'wp_ajax_tureserva_sync_alojamientos', 'tureserva_ajax_sync_alojamientos' );

function tureserva_ajax_sync_alojamientos() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'No autorizado' );
    }

    $ok = tureserva_sync_alojamientos();

    wp_send_json_success( array(
        'mensaje' => $ok ? '✅ Alojamiento sincronizado con Supabase.' : '❌ Error de conexión con Supabase.'
    ));
}

// =======================================================
// 🧠 PRUEBA DE CONEXIÓN (para panel de ajustes)
// =======================================================
function tureserva_sync_test_connection() {
    $url = get_option( 'tureserva_supabase_url' );
    $key = get_option( 'tureserva_supabase_key' );

    if ( empty( $url ) || empty( $key ) ) return '❌ Falta configuración.';

    $response = wp_remote_get( $url, array(
        'headers' => array(
            'apikey' => $key,
        ),
        'timeout' => 10,
    ));

    if ( is_wp_error( $response ) ) {
        return '❌ Error de conexión: ' . $response->get_error_message();
    }

    $code = wp_remote_retrieve_response_code( $response );
    return ( $code === 200 ) ? '✅ Conexión exitosa con Supabase' : '⚠️ Respuesta inesperada (' . $code . ')';
}

// =======================================================
// 💳 SINCRONIZAR PAGOS — OPCIONAL (para versiones futuras)
// =======================================================
function tureserva_sync_pagos() {
    $pagos = get_posts([
        'post_type' => 'tureserva_pagos',
        'post_status' => 'publish',
        'posts_per_page' => 10
    ]);

    $data = [];

    foreach ($pagos as $pago) {
        $data[] = [
            'codigo'     => get_post_meta($pago->ID, '_tureserva_pago_codigo', true),
            'cliente'    => get_post_meta($pago->ID, '_tureserva_cliente_nombre', true),
            'monto'      => get_post_meta($pago->ID, '_tureserva_pago_monto', true),
            'moneda'     => get_post_meta($pago->ID, '_tureserva_pago_moneda', true),
            'estado'     => get_post_meta($pago->ID, '_tureserva_pago_estado', true),
            'fecha'      => get_the_date('Y-m-d H:i:s', $pago->ID)
        ];
    }

    return tureserva_sync_to_supabase('tureserva_pagos', $data);
}