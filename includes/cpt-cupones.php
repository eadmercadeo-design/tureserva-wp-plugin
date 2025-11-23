<?php
/**
 * ==========================================================
 * CPT: Cupones — TuReserva
 * ==========================================================
 * Registra el tipo de contenido personalizado para los cupones
 * de descuento.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_register_cpt_cupones() {

    $labels = array(
        'name'               => __('Cupones', 'tureserva'),
        'singular_name'      => __('Cupón', 'tureserva'),
        'menu_name'          => __('Cupones', 'tureserva'),
        'add_new'            => __('Agregar Nuevo', 'tureserva'),
        'add_new_item'       => __('Agregar Nuevo Cupón', 'tureserva'),
        'edit_item'          => __('Editar Cupón', 'tureserva'),
        'new_item'           => __('Nuevo Cupón', 'tureserva'),
        'view_item'          => __('Ver Cupón', 'tureserva'),
        'search_items'       => __('Buscar Cupones', 'tureserva'),
        'not_found'          => __('No se encontraron cupones', 'tureserva'),
        'not_found_in_trash' => __('No hay cupones en la papelera', 'tureserva'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,  // No accesible públicamente en frontend
        'show_ui'             => true,   // Visible en admin
        'show_in_menu'        => false,  // Se agregará manualmente al menú Reservas
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'), // Solo título, el resto son metaboxes
        'has_archive'         => false,
        'rewrite'             => false,
        'show_in_rest'        => false, // Desactivar Gutenberg para usar metaboxes clásicos
    );

    register_post_type('tureserva_cupon', $args);
}
add_action('init', 'tureserva_register_cpt_cupones');
