<?php
// CPT: Atributos
function tureserva_register_atributo_cpt() {
    $labels = array(
        'name' => 'Atributos',
        'singular_name' => 'Atributo',
        'add_new' => 'Agregar nuevo atributo',
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=tureserva_alojamiento', // 👈 Esto lo mueve dentro de Alojamiento
        'menu_icon' => 'dashicons-filter',
        'supports' => array('title'),
        'show_in_rest' => true,
    );

    register_post_type('atributo', $args);
}
add_action('init', 'tureserva_register_atributo_cpt');
