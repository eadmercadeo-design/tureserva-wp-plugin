<?php
/**
 * ==========================================================
 * CORE: Sistema de Disponibilidad — TuReserva
 * ==========================================================
 * Controla la disponibilidad de alojamientos mediante:
 * - Reservas activas (pendientes o confirmadas)
 * - Bloqueos manuales del administrador
 * - Validación de rangos de fechas y solapamientos
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔹 FUNCIÓN PRINCIPAL — VERIFICAR DISPONIBILIDAD
// =======================================================
function tureserva_esta_disponible( $alojamiento_id, $check_in, $check_out ) {

    $inicio = strtotime( $check_in );
    $fin    = strtotime( $check_out );

    if ( ! $inicio || ! $fin || $inicio >= $fin ) {
        return false; // Fechas inválidas
    }

    // ===============================
    // 🔸 1. Revisar reservas existentes
    // ===============================
    $args = array(
        'post_type'      => 'tureserva_reservas',
        'post_status'    => array( 'publish', 'pending', 'confirmed' ),
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => '_tureserva_alojamiento_id',
                'value'   => $alojamiento_id,
                'compare' => '='
            ),
            array(
                'key'     => '_tureserva_estado',
                'value'   => array( 'confirmada', 'pendiente' ),
                'compare' => 'IN'
            ),
        ),
    );

    $reservas = get_posts( $args );

    foreach ( $reservas as $reserva ) {

        $entrada = strtotime( get_post_meta( $reserva->ID, '_tureserva_checkin', true ) );
        $salida  = strtotime( get_post_meta( $reserva->ID, '_tureserva_checkout', true ) );

        // Validar solapamiento
        if ( $entrada < $fin && $salida > $inicio ) {
            return false; // Ya reservado
        }
    }

    // ===============================
    // 🔸 2. Revisar bloqueos manuales
    // ===============================
    $bloqueos = get_post_meta( $alojamiento_id, '_tureserva_bloqueos', true );
    if ( ! empty( $bloqueos ) && is_array( $bloqueos ) ) {
        foreach ( $bloqueos as $bloqueo ) {
            $bloqueo_inicio = strtotime( $bloqueo['inicio'] ?? '' );
            $bloqueo_fin    = strtotime( $bloqueo['fin'] ?? '' );

            if ( $bloqueo_inicio && $bloqueo_fin && $inicio < $bloqueo_fin && $fin > $bloqueo_inicio ) {
                return false; // Fechas bloqueadas
            }
        }
    }

    return true; // Disponible
}

// =======================================================
// 🔍 FUNCIÓN — OBTENER LISTA DE ALOJAMIENTOS DISPONIBLES
// =======================================================
function tureserva_buscar_alojamientos_disponibles( $check_in, $check_out, $args_extra = array() ) {

    $defaults = array(
        'post_type'      => 'tureserva_alojamiento',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    );

    $args = wp_parse_args( $args_extra, $defaults );
    $alojamientos = get_posts( $args );

    $disponibles = array();

    foreach ( $alojamientos as $alojamiento ) {
        if ( tureserva_esta_disponible( $alojamiento->ID, $check_in, $check_out ) ) {
            $disponibles[] = $alojamiento;
        }
    }

    return $disponibles;
}

// =======================================================
// 🧱 FUNCIÓN — AGREGAR BLOQUEO MANUAL (ADMIN)
// =======================================================
function tureserva_agregar_bloqueo( $alojamiento_id, $inicio, $fin, $motivo = 'Bloqueo manual' ) {

    $bloqueos = get_post_meta( $alojamiento_id, '_tureserva_bloqueos', true );
    if ( ! is_array( $bloqueos ) ) $bloqueos = array();

    $bloqueos[] = array(
        'inicio' => $inicio,
        'fin'    => $fin,
        'motivo' => sanitize_text_field( $motivo ),
    );

    update_post_meta( $alojamiento_id, '_tureserva_bloqueos', $bloqueos );
    return true;
}

// =======================================================
// ❌ FUNCIÓN — ELIMINAR BLOQUEO MANUAL
// =======================================================
function tureserva_eliminar_bloqueo( $alojamiento_id, $index ) {
    $bloqueos = get_post_meta( $alojamiento_id, '_tureserva_bloqueos', true );
    if ( isset( $bloqueos[$index] ) ) {
        unset( $bloqueos[$index] );
        update_post_meta( $alojamiento_id, '_tureserva_bloqueos', array_values( $bloqueos ) );
        return true;
    }
    return false;
}

// =======================================================
// 🧠 DEPURACIÓN LOCAL (solo desarrollo)
// =======================================================
// add_action( 'init', function() {
//     if ( isset($_GET['debug_disponibilidad']) ) {
//         $check_in  = '2025-12-20';
//         $check_out = '2025-12-25';
//         $aloj_id   = 123; // ID ejemplo

//         $disponible = tureserva_esta_disponible( $aloj_id, $check_in, $check_out );
//         echo '<h2>Alojamiento #' . $aloj_id . '</h2>';
//         echo $disponible ? '<p style="color:green;">Disponible ✅</p>' : '<p style="color:red;">No disponible ❌</p>';

//         $lista = tureserva_buscar_alojamientos_disponibles( $check_in, $check_out );
//         echo '<pre>'; print_r( wp_list_pluck( $lista, 'post_title' ) ); echo '</pre>';
//         exit;
//     }
// });
