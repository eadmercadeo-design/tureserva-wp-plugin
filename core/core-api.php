<?php
/**
 * ==========================================================
 * CORE: API REST — TuReserva
 * ==========================================================
 * Expone endpoints públicos y autenticados:
 *  - /wp-json/tureserva/v1/alojamientos
 *  - /wp-json/tureserva/v1/disponibilidad
 *  - /wp-json/tureserva/v1/reservar
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔧 REGISTRO DE RUTA PRINCIPAL
// =======================================================
add_action( 'rest_api_init', 'tureserva_register_api_routes' );

function tureserva_register_api_routes() {

    $namespace = 'tureserva/v1';

    // Listar alojamientos
    register_rest_route( $namespace, '/alojamientos', array(
        'methods'  => 'GET',
        'callback' => 'tureserva_api_get_alojamientos',
        'permission_callback' => '__return_true'
    ));

    // Verificar disponibilidad
    register_rest_route( $namespace, '/disponibilidad', array(
        'methods'  => 'GET',
        'callback' => 'tureserva_api_check_disponibilidad',
        'permission_callback' => '__return_true'
    ));

    // Crear reserva
    register_rest_route( $namespace, '/reservar', array(
        'methods'  => 'POST',
        'callback' => 'tureserva_api_crear_reserva',
        'permission_callback' => '__return_true'
    ));
}

// =======================================================
// 🏨 ENDPOINT: LISTAR ALOJAMIENTOS
// =======================================================
function tureserva_api_get_alojamientos( $request ) {

    $args = array(
        'post_type'      => 'trs_alojamiento',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    );

    $posts = get_posts( $args );
    $data = array();

    foreach ( $posts as $post ) {
        $data[] = array(
            'id'          => $post->ID,
            'titulo'      => $post->post_title,
            'descripcion' => wp_strip_all_tags( $post->post_content ),
            'precio_base' => floatval( get_post_meta( $post->ID, '_tureserva_precio_base', true ) ),
            'capacidad'   => intval( get_post_meta( $post->ID, '_tureserva_capacidad', true ) ),
            'imagen'      => get_the_post_thumbnail_url( $post->ID, 'large' ),
        );
    }

    return rest_ensure_response( $data );
}

// =======================================================
// 📅 ENDPOINT: VERIFICAR DISPONIBILIDAD
// =======================================================
function tureserva_api_check_disponibilidad( $request ) {

    $alojamiento_id = intval( $request['alojamiento_id'] ?? 0 );
    $check_in  = sanitize_text_field( $request['check_in'] ?? '' );
    $check_out = sanitize_text_field( $request['check_out'] ?? '' );

    if ( ! $alojamiento_id || empty( $check_in ) || empty( $check_out ) ) {
        return new WP_Error( 'falta_parametros', 'Parámetros incompletos.', array( 'status' => 400 ) );
    }

    $disponible = tureserva_esta_disponible( $alojamiento_id, $check_in, $check_out );

    return rest_ensure_response( array(
        'alojamiento_id' => $alojamiento_id,
        'check_in'  => $check_in,
        'check_out' => $check_out,
        'disponible' => $disponible,
    ));
}

// =======================================================
// 🧾 ENDPOINT: CREAR RESERVA DESDE EL FRONTEND
// =======================================================
function tureserva_api_crear_reserva( $request ) {

    $params = $request->get_json_params();

    $data = array(
        'alojamiento_id' => intval( $params['alojamiento_id'] ?? 0 ),
        'check_in'       => sanitize_text_field( $params['check_in'] ?? '' ),
        'check_out'      => sanitize_text_field( $params['check_out'] ?? '' ),
        'huespedes'      => array(
            'adultos' => intval( $params['adultos'] ?? 1 ),
            'ninos'   => intval( $params['ninos'] ?? 0 ),
        ),
        'servicios' => is_array( $params['servicios'] ?? [] ) ? array_map( 'intval', $params['servicios'] ) : [],
        'cliente'   => array(
            'nombre'   => sanitize_text_field( $params['nombre'] ?? '' ),
            'email'    => sanitize_email( $params['email'] ?? '' ),
            'telefono' => sanitize_text_field( $params['telefono'] ?? '' ),
            'notas'    => sanitize_textarea_field( $params['notas'] ?? '' ),
        ),
        'estado'      => 'pendiente',
        'origen'      => 'api',
        'coupon_code' => sanitize_text_field( $params['coupon_code'] ?? '' ),
    );

    // Verificar disponibilidad
    $disponible = tureserva_esta_disponible( $data['alojamiento_id'], $data['check_in'], $data['check_out'] );
    if ( ! $disponible ) {
        return new WP_Error( 'no_disponible', 'El alojamiento no está disponible en las fechas indicadas.', array( 'status' => 409 ) );
    }

    // Crear reserva
    $reserva_id = tureserva_crear_reserva( $data );

    if ( is_wp_error( $reserva_id ) ) {
        return new WP_Error( 'error_crear_reserva', $reserva_id->get_error_message(), array( 'status' => 500 ) );
    }

    return rest_ensure_response( array(
        'mensaje' => '✅ Reserva creada correctamente.',
        'reserva_id' => $reserva_id,
    ));
}
