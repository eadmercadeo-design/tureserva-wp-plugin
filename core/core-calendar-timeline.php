<?php
add_action('wp_ajax_tureserva_get_resources', 'tureserva_get_resources');
function tureserva_get_resources() {
    if (!current_user_can('manage_options')) wp_send_json_error('Acceso denegado');

    $alojamientos = get_posts([
        'post_type'      => 'tureserva_alojamiento',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);

    $resources = [];
    foreach ($alojamientos as $aloj) {
        $resources[] = [
            'id' => $aloj->ID,
            'title' => $aloj->post_title
        ];
    }

    wp_send_json_success($resources);
}
