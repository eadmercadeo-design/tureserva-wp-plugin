<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Reportes — TuReserva
 * ==========================================================
 * Carga el módulo de reportes modular desde admin/reports/
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Reportes"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_reportes' );
function tureserva_menu_reportes() {
    $hook = add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        'Reportes de Reservas',
        'Reportes',
        'manage_options',
        'tureserva_reportes',
        'tureserva_vista_reportes'
    );

    // Encolar assets solo en esta página
    add_action( "load-$hook", 'tureserva_reportes_assets' );
}

function tureserva_reportes_assets() {
    // Chart.js (CDN)
    wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.0', true );

    // Module Assets
    // dirname(__DIR__) apunta a la raíz del plugin (si este archivo está en /includes)
    $plugin_url = plugin_dir_url( dirname( __DIR__ ) . '/tureserva.php' );
    
    wp_enqueue_style( 'tureserva-reports-css', $plugin_url . 'admin/reports/reports.css', array(), '1.0.0' );
    wp_enqueue_script( 'tureserva-reports-js', $plugin_url . 'admin/reports/reports.js', array('jquery', 'chartjs'), '1.0.0', true );
}

// =======================================================
// 📊 CALLBACK: RENDERIZADO DEL DASHBOARD
// =======================================================
function tureserva_vista_reportes() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Cargar layout principal
    // dirname(__DIR__) es la raíz del plugin /tureserva/
    $layout_path = dirname( __DIR__ ) . '/admin/reports/layout.php';
    
    if ( file_exists( $layout_path ) ) {
        require_once $layout_path;
    } else {
        echo '<div class="error"><p>Error: No se encuentra el archivo de layout en ' . esc_html( $layout_path ) . '</p></div>';
    }
}
