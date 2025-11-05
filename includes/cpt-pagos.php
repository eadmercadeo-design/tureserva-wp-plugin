<?php
/**
 * ==========================================================
 * CPT: Pagos — TuReserva
 * ==========================================================
 * Cada registro representa un pago individual (manual o online),
 * asociado a una reserva existente.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "tureserva_pagos"
// ==========================================================
function tureserva_register_cpt_pagos() {

    $labels = array(
        'name'                  => __( 'Pagos', 'tureserva' ),
        'singular_name'         => __( 'Pago', 'tureserva' ),
        'menu_name'             => __( 'Historial de pagos', 'tureserva' ),
        'name_admin_bar'        => __( 'Pago', 'tureserva' ),
        'add_new'               => __( 'Añadir nuevo', 'tureserva' ),
        'add_new_item'          => __( 'Registrar nuevo pago', 'tureserva' ),
        'edit_item'             => __( 'Editar pago', 'tureserva' ),
        'new_item'              => __( 'Nuevo pago', 'tureserva' ),
        'view_item'             => __( 'Ver pago', 'tureserva' ),
        'search_items'          => __( 'Buscar pagos', 'tureserva' ),
        'not_found'             => __( 'No se encontraron pagos.', 'tureserva' ),
        'not_found_in_trash'    => __( 'No hay pagos en la papelera.', 'tureserva' ),
        'all_items'             => __( 'Todos los pagos', 'tureserva' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
'show_ui'            => true,
'show_in_menu'       => 'edit.php?post_type=reserva', // Se agrupa bajo Reservas
'supports'           => array( 'title', 'custom-fields' ),
'capability_type'    => 'post',
'map_meta_cap'       => true,
'hierarchical'       => false,
'has_archive'        => false,
'rewrite'            => false,
'menu_position'      => 30,
'can_export'         => true,
'delete_with_user'   => false,
    );

    register_post_type( 'tureserva_pagos', $args );
}
add_action( 'init', 'tureserva_register_cpt_pagos' );


// ==========================================================
// 💳 COLUMNAS PERSONALIZADAS EN LA LISTA ADMIN
// ==========================================================
add_filter( 'manage_tureserva_pagos_posts_columns', 'tureserva_pagos_columns' );
function tureserva_pagos_columns( $columns ) {
    return array(
        'cb'             => '<input type="checkbox" />',
        'title'          => __( 'Identidad', 'tureserva' ),
        'cliente'        => __( 'Cliente', 'tureserva' ),
        'estado'         => __( 'Estado', 'tureserva' ),
        'cantidad'       => __( 'Cantidad', 'tureserva' ),
        'reserva'        => __( 'Reserva', 'tureserva' ),
        'pasarela'       => __( 'Pasarela', 'tureserva' ),
        'transaccion'    => __( 'ID de transacción', 'tureserva' ),
        'date'           => __( 'Fecha', 'tureserva' ),
    );
}


// ==========================================================
// 🧾 RENDERIZAR VALORES DE CADA COLUMNA
// ==========================================================
add_action( 'manage_tureserva_pagos_posts_custom_column', 'tureserva_render_pagos_columns', 10, 2 );
function tureserva_render_pagos_columns( $column, $post_id ) {

    switch ( $column ) {

        case 'cliente':
            $nombre = get_post_meta( $post_id, '_tureserva_cliente_nombre', true );
            $email  = get_post_meta( $post_id, '_tureserva_cliente_email', true );
            echo $nombre
                ? esc_html( $nombre ) . ( $email ? "<br><a href='mailto:$email' style='color:#777;'>$email</a>" : '' )
                : '—';
            break;

        case 'estado':
            $estado = get_post_meta( $post_id, '_tureserva_pago_estado', true );
            $color = match ( strtolower($estado) ) {
                'completado', 'pagado' => '#22b14c',
                'pendiente' => '#f0ad4e',
                'fallido' => '#d9534f',
                default => '#777'
            };
            echo "<span style='font-weight:600; color:{$color}; text-transform:capitalize;'>{$estado}</span>";
            break;

        case 'cantidad':
            $monto  = floatval( get_post_meta( $post_id, '_tureserva_pago_monto', true ) );
            $moneda = strtoupper( get_post_meta( $post_id, '_tureserva_pago_moneda', true ) ?: 'USD' );
            echo $monto ? esc_html( number_format( $monto, 2 ) . " $moneda" ) : '—';
            break;

        case 'reserva':
            $reserva_id = get_post_meta( $post_id, '_tureserva_reserva_id', true );
            if ( $reserva_id && get_post_status( $reserva_id ) ) {
                echo '<a href="' . esc_url( get_edit_post_link( $reserva_id ) ) . '">#' . intval( $reserva_id ) . '</a>';
            } else {
                echo '—';
            }
            break;

        case 'pasarela':
            echo esc_html( get_post_meta( $post_id, '_tureserva_pasarela', true ) ?: 'Manual' );
            break;

        case 'transaccion':
            echo esc_html( get_post_meta( $post_id, '_tureserva_pago_id', true ) ?: '—' );
            break;
    }
}


// ==========================================================
// 🔍 FILTRO POR ESTADO EN LA LISTA ADMIN
// ==========================================================
add_action( 'restrict_manage_posts', 'tureserva_filtro_estado_pagos' );
function tureserva_filtro_estado_pagos() {
    global $typenow;
    if ( $typenow !== 'tureserva_pagos' ) return;

    $estado_actual = $_GET['estado_pago'] ?? '';
    $estados = array(
        ''            => __( 'Todos los estados', 'tureserva' ),
        'completado'  => __( 'Completado', 'tureserva' ),
        'pendiente'   => __( 'Pendiente', 'tureserva' ),
        'fallido'     => __( 'Fallido', 'tureserva' ),
    );

    echo '<select name="estado_pago">';
    foreach ( $estados as $valor => $etiqueta ) {
        printf( '<option value="%s"%s>%s</option>', esc_attr( $valor ), selected( $estado_actual, $valor, false ), esc_html( $etiqueta ) );
    }
    echo '</select>';
}

add_filter( 'parse_query', 'tureserva_filtrar_estado_pagos_query' );
function tureserva_filtrar_estado_pagos_query( $query ) {
    global $pagenow, $typenow;

    if ( $typenow === 'tureserva_pagos' && $pagenow === 'edit.php' && !empty( $_GET['estado_pago'] ) ) {
        $query->query_vars['meta_key']   = '_tureserva_pago_estado';
        $query->query_vars['meta_value'] = sanitize_text_field( $_GET['estado_pago'] );
    }
}

