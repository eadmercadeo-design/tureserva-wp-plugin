<?php
/**
 * Plugin Name: TuReserva – Sistema de Reservas Hoteleras
 * Description: Sistema integral de gestión hotelera.
 * Version: 0.3.3
 * Author: Edwin Duarte
 * Text Domain: tureserva
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 CONFIGURACIÓN PRINCIPAL
// =======================================================
define('TURESERVA_VERSION', '0.3.3');
define('TURESERVA_PATH', plugin_dir_path(__FILE__));
define('TURESERVA_URL', plugin_dir_url(__FILE__));
define('TURESERVA_MAIN_FILE', __FILE__);


// =======================================================
// 🚀 CARGA TEMPRANA DE CPTs (FIJADO)
// =======================================================
// IMPORTANTE:
// Los CPTs DEBEN cargarse ANTES de admin_menu.
// No envolver en init. No envolver en add_action.
// =======================================================
require_once TURESERVA_PATH . 'includes/cpt-alojamiento.php';
require_once TURESERVA_PATH . 'includes/cpt-reservas.php';
require_once TURESERVA_PATH . 'includes/cpt-tarifas.php';
require_once TURESERVA_PATH . 'includes/cpt-servicios.php';
require_once TURESERVA_PATH . 'includes/cpt-temporadas.php';
require_once TURESERVA_PATH . 'includes/cpt-pagos.php';


// =======================================================
// 📌 TAXONOMÍAS
// =======================================================
require_once TURESERVA_PATH . 'includes/taxonomias-alojamiento.php';
require_once TURESERVA_PATH . 'includes/taxonomy-categorias-alojamiento.php';
require_once TURESERVA_PATH . 'includes/default-categorias.php';


// =======================================================
// 🎨 CSS SOLO PARA LAS PANTALLAS DEL SISTEMA
// =======================================================
add_action('admin_enqueue_scripts', function () {
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
// 🌐 INICIALIZACIÓN GLOBAL DEL SISTEMA
// =======================================================
function tureserva_init()
{
    // ---------- ADMIN PAGES ----------
    if (is_admin()) {
        require_once TURESERVA_PATH . 'admin/pages/generar-alojamientos.php';
        require_once TURESERVA_PATH . 'admin/pages/ajustes-generales.php';
        require_once TURESERVA_PATH . 'admin/pages/idioma-alojamiento.php';
        require_once TURESERVA_PATH . 'admin/pages/panel-supabase.php';

        // Dashboard interno
        require_once TURESERVA_PATH . 'admin/dashboard/tureserva-dashboard.php';

        // AJAX Handlers
        require_once TURESERVA_PATH . 'admin/reservas/ajax-find-available.php';
        require_once TURESERVA_PATH . 'admin/reservas/ajax-create-reservation.php';

        // Menús principales y secundarios
        require_once TURESERVA_PATH . 'includes/menu-alojamiento.php';
        require_once TURESERVA_PATH . 'includes/menu-calendario.php';
        require_once TURESERVA_PATH . 'includes/menu-reservas.php';
        require_once TURESERVA_PATH . 'includes/menu-notificaciones.php';
        require_once TURESERVA_PATH . 'includes/menu-reportes.php';
        require_once TURESERVA_PATH . 'includes/menu-tokens.php';
        require_once TURESERVA_PATH . 'includes/menu-cron.php';
        require_once TURESERVA_PATH . 'includes/menu-payments.php';

        // Diseños
        require_once TURESERVA_PATH . 'includes/admin-categorias-design.php';
        require_once TURESERVA_PATH . 'includes/admin-servicios-design.php'; // 🎨 Diseño Servicios
        require_once TURESERVA_PATH . 'includes/admin-temporadas-design.php'; // 🎨 Diseño Temporadas
        require_once TURESERVA_PATH . 'includes/admin-tarifas-design.php'; // 🎨 Diseño Tarifas (Lista)
        require_once TURESERVA_PATH . 'includes/admin-tarifas-edit-design.php'; // 🎨 Diseño Tarifas (Edición)
        require_once TURESERVA_PATH . 'includes/admin-ajustes-design.php'; // 🎨 Diseño Ajustes

        // Meta boxes del módulo Alojamiento
        require_once TURESERVA_PATH . 'includes/meta-boxes-alojamiento.php';
        
        // Módulo Cupones
        require_once TURESERVA_PATH . 'includes/cpt-cupones.php';
        require_once TURESERVA_PATH . 'includes/meta-boxes-cupones.php';

        // Módulo Reglas
        require_once TURESERVA_PATH . 'includes/cpt-reglas.php';
        require_once TURESERVA_PATH . 'includes/meta-boxes-reglas.php';

        // Módulo Impuestos
        require_once TURESERVA_PATH . 'includes/cpt-impuestos.php';
        require_once TURESERVA_PATH . 'includes/meta-boxes-impuestos.php';

        // Core iCal
        // require_once TURESERVA_PATH . 'core/core-ical.php';

        // Páginas especiales del sistema
        require_once TURESERVA_PATH . 'includes/setup-pages.php';

    }

    // ---------- CORE ----------
    $core_files = [
        'core-helpers.php',
        'core-settings.php',
        'core-pricing.php',
        'core-availability.php',
        'core-rules.php', // 📏 Motor de Reglas
        'core-bookings.php',
        'core-notifications.php',
        'core-email-cron.php',
        'core-calendar.php',
        'core-reports.php',
        'core-api.php',
        'core-auth.php',
        'core-sync.php',
        'core-cron.php',
        'core-payments.php',
    ];
    foreach ($core_files as $file) {
        require_once TURESERVA_PATH . 'core/' . $file;
    }

    // ---------- ELEMENTOR INTEGRATION ----------
    if ( did_action( 'elementor/loaded' ) ) {
        require_once TURESERVA_PATH . 'includes/elementor/class-tureserva-elementor.php';
    }

    // ---------- SHORTCODES ----------
    require_once TURESERVA_PATH . 'includes/shortcode-search-page.php';
    require_once TURESERVA_PATH . 'shortcodes/shortcode-buscador.php';

    // ---------- SYNC ----------
    $sync_files = [
        'calendar-logger.php',
        'calendar-sync.php',
        'calendar-handler.php',
        'ical-export.php',
        'calendar-cron.php',
        'cloud-handler.php',
        'cloud-sync.php',
        'tureserva-sync-pagos.php',
        'tureserva-sync-inverse.php',
        // New iCal Sync Classes
        '../repositories/class-tureserva-sync-urls-repository.php',
        '../repositories/class-tureserva-room-repository.php',
        '../repositories/class-tureserva-booking-repository.php',
        'class-tureserva-ical-cron.php',
        'class-tureserva-queued-synchronizer.php',
        'class-tureserva-background-synchronizer.php',
        'class-tureserva-ical-importer.php',
        'class-tureserva-ical-exporter.php',
        'class-tureserva-calendar-feed.php',
    ];
    foreach ($sync_files as $file) {
        require_once TURESERVA_PATH . 'includes/sync/' . $file;
    }

    // Initialize Global Instances
    new TuReserva_Ical_Cron();
    new TuReserva_Calendar_Feed();

    load_plugin_textdomain('tureserva', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'tureserva_init');


// =======================================================
// 🔥 ACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_activate()
{
    // Cargar toda la lógica
    tureserva_init();

    // Crear páginas del sistema automáticamente
    if (function_exists('tureserva_create_system_pages')) {
        tureserva_create_system_pages();
    }

    // Regenerar reglas
    // Asegurar que las reglas del feed estén registradas antes del flush
    if ( class_exists( 'TuReserva_Calendar_Feed' ) ) {
        $feed = new TuReserva_Calendar_Feed();
        $feed->setup_feed();
    }
    flush_rewrite_rules();

    // Crear tablas personalizadas
    require_once TURESERVA_PATH . 'includes/class-tureserva-database-manager.php';
    $db_manager = new TuReserva_Database_Manager();
    $db_manager->create_tables();
}
register_activation_hook(__FILE__, 'tureserva_on_activate');


// =======================================================
// 🧹 DESACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'tureserva_on_deactivate');
