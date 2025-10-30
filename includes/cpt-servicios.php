<?php
// CPT: Servicios
function tureserva_register_servicio_cpt() {
    $labels = array(
        'name' => 'Servicios',
        'singular_name' => 'Servicio',
        'add_new' => 'Agregar nuevo servicio',
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-hammer',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    );

    register_post_type('servicio', $args);
}
add_action('init', 'tureserva_register_servicio_cpt');
