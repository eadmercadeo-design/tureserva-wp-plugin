<?php
/**
 * ==========================================================
 * CPT: Alojamiento — TuReserva
 * ==========================================================
 * Este archivo registra el tipo de contenido personalizado
 * para gestionar los alojamientos dentro del sistema.
 *
 * - Visible en el panel administrativo de WordPress.
 * - Compatible con el editor clásico y Gutenberg.
 * - Incluye miniatura, descripción y título.
 * - Posicionado antes del menú de Reservas.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit; // 🚫 Seguridad

// =======================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "Alojamiento"
// =======================================================
function tureserva_register_alojamiento_cpt() {

    $labels = array(
        'name'                  => __( 'Alojamientos', 'tureserva' ),
        'singular_name'         => __( 'Alojamiento', 'tureserva' ),
        'menu_name'             => __( 'Alojamiento', 'tureserva' ),
        'name_admin_bar'        => __( 'Alojamiento', 'tureserva' ),
        'add_new'               => __( 'Agregar nuevo', 'tureserva' ),
        'add_new_item'          => __( 'Agregar nuevo alojamiento', 'tureserva' ),
        'edit_item'             => __( 'Editar alojamiento', 'tureserva' ),
        'new_item'              => __( 'Nuevo alojamiento', 'tureserva' ),
        'view_item'             => __( 'Ver alojamiento', 'tureserva' ),
        'search_items'          => __( 'Buscar alojamientos', 'tureserva' ),
        'not_found'             => __( 'No se encontraron alojamientos', 'tureserva' ),
        'not_found_in_trash'    => __( 'No hay alojamientos en la papelera', 'tureserva' ),
        'all_items'             => __( 'Todos los alojamientos', 'tureserva' ),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'menu_icon'             => 'dashicons-building',
        'menu_position'         => 5, // 📌 Antes del menú "Reservas"
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'has_archive'           => true,
        'show_in_rest'          => true, // Compatible con el editor de bloques
        'rewrite'               => array( 'slug' => 'alojamientos' ),
        'capability_type'       => 'post',
        'publicly_queryable'    => true,
    );

    register_post_type( 'alojamiento', $args );
}
add_action( 'init', 'tureserva_register_alojamiento_cpt' );
