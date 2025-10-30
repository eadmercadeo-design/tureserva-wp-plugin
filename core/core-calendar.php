<?php
/**
 * ==========================================================
 * CORE: Calendario de Reservas — TuReserva
 * ==========================================================
 * Provee los datos en formato JSON para el calendario anual:
 *  - Reservas confirmadas, pendientes o canceladas
 *  - Bloqueos manuales de alojamientos
 *  - Filtros por año, estado o alojamiento
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📅 ENDPOINT AJAX: Obtener eventos del calendario
// =======================================================
add_action( 'wp_ajax_tureserva_get_calendar', 'tureserva_get_calendar' );
add_action( 'wp_ajax_nopriv_tureserva_get_calendar', 'tureserva_get_calendar' );

function tureserva_get_calendar() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Acceso denegado' );

    $year         = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : date( 'Y' );
    $alojamiento  = isset( $_GET['alojamiento'] ) ? intval( $_GET['alojamiento'] ) : 0;
    $estado       = isset( $_GET['estado'] ) ? sanitize_text_field( $_GET['estado'] ) : '';

    $eventos = array();

    // ===============================
    // 🔸 1. Cargar Reservas
    // ===============================
    $args = array(
        'post_type'      => 'tureserva_reservas',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(),
    );

    if ( $alojamiento > 0 ) {
        $args['meta_query'][] = array(
            'key'   => '_tureserva_alojamiento_id',
            'value' => $alojamiento,
            'compare' => '='
        );
    }

    if ( ! empty( $estado ) ) {
        $args['meta_query'][] = array(
            'key'   => '_tureserva_estado',
            'value' => $estado,
            'compare' => '='
        );
    }

    $reservas = get_posts( $args );

    foreach ( $reservas as $reserva ) {
        $check_in  = get_post_meta( $reserva->ID, '_tureserva_check_in', true );
        $check_out = get_post_meta( $reserva->ID, '_tureserva_check_out', true );
        $estado    = get_post_meta( $reserva->ID, '_tureserva_estado', true );
        $cliente   = get_post_meta( $reserva->ID, '_tureserva_cliente_nombre', true );
        $aloj_id   = get_post_meta( $reserva->ID, '_tureserva_alojamiento_id', true );
        $aloj_tit  = get_the_title( $aloj_id );

        if ( empty( $check_in ) || empty( $check_out ) ) continue;

        $color = match ( $estado ) {
            'confirmada' => '#2ecc71',   // verde
            'pendiente'  => '#f1c40f',   // amarillo
            'cancelada'  => '#e74c3c',   // rojo
            default      => '#3498db',   // azul
        };

        $eventos[] = array(
            'id'        => $reserva->ID,
            'title'     => "🛏️ {$aloj_tit} — {$cliente}",
            'start'     => $check_in,
            'end'       => $check_out,
            'color'     => $color,
            'extendedProps' => array(
                'estado' => $estado,
                'cliente' => $cliente,
                'alojamiento' => $aloj_tit,
                'tipo' => 'reserva',
            ),
        );
    }

    // ===============================
    // 🔸 2. Cargar Bloqueos Manuales
    // ===============================
    $alojamientos = $alojamiento > 0
        ? array( get_post( $alojamiento ) )
        : get_posts( array( 'post_type' => 'tureserva_alojamiento', 'posts_per_page' => -1, 'post_status' => 'publish' ) );

    foreach ( $alojamientos as $aloj ) {
        $bloqueos = get_post_meta( $aloj->ID, '_tureserva_bloqueos', true );

        if ( empty( $bloqueos ) || ! is_array( $bloqueos ) ) continue;

        foreach ( $bloqueos as $bloqueo ) {
            $inicio = $bloqueo['inicio'] ?? '';
            $fin    = $bloqueo['fin'] ?? '';
            $motivo = $bloqueo['motivo'] ?? 'Bloqueo';

            if ( empty( $inicio ) || empty( $fin ) ) continue;

            $eventos[] = array(
                'id'        => uniqid('bloqueo_'),
                'title'     => "⛔ {$aloj->post_title} — {$motivo}",
                'start'     => $inicio,
                'end'       => $fin,
                'color'     => '#95a5a6',
                'extendedProps' => array(
                    'motivo' => $motivo,
                    'tipo' => 'bloqueo',
                    'alojamiento' => $aloj->post_title,
                ),
            );
        }
    }

    // ===============================
    // 🔸 3. Filtrar por año
    // ===============================
    $eventos = array_filter( $eventos, function( $e ) use ( $year ) {
        return str_starts_with( $e['start'], (string)$year );
    });

    wp_send_json_success( array_values( $eventos ) );
}
