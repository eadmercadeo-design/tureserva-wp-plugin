<?php
/**
 * ==========================================================
 * CORE: Sincronización con Supabase — TuReserva
 * ==========================================================
 * Envía datos de alojamientos y reservas a Supabase.
 * Permite mantener un backup cloud y análisis externo.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ OPCIONES Y CONFIGURACIÓN BÁSICA
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
            'Content-Type'  => 'application/json'
        ),
        'body' => wp_json_encode( $data ),
        'timeout' => 15,
    ));

    if ( is_wp_error( $response ) ) {
        error_log('[TuReserva Sync] Error de conexión: ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code( $response );
    return in_array( $code, array( 200, 201 ) );
}

// =======================================================
// 🧾 SINCRONIZAR NUEVAS RESERVAS
// =======================================================
add_action( 'tureserva_reserva_creada', 'tureserva_sync_reserva', 10, 2 );

function tureserva_sync_reserva( $reserva_id, $data ) {

    $registro = array(
        'id_reserva'    => $reserva_id,
        'alojamiento_id'=> $data['alojamiento_id'],
        'check_in'      => $data['check_in'],
        'check_out'     => $data['check_out'],
        'adultos'       => $data['huespedes']['adultos'] ?? 1,
        'ninos'         => $data['huespedes']['ninos'] ?? 0,
        'cliente_nombre'=> $data['cliente']['nombre'] ?? '',
        'cliente_email' => $data['cliente']['email'] ?? '',
        'estado'        => $data['estado'],
        'fecha_creacion'=> current_time( 'mysql' ),
    );

    $ok = tureserva_sync_to_supabase( 'reservas', $registro );

    if ( $ok ) {
        error_log('[TuReserva Sync] ✅ Reserva #' . $reserva_id . ' enviada a Supabase.');
    } else {
        error_log('[TuReserva Sync] ❌ No se pudo sincronizar la reserva #' . $reserva_id);
    }
}

// =======================================================
// 🏨 SINCRONIZAR ALOJAMIENTOS (ON DEMAND)
// =======================================================
function tureserva_sync_alojamientos() {
    $alojamientos = get_posts( array(
        'post_type'      => 'tureserva_alojamiento',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    ));

    $batch = array();

    foreach ( $alojamientos as $post ) {
        $batch[] = array(
            'id'          => $post->ID,
            'nombre'      => $post->post_title,
            'descripcion' => wp_strip_all_tags( $post->post_content ),
            'precio_base' => floatval( get_post_meta( $post->ID, '_tureserva_precio_base', true ) ),
            'capacidad'   => intval( get_post_meta( $post->ID, '_tureserva_capacidad', true ) ),
            'fecha_sync'  => current_time( 'mysql' ),
        );
    }

    $ok = tureserva_sync_to_supabase( 'alojamientos', $batch );

    return $ok;
}

// =======================================================
// ⚙️ SINCRONIZACIÓN MANUAL DESDE ADMIN (AJAX)
// =======================================================
add_action( 'wp_ajax_tureserva_sync_alojamientos', 'tureserva_ajax_sync_alojamientos' );

function tureserva_ajax_sync_alojamientos() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );
    $ok = tureserva_sync_alojamientos();
    wp_send_json_success( array(
        'mensaje' => $ok ? '✅ Alojamiento sincronizado con Supabase.' : '❌ Error de conexión.'
    ));
}
