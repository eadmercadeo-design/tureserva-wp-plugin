<?php
/**
 * ==========================================================
 * Plugin Name: TuReserva – Sistema de Reservas Hoteleras
 * Description: Sistema integral de gestión hotelera con alojamientos, tarifas, temporadas, reservas, servicios, notificaciones automáticas y sincronización cloud.
 * Version: 0.3.3
 * Author: Edwin Duarte
 * Text Domain: tureserva
 * Domain Path: /languages
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit; // 🚫 Evita acceso directo

// =======================================================
// 🔧 CONFIGURACIÓN PRINCIPAL
// =======================================================
define( 'TURESERVA_VERSION', '0.3.3' );
define( 'TURESERVA_PATH', plugin_dir_path( __FILE__ ) );
define( 'TURESERVA_URL', plugin_dir_url( __FILE__ ) );
define( 'TURESERVA_MAIN_FILE', __FILE__ );

// =======================================================
// 🎨 Encolar CSS global del administrador TuReserva
// =======================================================
add_action('admin_enqueue_scripts', function () {
    // Solo carga en pantallas del plugin TuReserva
    $screen = get_current_screen();
    if (isset($screen->id) && strpos($screen->id, 'tureserva') !== false) {
        wp_enqueue_style(
            'tureserva-admin-styles',
            TURESERVA_URL . 'assets/css/admin-add-reserva.css?v=6',
            [],
            null
        );
    }
});

// =======================================================
// 🎨 Scripts para validación en la pantalla de pagos
// =======================================================
add_action('admin_enqueue_scripts', function($hook) {
    global $post_type;
    if ($post_type === 'tureserva_pagos') {
        wp_enqueue_script(
            'tureserva-pagos-validation',
            TURESERVA_URL . 'admin/assets/js/pagos-validation.js',
            [],
            TURESERVA_VERSION,
            true
        );
    }
});

// =======================================================
// 🚀 FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
// =======================================================
function tureserva_init() {

    // -------------------------------------------------------
    // 🧱 CUSTOM POST TYPES
    // -------------------------------------------------------
    require_once TURESERVA_PATH . 'includes/cpt-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/cpt-tarifas.php';
    require_once TURESERVA_PATH . 'includes/cpt-servicios.php';
    require_once TURESERVA_PATH . 'includes/cpt-reservas.php';
    require_once TURESERVA_PATH . 'includes/cpt-temporadas.php';
    require_once TURESERVA_PATH . 'includes/cpt-pagos.php'; // 💳
    require_once TURESERVA_PATH . 'admin/metaboxes/metabox-pago-detalles.php';
    require_once TURESERVA_PATH . 'admin/metaboxes/metabox-pago-lateral.php';

    // -------------------------------------------------------
    // 🏷️ TAXONOMÍAS
    // -------------------------------------------------------
    require_once TURESERVA_PATH . 'includes/taxonomias-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/taxonomy-categorias-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/default-categorias.php';

    // -------------------------------------------------------
    // 🗂️ MENÚS ADMINISTRATIVOS
    // -------------------------------------------------------
    require_once TURESERVA_PATH . 'includes/menu-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/menu-calendario.php';    // ⚠️ Debe cargarse antes de menu-reservas.php
    require_once TURESERVA_PATH . 'includes/menu-reservas.php';     // ✅ Menú unificado (usa tureserva_vista_calendario)
    require_once TURESERVA_PATH . 'includes/menu-comodidades.php';
    require_once TURESERVA_PATH . 'includes/menu-notificaciones.php';
    require_once TURESERVA_PATH . 'includes/menu-reportes.php';
    require_once TURESERVA_PATH . 'includes/menu-tokens.php';
    require_once TURESERVA_PATH . 'includes/menu-cron.php';
    require_once TURESERVA_PATH . 'includes/menu-payments.php';
    require_once TURESERVA_PATH . 'includes/meta-boxes-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/setup-pages.php';

    // -------------------------------------------------------
    // ⚙️ PÁGINAS ADMIN (solo backend)
    // -------------------------------------------------------
    if ( is_admin() ) {
        require_once TURESERVA_PATH . 'admin/pages/generar-alojamientos.php';
        require_once TURESERVA_PATH . 'admin/pages/ajustes-generales.php';
        require_once TURESERVA_PATH . 'admin/pages/idioma-alojamiento.php';
        require_once TURESERVA_PATH . 'admin/pages/panel-supabase.php';
    }

    // -------------------------------------------------------
    // 💡 MÓDULOS NÚCLEO (CORE LOGIC)
    // -------------------------------------------------------
    $core_files = [
        'core-helpers.php',
        'core-settings.php',
        'core-pricing.php',
        'core-availability.php',
        'core-bookings.php',
        'core-notifications.php',
        'core-email-cron.php',
        'core-calendar.php',
        'core-reports.php',
        'core-api.php',
        'core-auth.php',
        'core-sync.php',       // ☁️ Supabase Sync
        'core-cron.php',
        'core-payments.php'
    ];
    foreach ( $core_files as $file ) {
        require_once TURESERVA_PATH . 'core/' . $file;
    }

    // -------------------------------------------------------
    // 🧱 SHORTCODES (Front-End)
    // -------------------------------------------------------
    require_once TURESERVA_PATH . 'shortcodes/shortcode-buscador.php';
    require_once TURESERVA_PATH . 'shortcodes/shortcode-pago.php';

    // -------------------------------------------------------
    // ☁️ SINCRONIZACIÓN DE CALENDARIOS / CLOUD
    // -------------------------------------------------------
    $sync_files = [
        'calendar-logger.php',
        'calendar-sync.php',
        'calendar-handler.php',
        'ical-export.php',
        'calendar-cron.php',
        'cloud-handler.php',
        'cloud-sync.php',
        'tureserva-sync-pagos.php',      // Sincronización automática de pagos
        'tureserva-sync-inverse.php'     // Sincronización inversa (descarga)
    ];
    foreach ( $sync_files as $file ) {
        require_once TURESERVA_PATH . 'includes/sync/' . $file;
    }

    // -------------------------------------------------------
    // 🌍 TRADUCCIONES
    // -------------------------------------------------------
    load_plugin_textdomain( 'tureserva', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // -------------------------------------------------------
    // 📊 Dashboard personalizado — TuReserva
    // -------------------------------------------------------
    if (is_admin()) {
        require_once TURESERVA_PATH . 'admin/dashboard/tureserva-dashboard.php';
    }
}
add_action( 'plugins_loaded', 'tureserva_init' );

// =======================================================
// 🗓️ ACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_activate() {

    // Inserta categorías por defecto
    if ( function_exists( 'tureserva_insert_default_categorias' ) ) {
        tureserva_insert_default_categorias();
    }

    // Inicializa estructuras
    tureserva_init();
    flush_rewrite_rules();

    // Hook para acciones externas
    do_action( 'tureserva_activated' );

    // Valores iniciales por defecto
    update_option( 'tureserva_admin_email', 'reservas@tuhotel.com' );
    update_option( 'tureserva_from_name', 'TuReserva Hotel' );
    update_option( 'tureserva_from_email', 'no-reply@tuhotel.com' );
    update_option( 'tureserva_whatsapp_api_url', 'https://graph.facebook.com/v19.0/MY_NUMBER/messages' );
    update_option( 'tureserva_whatsapp_token', 'TOKEN_DE_ACCESO' );
}
register_activation_hook( __FILE__, 'tureserva_on_activate' );

// =======================================================
// 🧹 DESACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_deactivate() {
    flush_rewrite_rules();
    do_action( 'tureserva_deactivated' );
}
register_deactivation_hook( __FILE__, 'tureserva_on_deactivate' );

// =======================================================
// 🧩 UTILIDAD OPCIONAL: CARGA AUTOMÁTICA DE CLASES
// =======================================================
spl_autoload_register( function ( $class ) {
    if ( strpos( $class, 'TuReserva_' ) === 0 ) {
        $path = TURESERVA_PATH . 'classes/' . strtolower( str_replace( 'TuReserva_', '', $class ) ) . '.php';
        if ( file_exists( $path ) ) require_once $path;
    }
});
