<?php
/**
 * ==========================================================
 * CORE: Sincronización iCal — TuReserva (Skeleton Debug)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 1. HOOKS BÁSICOS
// =======================================================

if (!function_exists('tureserva_ical_register_query_vars')) {
    function tureserva_ical_register_query_vars($vars) {
        $vars[] = 'tureserva_ical';
        $vars[] = 'ical_id';
        return $vars;
    }
}
add_filter('query_vars', 'tureserva_ical_register_query_vars');

if (!function_exists('tureserva_ical_feed_handler')) {
    function tureserva_ical_feed_handler() {
        // Lógica comentada para probar estabilidad
        /*
        if (get_query_var('tureserva_ical') == 'export' && get_query_var('ical_id')) {
            // tureserva_generate_ical_feed(intval(get_query_var('ical_id')));
            exit;
        }
        */
    }
}
add_action('template_redirect', 'tureserva_ical_feed_handler');

// =======================================================
// 2. HELPER FUNCTION (Safe Mode)
// =======================================================

if (!function_exists('tureserva_get_reservas_por_alojamiento')) {
    function tureserva_get_reservas_por_alojamiento($alojamiento_id) {
        return []; // Retorno vacío por seguridad
    }
}
