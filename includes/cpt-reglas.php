<?php
/**
 * ==========================================================
 * CPT: Reglas de Reserva — TuReserva
 * ==========================================================
 * Registra el tipo de contenido para gestionar reglas como:
 * - Estancia mínima/máxima
 * - Días de entrada/salida
 * - Bloqueos
 * - Antelación
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_register_cpt_reglas() {

    $labels = array(
        'name'               => __('Reglas de Reserva', 'tureserva'),
        'singular_name'      => __('Regla', 'tureserva'),
        'menu_name'          => __('Reglas', 'tureserva'),
        'add_new'            => __('Agregar Regla', 'tureserva'),
        'add_new_item'       => __('Agregar Nueva Regla', 'tureserva'),
        'edit_item'          => __('Editar Regla', 'tureserva'),
        'new_item'           => __('Nueva Regla', 'tureserva'),
        'view_item'          => __('Ver Regla', 'tureserva'),
        'search_items'       => __('Buscar Reglas', 'tureserva'),
        'not_found'          => __('No se encontraron reglas', 'tureserva'),
        'not_found_in_trash' => __('No hay reglas en la papelera', 'tureserva'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,   // Visible para editar, pero menú oculto (se usa página custom)
        'show_in_menu'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'), // El resto son metaboxes
        'has_archive'         => false,
        'rewrite'             => false,
        'show_in_rest'        => false,
    );

    register_post_type('tureserva_regla', $args);
}
add_action('init', 'tureserva_register_cpt_reglas');
