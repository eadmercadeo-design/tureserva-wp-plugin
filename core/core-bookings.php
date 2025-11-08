<?php
/**
 * ==========================================================
 * CORE: Sistema de Reservas — TuReserva
 * ==========================================================
 * Gestiona la creación, validación y almacenamiento de reservas.
 * Interactúa directamente con los módulos:
 *  - core-pricing.php
 *  - core-availability.php
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔹 CREAR UNA NUEVA RESERVA
// =======================================================
function tureserva_crear_reserva( $args = array() ) {

    $defaults = array(
        'alojamiento_id' => 0,
        'check_in'       => '',
        'check_out'      => '',
        'huespedes'      => array( 'adultos' => 2, 'ninos' => 0 ),
        'servicios'      => array(),
        'cliente'        => array(
            'nombre'  => '',
            'email'   => '',
            'telefono'=> '',
            'notas'   => ''
        ),
        'estado'         => 'pendiente', // pendiente | confirmada | cancelada
        'origen'         => 'manual',    // manual | web | api
    );

    $data = wp_parse_args( $args, $defaults );

    // Validaciones básicas
    if ( empty( $data['alojamiento_id'] ) || empty( $data['check_in'] ) || empty( $data['check_out'] ) ) {
        return new WP_Error( 'datos_incompletos', __( 'Faltan datos obligatorios para crear la reserva.', 'tureserva' ) );
    }

    // Verificar disponibilidad
    $disponible = tureserva_esta_disponible( $data['alojamiento_id'], $data['check_in'], $data['check_out'] );
    if ( ! $disponible ) {
        return new WP_Error( 'no_disponible', __( 'El alojamiento no está disponible en las fechas seleccionadas.', 'tureserva' ) );
    }

    // Calcular precio total
    $precio = tureserva_calcular_precio_total(
        $data['alojamiento_id'],
        $data['check_in'],
        $data['check_out'],
        $data['huespedes'],
        $data['servicios']
    );

    if ( empty( $precio['total'] ) || $precio['total'] <= 0 ) {
        return new WP_Error( 'precio_invalido', __( 'No se pudo calcular el precio de la reserva.', 'tureserva' ) );
    }

    // Crear post de tipo reserva
    $reserva_id = wp_insert_post( array(
        'post_title'   => 'Reserva de ' . $data['cliente']['nombre'] . ' (' . date_i18n( 'd/m/Y', strtotime( $data['check_in'] ) ) . ')',
        'post_type'    => 'tureserva_reservas',
        'post_status'  => 'publish',
        'post_content' => sanitize_textarea_field( $data['cliente']['notas'] ),
    ) );

    if ( is_wp_error( $reserva_id ) ) {
        return $reserva_id;
    }

    // Guardar metadatos
    update_post_meta( $reserva_id, '_tureserva_alojamiento_id', intval( $data['alojamiento_id'] ) );
    update_post_meta( $reserva_id, '_tureserva_checkin', sanitize_text_field( $data['check_in'] ) );
    update_post_meta( $reserva_id, '_tureserva_checkout', sanitize_text_field( $data['check_out'] ) );
    update_post_meta( $reserva_id, '_tureserva_adultos', intval( $data['huespedes']['adultos'] ) );
    update_post_meta( $reserva_id, '_tureserva_ninos', intval( $data['huespedes']['ninos'] ) );
    update_post_meta( $reserva_id, '_tureserva_servicios', $data['servicios'] );
    update_post_meta( $reserva_id, '_tureserva_cliente_nombre', sanitize_text_field( $data['cliente']['nombre'] ) );
    update_post_meta( $reserva_id, '_tureserva_cliente_email', sanitize_email( $data['cliente']['email'] ) );
    update_post_meta( $reserva_id, '_tureserva_cliente_telefono', sanitize_text_field( $data['cliente']['telefono'] ) );
    update_post_meta( $reserva_id, '_tureserva_estado', sanitize_text_field( $data['estado'] ) );
    update_post_meta( $reserva_id, '_tureserva_origen', sanitize_text_field( $data['origen'] ) );
    update_post_meta( $reserva_id, '_tureserva_precio_total', floatval( $precio['total'] ) );
    update_post_meta( $reserva_id, '_tureserva_desglose_precio', $precio );

    do_action( 'tureserva_reserva_creada', $reserva_id, $data );

    return $reserva_id;
}

// =======================================================
// 🔄 ACTUALIZAR ESTADO DE RESERVA
// =======================================================
function tureserva_actualizar_estado_reserva( $reserva_id, $nuevo_estado ) {

    $estados_validos = array( 'pendiente', 'confirmada', 'cancelada' );
    if ( ! in_array( $nuevo_estado, $estados_validos ) ) {
        return new WP_Error( 'estado_invalido', __( 'Estado de reserva no válido.', 'tureserva' ) );
    }

    update_post_meta( $reserva_id, '_tureserva_estado', $nuevo_estado );
    wp_update_post( array( 'ID' => $reserva_id, 'post_status' => 'publish' ) );

    do_action( 'tureserva_reserva_estado_actualizado', $reserva_id, $nuevo_estado );
    return true;
}

// =======================================================
// 🔍 OBTENER DETALLES COMPLETOS DE UNA RESERVA
// =======================================================
function tureserva_obtener_detalles_reserva( $reserva_id ) {

    $datos = array(
        'id'           => $reserva_id,
        'alojamiento'  => intval( get_post_meta( $reserva_id, '_tureserva_alojamiento_id', true ) ),
        'check_in'     => get_post_meta( $reserva_id, '_tureserva_checkin', true ),
        'check_out'    => get_post_meta( $reserva_id, '_tureserva_checkout', true ),
        'adultos'      => intval( get_post_meta( $reserva_id, '_tureserva_adultos', true ) ),
        'ninos'        => intval( get_post_meta( $reserva_id, '_tureserva_ninos', true ) ),
        'servicios'    => get_post_meta( $reserva_id, '_tureserva_servicios', true ),
        'estado'       => get_post_meta( $reserva_id, '_tureserva_estado', true ),
        'cliente'      => array(
            'nombre'   => get_post_meta( $reserva_id, '_tureserva_cliente_nombre', true ),
            'email'    => get_post_meta( $reserva_id, '_tureserva_cliente_email', true ),
            'telefono' => get_post_meta( $reserva_id, '_tureserva_cliente_telefono', true ),
        ),
        'precio_total' => floatval( get_post_meta( $reserva_id, '_tureserva_precio_total', true ) ),
        'detalle_precio'=> get_post_meta( $reserva_id, '_tureserva_desglose_precio', true ),
    );

    return apply_filters( 'tureserva_detalles_reserva', $datos, $reserva_id );
}

// =======================================================
// ❌ CANCELAR UNA RESERVA
// =======================================================
function tureserva_cancelar_reserva( $reserva_id ) {
    tureserva_actualizar_estado_reserva( $reserva_id, 'cancelada' );
    do_action( 'tureserva_reserva_cancelada', $reserva_id );
    return true;
}

// =======================================================
// 🧠 DEPURACIÓN LOCAL (solo desarrollo)
// =======================================================
// add_action( 'init', function() {
//     if ( isset($_GET['debug_reserva']) ) {
//         $args = array(
//             'alojamiento_id' => 123,
//             'check_in'       => '2025-12-20',
//             'check_out'      => '2025-12-25',
//             'huespedes'      => array( 'adultos' => 2, 'ninos' => 1 ),
//             'servicios'      => array( 45, 46 ),
//             'cliente'        => array(
//                 'nombre'   => 'Carlos Pérez',
//                 'email'    => 'carlos@example.com',
//                 'telefono' => '555-1234',
//                 'notas'    => 'Solicitud de habitación con vista al mar',
//             ),
//             'estado' => 'pendiente',
//             'origen' => 'web',
//         );

//         $reserva_id = tureserva_crear_reserva( $args );
//         echo '<pre>'; print_r( $reserva_id ); echo '</pre>'; exit;
//     }
// });
