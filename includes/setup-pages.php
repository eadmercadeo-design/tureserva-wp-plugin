<?php
/**
 * ==========================================================
 * 📄 Instalador automático de páginas del sistema — TuReserva
 * ==========================================================
 * Crea todas las páginas base necesarias para el funcionamiento
 * del sistema de reservas, al estilo MotoPress Hotel Booking.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * 🚀 Función principal: Crear páginas del sistema
 * ==========================================================
 */
function tureserva_create_system_pages() {

    // 🔹 Listado de páginas base
    $pages = array(
        'buscar-disponibilidad' => array(
            'title'   => 'Buscar disponibilidad',
            'content' => '[tureserva_buscar_disponibilidad]'
        ),
        'cancelacion-de-reserva' => array(
            'title'   => 'Cancelación de reserva',
            'content' => '[tureserva_cancelacion_reserva]'
        ),
        'comodidades' => array(
            'title'   => 'Comodidades',
            'content' => '[tureserva_comodidades]'
        ),
        'confirmacion-de-reserva' => array(
            'title'   => 'Confirmación de reserva',
            'content' => '[tureserva_confirmacion_reserva]'
        ),
        'reserva-cancelada' => array(
            'title'   => 'Reserva cancelada',
            'content' => '[tureserva_reserva_cancelada]'
        ),
        'reserva-confirmada' => array(
            'title'   => 'Reserva confirmada',
            'content' => '[tureserva_reserva_confirmada]'
        ),
        'reserva-recibida' => array(
            'title'   => 'Reserva recibida',
            'content' => '[tureserva_reserva_recibida]'
        ),
        'transaccion-fallida' => array(
            'title'   => 'Transacción fallida',
            'content' => '[tureserva_transaccion_fallida]'
        ),
        'mi-cuenta' => array(
            'title'   => 'Mi cuenta',
            'content' => '[tureserva_mi_cuenta]'
        ),
        'resultados-de-busqueda' => array(
            'title'   => 'Resultados de búsqueda',
            'content' => '[tureserva_resultados_busqueda]'
        ),
    );

    // 🔹 Crear cada página si no existe
    foreach ($pages as $slug => $page) {

        $existing_page = get_page_by_path($slug);

        if (!$existing_page) {
            $new_page_id = wp_insert_post(array(
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1
            ));

            if ($new_page_id && !is_wp_error($new_page_id)) {
                update_option('tureserva_page_' . $slug, $new_page_id);
            }
        } else {
            // 🔹 Si ya existe, guardar su ID igualmente
            update_option('tureserva_page_' . $slug, $existing_page->ID);
        }
    }
}

/**
 * ==========================================================
 * 🧩 Hook de activación (debe ejecutarse desde el archivo principal)
 * ==========================================================
 */
if (defined('TURESERVA_MAIN_FILE')) {
    register_activation_hook(TURESERVA_MAIN_FILE, 'tureserva_create_system_pages');
}
/**
 * ==========================================================
 * 🧠 Función auxiliar: Obtener ID de una página del sistema
 * ==========================================================
 * Ejemplo: tureserva_get_page_id('confirmacion-de-reserva');
 */
function tureserva_get_page_id($slug) {
    return get_option('tureserva_page_' . $slug);
}
/**
 * ==========================================================
 * 🧰 Herramienta de diagnóstico de páginas del sistema
 * ==========================================================
 * Muestra el estado de cada página requerida por TuReserva.
 * ==========================================================
 */
function tureserva_add_system_pages_tool() {
    add_management_page(
        'Páginas del sistema TuReserva',
        'Páginas TuReserva',
        'manage_options',
        'tureserva-system-pages',
        'tureserva_render_system_pages_tool'
    );
}
add_action('admin_menu', 'tureserva_add_system_pages_tool');

function tureserva_render_system_pages_tool() {
    echo '<div class="wrap"><h1>Diagnóstico: Páginas del sistema TuReserva</h1>';
    echo '<p>Verifica si las páginas base del plugin están creadas correctamente.</p>';

    $pages = array(
        'buscar-disponibilidad' => 'Buscar disponibilidad',
        'cancelacion-de-reserva' => 'Cancelación de reserva',
        'comodidades' => 'Comodidades',
        'confirmacion-de-reserva' => 'Confirmación de reserva',
        'reserva-cancelada' => 'Reserva cancelada',
        'reserva-confirmada' => 'Reserva confirmada',
        'reserva-recibida' => 'Reserva recibida',
        'transaccion-fallida' => 'Transacción fallida',
        'mi-cuenta' => 'Mi cuenta',
        'resultados-de-busqueda' => 'Resultados de búsqueda',
    );

    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Página</th><th>Slug</th><th>Estado</th></tr></thead><tbody>';

    foreach ($pages as $slug => $title) {
        $page = get_page_by_path($slug);
        if ($page) {
            echo '<tr><td>' . esc_html($title) . '</td><td>' . esc_html($slug) . '</td><td style="color:green;">✅ Existente (ID: ' . $page->ID . ')</td></tr>';
        } else {
            echo '<tr><td>' . esc_html($title) . '</td><td>' . esc_html($slug) . '</td><td style="color:red;">❌ No encontrada</td></tr>';
        }
    }

    echo '</tbody></table>';

    echo '<form method="post" style="margin-top:20px;">';
    submit_button('🔄 Volver a crear páginas del sistema', 'primary', 'tureserva_regenerar_paginas');
    echo '</form>';

    // 🧩 Si se pulsa el botón, recrear páginas
    if (isset($_POST['tureserva_regenerar_paginas'])) {
        tureserva_create_system_pages();
        echo '<div class="updated"><p>✅ Páginas verificadas y creadas si hacían falta.</p></div>';
    }

    echo '</div>';
}
