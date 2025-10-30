<?php
/**
 * ==========================================================
 * CPT: Pagos — TuReserva
 * ==========================================================
 * Cada registro representa un pago individual (manual o online).
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function tureserva_register_cpt_pagos() {
    $labels = array(
        'name'               => 'Pagos',
        'singular_name'      => 'Pago',
        'menu_name'          => 'Historia de pagos',
        'name_admin_bar'     => 'Pago',
        'add_new'            => 'Añadir nuevo pago',
        'add_new_item'       => 'Registrar nuevo pago',
        'edit_item'          => 'Editar pago',
        'new_item'           => 'Nuevo pago',
        'view_item'          => 'Ver pago',
        'search_items'       => 'Buscar pagos',
        'not_found'          => 'No se encontraron pagos',
        'not_found_in_trash' => 'No hay pagos en la papelera',
        'all_items'          => 'Todos los pagos',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=tureserva_reservas',
        'menu_icon'          => 'dashicons-tickets-alt',
        'supports'           => array( 'title', 'custom-fields' ),
        'capability_type'    => 'post',
        'rewrite'            => false,
    );

    register_post_type( 'tureserva_pagos', $args );
}
add_action( 'init', 'tureserva_register_cpt_pagos' );
// =======================================================
// 💳 COLUMNAS PERSONALIZADAS EN LISTA ADMIN
// =======================================================
add_filter( 'manage_tureserva_pagos_posts_columns', 'tureserva_pagos_columns' );
function tureserva_pagos_columns( $columns ) {
    return array(
        'cb'             => '<input type="checkbox" />',
        'title'          => 'Identidad',
        'cliente'        => 'Cliente',
        'estado'         => 'Estado',
        'cantidad'       => 'Cantidad',
        'reserva'        => 'Reserva',
        'pasarela'       => 'Pasarela',
        'transaccion'    => 'ID de transacción',
        'date'           => 'Fecha'
    );
}

add_action( 'manage_tureserva_pagos_posts_custom_column', 'tureserva_render_pagos_columns', 10, 2 );
function tureserva_render_pagos_columns( $column, $post_id ) {
    switch ( $column ) {
        case 'cliente':
            echo esc_html( get_post_meta( $post_id, '_tureserva_cliente_nombre', true ) ?: '—' );
            break;

        case 'estado':
            $estado = get_post_meta( $post_id, '_tureserva_pago_estado', true );
            $color = match ( $estado ) {
                'pagado' => 'green',
                'pendiente' => 'orange',
                'fallido' => 'red',
                default => '#777'
            };
            echo '<span style="color:' . $color . '; font-weight:600;">' . ucfirst( $estado ?: '—' ) . '</span>';
            break;

        case 'cantidad':
            echo esc_html( number_format( get_post_meta( $post_id, '_tureserva_pago_monto', true ), 2 ) . ' ' . strtoupper( get_post_meta( $post_id, '_tureserva_pago_moneda', true ) ?: 'USD' ) );
            break;

        case 'reserva':
            $reserva_id = get_post_meta( $post_id, '_tureserva_reserva_id', true );
            echo $reserva_id ? '<a href="' . get_edit_post_link( $reserva_id ) . '">#' . $reserva_id . '</a>' : '—';
            break;

        case 'pasarela':
            echo esc_html( get_post_meta( $post_id, '_tureserva_pasarela', true ) ?: '—' );
            break;

        case 'transaccion':
            echo esc_html( get_post_meta( $post_id, '_tureserva_pago_id', true ) ?: '—' );
            break;
    }
}
