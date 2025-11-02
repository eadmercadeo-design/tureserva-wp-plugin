<?php
/**
 * ==========================================================
 * Plugin Name: TuReserva – Sistema de Reservas Hoteleras
 * Description: Sistema integral de gestión hotelera con alojamientos, tarifas, temporadas, reservas, servicios, notificaciones automáticas y sincronización cloud.
 * Version: 0.3.2
 * Author: Edwin Duarte
 * Text Domain: tureserva
 * Domain Path: /languages
 * ==========================================================
 */

if (!defined('ABSPATH')) exit; // 🚫 Evita acceso directo

// =======================================================
// 🔧 CONFIGURACIÓN PRINCIPAL
// =======================================================
define('TURESERVA_VERSION', '0.3.2');
define('TURESERVA_PATH', plugin_dir_path(__FILE__));
define('TURESERVA_URL', plugin_dir_url(__FILE__));
define('TURESERVA_MAIN_FILE', __FILE__);

// =======================================================
// 🚀 INICIALIZACIÓN PRINCIPAL DEL PLUGIN
// =======================================================
function tureserva_init() {

    // =======================================================
    // 🧱 Custom Post Types
    // =======================================================
    require_once TURESERVA_PATH . 'includes/cpt-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/cpt-tarifas.php';
    require_once TURESERVA_PATH . 'includes/cpt-servicios.php';
    require_once TURESERVA_PATH . 'includes/cpt-reservas.php';
    require_once TURESERVA_PATH . 'includes/cpt-temporadas.php';
    require_once TURESERVA_PATH . 'includes/cpt-pagos.php'; // 💳 Pagos

    // =======================================================
    // 🏷️ Taxonomías
    // =======================================================
    require_once TURESERVA_PATH . 'includes/taxonomias-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/taxonomy-categorias-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/default-categorias.php';

    // =======================================================
    // 🗂️ Menús Administrativos
    // =======================================================
    require_once TURESERVA_PATH . 'includes/menu-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/menu-reservas.php';     // ✅ Menú unificado de Reservas
    require_once TURESERVA_PATH . 'includes/menu-sync.php';         // ✅ Submenús de sincronización

    
    require_once TURESERVA_PATH . 'includes/menu-comodidades.php';
    require_once TURESERVA_PATH . 'includes/meta-boxes-alojamiento.php';
    require_once TURESERVA_PATH . 'includes/menu-notificaciones.php';
    require_once TURESERVA_PATH . 'includes/menu-calendario.php';
    require_once TURESERVA_PATH . 'includes/menu-reportes.php';
    require_once TURESERVA_PATH . 'includes/menu-ajustes.php';
    require_once TURESERVA_PATH . 'includes/menu-tokens.php';
    require_once TURESERVA_PATH . 'includes/menu-cron.php';
    require_once TURESERVA_PATH . 'includes/menu-payments.php';
    require_once TURESERVA_PATH . 'includes/setup-pages.php';

    // =======================================================
    // 💡 Núcleo lógico (Core)
    // =======================================================
    require_once TURESERVA_PATH . 'core/core-helpers.php';
    require_once TURESERVA_PATH . 'core/core-settings.php';
    require_once TURESERVA_PATH . 'core/core-pricing.php';
    require_once TURESERVA_PATH . 'core/core-availability.php';
    require_once TURESERVA_PATH . 'core/core-bookings.php';
    require_once TURESERVA_PATH . 'core/core-notifications.php';
    require_once TURESERVA_PATH . 'core/core-calendar.php';
    require_once TURESERVA_PATH . 'core/core-reports.php';
    require_once TURESERVA_PATH . 'core/core-api.php';
    require_once TURESERVA_PATH . 'core/core-auth.php';
    require_once TURESERVA_PATH . 'core/core-sync.php'; // ☁️ Sincronización Supabase
    require_once TURESERVA_PATH . 'core/core-cron.php';
    require_once TURESERVA_PATH . 'core/core-payments.php';

    // =======================================================
    // ⚙️ Panel Administrativo (Integraciones)
    // =======================================================
    require_once TURESERVA_PATH . 'admin/panel-supabase.php'; // ⚙️ Configuración Cloud

    // =======================================================
    // 🧱 Shortcodes (Front-End)
    // =======================================================
    require_once TURESERVA_PATH . 'shortcodes/shortcode-buscador.php';
    require_once TURESERVA_PATH . 'shortcodes/shortcode-pago.php';

    // =======================================================
    // ☁️ Sincronización de Calendarios y Cloud (Supabase)
    // =======================================================
    require_once TURESERVA_PATH . 'includes/sync/calendar-logger.php';
    require_once TURESERVA_PATH . 'includes/sync/calendar-sync.php';   // 🟢 Página de sincronización
    require_once TURESERVA_PATH . 'includes/sync/calendar-handler.php';
    require_once TURESERVA_PATH . 'includes/sync/ical-export.php';
    require_once TURESERVA_PATH . 'includes/sync/calendar-cron.php';
    require_once TURESERVA_PATH . 'includes/sync/cloud-handler.php';
    require_once TURESERVA_PATH . 'includes/sync/cloud-sync.php';      // 🟢 Página Cloud Sync

    // =======================================================
    // 🌍 Traducciones
    // =======================================================
    load_plugin_textdomain('tureserva', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'tureserva_init');

// =======================================================
// 🗓️ ACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_activate() {

    // Crear categorías base de alojamiento
    if (function_exists('tureserva_insert_default_categorias')) {
        tureserva_insert_default_categorias();
    }

    // Registrar CPTs y taxonomías antes del flush
    tureserva_init();
    flush_rewrite_rules();

    // Acción personalizada para otras extensiones
    do_action('tureserva_activated');

    // ⚙️ Configuración inicial de notificaciones
    update_option('tureserva_admin_email', 'reservas@tuhotel.com');
    update_option('tureserva_from_name', 'TuReserva Hotel');
    update_option('tureserva_from_email', 'no-reply@tuhotel.com');
    update_option('tureserva_whatsapp_api_url', 'https://graph.facebook.com/v19.0/MY_NUMBER/messages');
    update_option('tureserva_whatsapp_token', 'TOKEN_DE_ACCESO');
}
register_activation_hook(__FILE__, 'tureserva_on_activate');

// =======================================================
// 🧹 DESACTIVACIÓN DEL PLUGIN
// =======================================================
function tureserva_on_deactivate() {
    flush_rewrite_rules();
    do_action('tureserva_deactivated');
}
register_deactivation_hook(__FILE__, 'tureserva_on_deactivate');
