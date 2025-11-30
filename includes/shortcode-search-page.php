<?php
/**
 * Shortcode: Página de Búsqueda de Disponibilidad
 * Uso: [tureserva_search_page]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'tureserva_search_page', 'tureserva_render_search_page' );

function tureserva_render_search_page() {
    // Enqueue styles and scripts
    wp_enqueue_style( 
        'tureserva-search-page-css', 
        TURESERVA_URL . 'assets/css/search-availability.css', 
        array(), 
        TURESERVA_VERSION 
    );

    wp_enqueue_script( 
        'tureserva-search-page-js', 
        TURESERVA_URL . 'assets/js/search-availability.js', 
        array( 'jquery' ), 
        TURESERVA_VERSION, 
        true 
    );

    // Buffer output
    ob_start();
    
    // Include template
    $template_path = TURESERVA_PATH . 'templates/search-availability.html';
    if ( file_exists( $template_path ) ) {
        include $template_path;
    } else {
        echo '<p>Error: Template not found.</p>';
    }

    return ob_get_clean();
}
