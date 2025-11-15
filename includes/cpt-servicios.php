<?php
/**
 * ==========================================================
 * CPT: Servicios — TuReserva (versión corregida y estandarizada)
 * ==========================================================
 * - Se muestra dentro del menú de Alojamientos
 * - Nombre del CPT corregido: tureserva_servicio
 * - Labels completos para mejor UX
 * - Gutenberg desactivado para mayor compatibilidad
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔧 REGISTRO DEL CPT SERVICIOS
// ==========================================================
function tureserva_register_servicio_cpt()
{
    // Etiquetas completas para WordPress Admin
    $labels = array(
        'name'               => __('Servicios', 'tureserva'),
        'singular_name'      => __('Servicio', 'tureserva'),
        'menu_name'          => __('Servicios', 'tureserva'),
        'add_new'            => __('Añadir nuevo', 'tureserva'),
        'add_new_item'       => __('Añadir nuevo servicio', 'tureserva'),
        'edit_item'          => __('Editar servicio', 'tureserva'),
        'new_item'           => __('Nuevo servicio', 'tureserva'),
        'view_item'          => __('Ver servicio', 'tureserva'),
        'search_items'       => __('Buscar servicios', 'tureserva'),
        'not_found'          => __('No se encontraron servicios', 'tureserva'),
        'not_found_in_trash' => __('No hay servicios en la papelera', 'tureserva'),
        'all_items'          => __('Todos los servicios', 'tureserva'),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => false,
        'show_ui'           => true,

        /**
         * 👇 Esto hace que Servicios aparezca dentro de Alojamientos:
         * Alojamientos →
         *      - Todos los alojamientos
         *      - Agregar nuevo
         *      - Servicios      👈 AHORA AQUÍ
         *      - Tarifas
         *      - Atributos
         */
        'show_in_menu'      => 'edit.php?post_type=tureserva_alojamiento',

        'menu_icon'         => 'dashicons-hammer',
        'supports'          => array('title', 'editor', 'thumbnail'),

        // Gutenberg desactivado para compatibilidad total
        'show_in_rest'      => false,

        'publicly_queryable'=> false,
        'has_archive'       => false,
        'rewrite'           => false
    );

    // CPT corregido y estandarizado
    register_post_type('tureserva_servicio', $args);
}
add_action('init', 'tureserva_register_servicio_cpt');
