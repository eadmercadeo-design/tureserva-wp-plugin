<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tureserva_find_available_rooms', 'tureserva_find_available_rooms');
function tureserva_find_available_rooms() {
    check_ajax_referer('tureserva_add_reserva_nonce', 'security');

    $check_in  = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $adults    = intval($_POST['adults']);
    $children  = intval($_POST['children']);
    $type      = intval($_POST['alojamiento_type']);

    if (empty($check_in) || empty($check_out)) {
        wp_send_json_error(__('Debe seleccionar las fechas de llegada y salida.', 'tureserva'));
    }

    // 🔍 Buscar alojamientos disponibles (ejemplo básico)
    $args = [
        'post_type' => 'alojamiento',
        'posts_per_page' => -1,
        'tax_query' => $type ? [['taxonomy' => 'categoria_alojamiento', 'terms' => $type]] : [],
    ];

    $query = new WP_Query($args);
    $resultados = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $resultados[] = [
                'id' => $post->ID,
                'nombre' => $post->post_title,
                'capacidad' => get_post_meta($post->ID, '_tureserva_capacidad', true),
                'precio' => get_post_meta($post->ID, '_tureserva_precio_noche', true),
            ];
        }
    }

    wp_send_json_success($resultados);
}
