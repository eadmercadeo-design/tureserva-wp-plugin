<?php
/**
 * ==========================================================
 * CORE: Reportes — TuReserva
 * ==========================================================
 * Calcula estadísticas de:
 *  - Ocupación
 *  - Ingresos
 *  - Estado de reservas
 * Filtrable por rango de fechas y alojamiento
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📊 FUNCIÓN PRINCIPAL: Generar resumen de reportes
// =======================================================
function tureserva_generar_reporte( $args = array() ) {

    $defaults = array(
        'fecha_inicio' => date( 'Y-m-01' ),      // inicio de mes actual
        'fecha_fin'    => date( 'Y-m-t' ),       // fin de mes actual
        'alojamiento'  => 0,                     // 0 = todos
        'estado'       => '',                    // vacío = todos
    );

    $args = wp_parse_args( $args, $defaults );

    $meta_query = array(
        array(
            'key'     => '_tureserva_checkin',
            'value'   => $args['fecha_inicio'],
            'compare' => '>=',
            'type'    => 'DATE',
        ),
        array(
            'key'     => '_tureserva_checkout',
            'value'   => $args['fecha_fin'],
            'compare' => '<=',
            'type'    => 'DATE',
        ),
    );

    if ( ! empty( $args['estado'] ) ) {
        $meta_query[] = array(
            'key'     => '_tureserva_estado',
            'value'   => $args['estado'],
            'compare' => '='
        );
    }

    if ( $args['alojamiento'] > 0 ) {
        $meta_query[] = array(
            'key'     => '_tureserva_alojamiento_id',
            'value'   => intval( $args['alojamiento'] ),
            'compare' => '='
        );
    }

    $query = new WP_Query( array(
        'post_type'      => 'tureserva_reserva',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => $meta_query
    ) );

    $total_reservas = 0;
    $ingresos_totales = 0;
    $ocupacion_dias = 0;
    $detalle_por_estado = array(
        'confirmada' => 0,
        'pendiente'  => 0,
        'cancelada'  => 0,
    );

    foreach ( $query->posts as $reserva ) {

        $estado = get_post_meta( $reserva->ID, '_tureserva_estado', true );
        $check_in  = strtotime( get_post_meta( $reserva->ID, '_tureserva_checkin', true ) );
        $check_out = strtotime( get_post_meta( $reserva->ID, '_tureserva_checkout', true ) );
        $total     = floatval( get_post_meta( $reserva->ID, '_tureserva_precio_total', true ) );

        if ( $estado === 'cancelada' ) continue; // ignorar canceladas en cálculos de ingresos

        $total_reservas++;
        $ingresos_totales += $total;
        $detalle_por_estado[$estado] = isset( $detalle_por_estado[$estado] ) ? $detalle_por_estado[$estado] + 1 : 1;

        // calcular días ocupados
        if ( $check_in && $check_out ) {
            $ocupacion_dias += max( 0, floor( ( $check_out - $check_in ) / DAY_IN_SECONDS ) );
        }
    }

    // cálculo promedio
    $promedio_reserva = $total_reservas > 0 ? $ingresos_totales / $total_reservas : 0;

    // resultado
    return array(
        'total_reservas'   => $total_reservas,
        'ingresos_totales' => round( $ingresos_totales, 2 ),
        'promedio_reserva' => round( $promedio_reserva, 2 ),
        'ocupacion_dias'   => $ocupacion_dias,
        'por_estado'       => $detalle_por_estado,
        'rango'            => array(
            'inicio' => $args['fecha_inicio'],
            'fin'    => $args['fecha_fin']
        )
    );
}

// =======================================================
// 📈 ENDPOINT AJAX PARA CONSULTAS DESDE ADMIN
// =======================================================
add_action( 'wp_ajax_tureserva_get_reporte', 'tureserva_get_reporte' );

function tureserva_get_reporte() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Sin permisos' );

    $args = array(
        'fecha_inicio' => sanitize_text_field( $_GET['inicio'] ?? date( 'Y-m-01' ) ),
        'fecha_fin'    => sanitize_text_field( $_GET['fin'] ?? date( 'Y-m-t' ) ),
        'alojamiento'  => intval( $_GET['alojamiento'] ?? 0 ),
        'estado'       => sanitize_text_field( $_GET['estado'] ?? '' ),
    );

    $reporte = tureserva_generar_reporte( $args );

    wp_send_json_success( $reporte );
}
