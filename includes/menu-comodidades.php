<?php
/**
 * Submenú: Comodidades (lista de alojamientos reales)
 */

if (!defined('ABSPATH')) exit;

function tureserva_add_comodidades_submenu() {
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Comodidades', 'tureserva'),
        __('Comodidades', 'tureserva'),
        'manage_options',
        'tureserva-comodidades',
        'tureserva_comodidades_page'
    );
}
add_action('admin_menu', 'tureserva_add_comodidades_submenu', 12);


// === PÁGINA ADMIN === //
function tureserva_comodidades_page() {
    echo '<div class="wrap">';
    echo '<h1>' . __('Comodidades', 'tureserva') . '</h1>';
    echo '<p>Listado de todos los alojamientos reales generados (Cabañas, Habitaciones, Suites, etc.).</p>';

    // Consulta de alojamientos físicos
    $args = [
        'post_type'      => 'tureserva_alojamiento',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC'
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>
                <th>' . __('Título', 'tureserva') . '</th>
                <th>' . __('Tipo de alojamiento', 'tureserva') . '</th>
                <th>' . __('Fecha', 'tureserva') . '</th>
              </tr></thead><tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $tipo = wp_get_post_terms(get_the_ID(), 'tipo_alojamiento');
            echo '<tr>
                    <td><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a></td>
                    <td>' . (!empty($tipo[0]->name) ? esc_html($tipo[0]->name) : '—') . '</td>
                    <td>' . get_the_date() . '</td>
                  </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No se encontraron alojamientos generados.</p>';
    }

    wp_reset_postdata();
    echo '</div>';
}
