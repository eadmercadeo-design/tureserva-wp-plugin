<?php
/**
 * ==========================================================
 * CPT: Impuestos y Cargos — TuReserva
 * ==========================================================
 * Registra el tipo de contenido para gestionar:
 * - Cargos adicionales (limpieza, etc.)
 * - Impuestos de alojamiento (IVA, etc.)
 * - Impuestos de servicios
 * - Impuestos exentos
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_register_cpt_impuestos() {

    $labels = array(
        'name'               => __('Impuestos y Cargos', 'tureserva'),
        'singular_name'      => __('Impuesto/Cargo', 'tureserva'),
        'menu_name'          => __('Impuestos', 'tureserva'),
        'add_new'            => __('Agregar Nuevo', 'tureserva'),
        'add_new_item'       => __('Agregar Nuevo Impuesto/Cargo', 'tureserva'),
        'edit_item'          => __('Editar Impuesto/Cargo', 'tureserva'),
        'new_item'           => __('Nuevo Impuesto/Cargo', 'tureserva'),
        'view_item'          => __('Ver Impuesto/Cargo', 'tureserva'),
        'search_items'       => __('Buscar', 'tureserva'),
        'not_found'          => __('No se encontraron registros', 'tureserva'),
        'not_found_in_trash' => __('No hay registros en la papelera', 'tureserva'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => false, // Se usa página custom
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'), // El resto son metaboxes
        'has_archive'         => false,
        'rewrite'             => false,
        'show_in_rest'        => false,
    );

    register_post_type('tureserva_impuesto', $args);
}
add_action('init', 'tureserva_register_cpt_impuestos');
