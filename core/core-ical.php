<?php
/**
 * ==========================================================
 * CORE: Sincronización iCal — TuReserva (Step 2: Export)
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
        if (get_query_var('tureserva_ical') == 'export' && get_query_var('ical_id')) {
            $post_id = intval(get_query_var('ical_id'));
            tureserva_generate_ical_feed($post_id);
            exit;
        }
    }
}
add_action('template_redirect', 'tureserva_ical_feed_handler');

// =======================================================
// 2. LÓGICA DE EXPORTACIÓN
// =======================================================

if (!function_exists('tureserva_generate_ical_feed')) {
    function tureserva_generate_ical_feed($post_id) {
        // Verificar que sea un alojamiento válido
        if (get_post_type($post_id) !== 'trs_alojamiento') {
            wp_die('Alojamiento no válido.', 'Error iCal', ['response' => 404]);
        }
    
        $alojamiento = get_post($post_id);
        $reservas = tureserva_get_reservas_por_alojamiento($post_id);
    
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="calendario-' . $post_id . '.ics"');
    
        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//TuReserva//NONSGML v1.0//EN\r\n";
        echo "CALSCALE:GREGORIAN\r\n";
        echo "METHOD:PUBLISH\r\n";
        echo "X-WR-CALNAME:" . esc_html($alojamiento->post_title) . " - TuReserva\r\n";
    
        if ($reservas && is_array($reservas)) {
            foreach ($reservas as $reserva) {
                $checkin = get_post_meta($reserva->ID, '_tureserva_checkin', true);
                $checkout = get_post_meta($reserva->ID, '_tureserva_checkout', true);
                
                if ($checkin && $checkout) {
                    echo "BEGIN:VEVENT\r\n";
                    echo "UID:tureserva-" . $reserva->ID . "@" . $_SERVER['HTTP_HOST'] . "\r\n";
                    echo "DTSTART;VALUE=DATE:" . date('Ymd', strtotime($checkin)) . "\r\n";
                    echo "DTEND;VALUE=DATE:" . date('Ymd', strtotime($checkout)) . "\r\n";
                    echo "SUMMARY:Reserva #" . $reserva->ID . "\r\n";
                    echo "DESCRIPTION:Reserva importada de TuReserva.\r\n";
                    echo "END:VEVENT\r\n";
                }
            }
        }
    
        echo "END:VCALENDAR";
    }
}

// =======================================================
// 3. HELPER FUNCTION
// =======================================================

if (!function_exists('tureserva_get_reservas_por_alojamiento')) {
    function tureserva_get_reservas_por_alojamiento($alojamiento_id) {
        return get_posts([
            'post_type' => 'tureserva_reserva',
            'numberposts' => -1,
            'meta_key' => '_tureserva_alojamiento_id',
            'meta_value' => $alojamiento_id,
            'post_status' => ['publish', 'future']
        ]);
    }
}
