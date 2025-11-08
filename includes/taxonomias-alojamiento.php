<?php
/**
 * Taxonomías principales del sistema TuReserva
 * - Categorías de alojamiento
 * - Tipos de alojamiento
 *
 * @package TuReserva
 * @author  Edwin Duarte
 */

if (!defined('ABSPATH')) exit;

/**
 * Registra las taxonomías personalizadas del módulo Alojamiento.
 */
function tureserva_register_taxonomias_alojamiento() {

    // ==========================================================
    // 🏷️ CATEGORÍAS DE ALOJAMIENTO
    // ==========================================================
    $labels_categoria = array(
        'name'              => __('Categorías de alojamiento', 'tureserva'),
        'singular_name'     => __('Categoría de alojamiento', 'tureserva'),
        'search_items'      => __('Buscar categorías', 'tureserva'),
        'all_items'         => __('Todas las categorías', 'tureserva'),
        'edit_item'         => __('Editar categoría', 'tureserva'),
        'update_item'       => __('Actualizar categoría', 'tureserva'),
        'add_new_item'      => __('Agregar nueva categoría', 'tureserva'),
        'new_item_name'     => __('Nuevo nombre de categoría', 'tureserva'),
        'menu_name'         => __('Categorías', 'tureserva'),
    );

    register_taxonomy('categoria_alojamiento', array('tureserva_alojamiento'), array(
        'labels'            => $labels_categoria,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'categoria-alojamiento'),
    ));


    // ==========================================================
    // 🏠 TIPOS DE ALOJAMIENTO
    // ==========================================================
    $labels_tipo = array(
        'name'              => __('Tipos de alojamiento', 'tureserva'),
        'singular_name'     => __('Tipo de alojamiento', 'tureserva'),
        'search_items'      => __('Buscar tipos de alojamiento', 'tureserva'),
        'all_items'         => __('Todos los tipos de alojamiento', 'tureserva'),
        'edit_item'         => __('Editar tipo de alojamiento', 'tureserva'),
        'update_item'       => __('Actualizar tipo de alojamiento', 'tureserva'),
        'add_new_item'      => __('Agregar nuevo tipo', 'tureserva'),
        'new_item_name'     => __('Nuevo nombre de tipo', 'tureserva'),
        'menu_name'         => __('Tipos de alojamientos', 'tureserva'),
    );

    register_taxonomy('tipo_alojamiento', array('tureserva_alojamiento'), array(
        'labels'            => $labels_tipo,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'tipo-alojamiento'),
    ));
}

add_action('init', 'tureserva_register_taxonomias_alojamiento');
