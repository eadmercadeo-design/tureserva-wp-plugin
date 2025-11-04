<?php
/**
 * ==========================================================
 * CPT: Reservas — TuReserva (versión mejorada)
 * ==========================================================
 * - Integración directa con el menú del plugin (sin duplicados)
 * - Gutenberg desactivado (usa pantalla personalizada)
 * - Columnas personalizadas con datos clave
 * - Redirección de “Añadir nueva” hacia interfaz AJAX
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "Reserva"
// =======================================================
function tureserva_register_cpt_reservas() {

    $labels = array(
        'name'                  => __( 'Reservas', 'tureserva' ),
        'singular_name'         => __( 'Reserva', 'tureserva' ),
        'menu_name'             => __( 'Reservas', 'tureserva' ),
        'name_admin_bar'        => __( 'Reserva', 'tureserva' ),
        'add_new'               => __( 'Añadir nueva', 'tureserva' ),
        'add_new_item'          => __( 'Añadir nueva reserva', 'tureserva' ),
        'edit_item'             => __( 'Editar reserva', 'tureserva' ),
        'new_item'              => __( 'Nueva reserva', 'tureserva' ),
        'view_item'             => __( 'Ver reserva', 'tureserva' ),
        'search_items'          => __( 'Buscar reservas', 'tureserva' ),
        'not_found'             => __( 'No se encontraron reservas', 'tureserva' ),
        'not_found_in_trash'    => __( 'No hay reservas en la papelera', 'tureserva' ),
        'all_items'             => __( 'Todas las reservas', 'tureserva' ),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => false, // Se añade mediante menú personalizado
        'menu_icon'             => 'dashicons-calendar-alt',
        'supports'              => array( 'title' ), // solo título
        'has_archive'           => false,
        'show_in_rest'          => false, // 🚫 desactiva Gutenberg
        'rewrite'               => false,
        'capability_type'       => 'post',
        'publicly_queryable'    => false,
    );

    register_post_type( 'reserva', $args );
}
add_action( 'init', 'tureserva_register_cpt_reservas' );

// =======================================================
// 🚀 REDIRECCIÓN: “Añadir nueva” → Interfaz personalizada
// =======================================================
add_action('load-post-new.php', function() {
    global $typenow;
    if ($typenow === 'reserva') {
        wp_redirect(admin_url('edit.php?post_type=reserva&page=tureserva-add-reserva'));
        exit;
    }
});

// =======================================================
// 🧾 COLUMNAS PERSONALIZADAS EN EL ADMIN
// =======================================================
add_filter( 'manage_edit-reserva_columns', 'tureserva_reservas_columns' );
function tureserva_reservas_columns( $columns ) {
    return array(
        'cb'          => '<input type="checkbox" />',
        'title'       => __( 'Identidad', 'tureserva' ),
        'estado'      => __( 'Estado', 'tureserva' ),
        'fechas'      => __( 'Check-in / Check-out', 'tureserva' ),
        'invitados'   => __( 'Invitados', 'tureserva' ),
        'cliente'     => __( 'Cliente', 'tureserva' ),
        'precio'      => __( 'Precio', 'tureserva' ),
        'alojamiento' => __( 'Alojamiento', 'tureserva' ),
        'date'        => __( 'Fecha', 'tureserva' ),
    );
}

// =======================================================
// 🧮 RENDERIZADO DE COLUMNAS
// =======================================================
add_action( 'manage_reserva_posts_custom_column', 'tureserva_render_reservas_columns', 10, 2 );
function tureserva_render_reservas_columns( $column, $post_id ) {

    switch ( $column ) {

        case 'estado':
            $estado = get_post_meta( $post_id, '_tureserva_estado', true ) ?: 'pendiente';
            $color = 'orange';
            if ( $estado === 'confirmada' ) $color = 'green';
            if ( $estado === 'cancelada' )  $color = 'red';
            echo '<strong style="color:' . esc_attr( $color ) . ';">' . esc_html( ucfirst( $estado ) ) . '</strong>';
            break;

        case 'fechas':
            $checkin  = get_post_meta( $post_id, '_tureserva_checkin', true );
            $checkout = get_post_meta( $post_id, '_tureserva_checkout', true );
            echo esc_html( $checkin && $checkout ? "$checkin — $checkout" : '—' );
            break;

        case 'invitados':
            $adultos = get_post_meta( $post_id, '_tureserva_adultos', true ) ?: 0;
            $ninos   = get_post_meta( $post_id, '_tureserva_ninos', true ) ?: 0;
            echo esc_html( "Adultos: $adultos / Niños: $ninos" );
            break;

        case 'cliente':
            $nombre = get_post_meta( $post_id, '_tureserva_cliente_nombre', true );
            $email  = get_post_meta( $post_id, '_tureserva_cliente_email', true );
            echo esc_html( $nombre ? "$nombre ($email)" : '—' );
            break;

        case 'precio':
            $precio = floatval( get_post_meta( $post_id, '_tureserva_precio_total', true ) );
            echo esc_html( $precio ? '$' . number_format( $precio, 2 ) : '—' );
            break;

        case 'alojamiento':
            $alojamiento_id = get_post_meta( $post_id, '_tureserva_alojamiento_id', true );
            if ( $alojamiento_id ) {
                $title = get_the_title( $alojamiento_id );
                echo '<a href="' . esc_url( get_edit_post_link( $alojamiento_id ) ) . '">' . esc_html( $title ?: ('#' . intval( $alojamiento_id )) ) . '</a>';
            } else {
                echo '—';
            }
            break;
    }
}
