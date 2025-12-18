<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Configuración de Pagos
 * ==========================================================
 * Updated to use Modular Payment System
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📁 REGISTRO DEL SUBMENÚ
// =======================================================
// =======================================================
// 📁 REGISTRO DEL SUBMENÚ
// =======================================================
// [REMOVED] User requested consolidation into Alojamiento > Ajustes
// add_action( 'admin_menu', 'tureserva_menu_payments', 30 );
function tureserva_menu_payments() {
    /* 
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        'Configuración de Pagos',
        'Configuración de Pagos',
        'manage_options',
        'tureserva_payments',
        'tureserva_vista_payments'
    );
    */
}

// =======================================================
// 💳 INTERFAZ DE CONFIGURACIÓN
// =======================================================
function tureserva_vista_payments() {
    // Include the separate view file for cleanliness
    $view_path = plugin_dir_path( __FILE__ ) . 'admin/pages/payments-config.php';
    if ( file_exists( $view_path ) ) {
        include $view_path;
    } else {
        echo '<div class="error"><p>Error: No se encuentra el archivo de vista de pagos.</p></div>';
    }
}

// =======================================================
// ⚙️ REGISTRO DE OPCIONES (SETTINGS API)
// =======================================================
add_action( 'admin_init', 'tureserva_register_payment_settings' );
function tureserva_register_payment_settings() {
    // General Settings
    register_setting( 'tureserva_payments_general_group', 'tureserva_moneda' );
    register_setting( 'tureserva_payments_general_group', 'tureserva_simbolo_moneda' );

    // Gateway Settings
    // We register a setting array for each INTENDED gateway ID so WP handles the array save
    // Currently hardcoded IDs for registration, could be dynamic in Manager
    $gateway_ids = ['stripe', 'paypal', 'manual'];
    
    foreach ( $gateway_ids as $id ) {
        register_setting( 'tureserva_payment_gateways_group', 'tureserva_payment_settings_' . $id );
        
        // Also register individual legacy options if we want to sync them?
        // Or we just migrate them. For now, separate is safer.
    }

    // For backward compatibility (so old Stripe code still works if it reads these options)
    // We might want to sync 'tureserva_payment_settings_stripe'['secret_key'] to 'tureserva_stripe_secret_key' on save.
}
