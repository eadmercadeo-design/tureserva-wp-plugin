<?php
// Custom Post Type: Alojamiento
if (!defined('ABSPATH')) exit;

function tureserva_register_alojamiento_cpt() {
    $labels = array(
        'name' => 'Alojamientos',
        'singular_name' => 'Alojamiento',
        'menu_name' => 'Alojamiento',
        'add_new' => 'Agregar nuevo',
        'add_new_item' => 'Agregar nuevo alojamiento',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
        'show_in_rest' => true,
    );

    register_post_type('alojamiento', $args);
}
add_action('init', 'tureserva_register_alojamiento_cpt');
