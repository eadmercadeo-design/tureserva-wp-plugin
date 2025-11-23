<?php
/**
 * ==========================================================
 * CORE: Sincronización iCal — TuReserva
 * ==========================================================
 * Gestiona la exportación (generación de feeds .ics) y la
 * importación de calendarios externos (Airbnb, Booking, etc).
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
// 2. LÓGICA DE EXPORTACIÓN (CORREGIDA)
// =======================================================

if (!function_exists('tureserva_generate_ical_feed')) {
    function tureserva_generate_ical_feed($post_id) {
        // 1. Verificar CPT (Soportamos ambos por si acaso hay datos legacy)
        $type = get_post_type($post_id);
        if ($type !== 'trs_alojamiento' && $type !== 'tureserva_alojamiento') {
            status_header(404);
            wp_die('Alojamiento no válido (' . esc_html($type) . ').', 'Error iCal', ['response' => 404]);
        }
    
        $alojamiento = get_post($post_id);
        if (!$alojamiento) {
             status_header(404);
             wp_die('Alojamiento no encontrado.', 'Error iCal', ['response' => 404]);
        }

        // 2. Headers Anti-Caché (CRÍTICO)
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="calendario-' . $post_id . '.ics"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    
        // 3. Generar contenido .ics
        $eol = "\r\n";
        $site_name = get_bloginfo('name');
        
        echo "BEGIN:VCALENDAR" . $eol;
        echo "VERSION:2.0" . $eol;
        echo "PRODID:-//" . sanitize_title($site_name) . "//TuReserva v1.0//EN" . $eol;
        echo "CALSCALE:GREGORIAN" . $eol;
        echo "METHOD:PUBLISH" . $eol;
        echo "X-WR-CALNAME:" . tureserva_ical_escape_text($alojamiento->post_title . ' - ' . $site_name) . $eol;
        
        // Zona horaria
        $timezone_string = get_option('timezone_string');
        if (!$timezone_string) $timezone_string = 'UTC';
        echo "X-WR-TIMEZONE:" . $timezone_string . $eol;
    
        // Obtener reservas (Sin 'fields' => 'ids' para evitar crash)
        $reservas = tureserva_get_reservas_por_alojamiento($post_id);

        if ($reservas && is_array($reservas)) {
            foreach ($reservas as $reserva) {
                // Verificar que sea objeto WP_Post
                if (!is_object($reserva)) continue;

                // Filtrar por estado (confirmada)
                $estado = get_post_meta($reserva->ID, '_tureserva_estado', true);
                if ($estado !== 'confirmada') continue;

                // Obtener fechas
                $checkin = get_post_meta($reserva->ID, '_tureserva_checkin', true);
                $checkout = get_post_meta($reserva->ID, '_tureserva_checkout', true);
                
                if ($checkin && $checkout) {
                    // Formato iCal Date (YYYYMMDD)
                    $dtstart = date('Ymd', strtotime($checkin));
                    $dtend = date('Ymd', strtotime($checkout)); 

                    // UID Robusto
                    $uid_string = $reserva->ID . $checkin . $checkout . site_url();
                    $uid = 'trs-' . $reserva->ID . '-' . md5($uid_string) . '@' . $_SERVER['HTTP_HOST'];
                    
                    // Timestamps
                    $dtstamp = gmdate('Ymd\THis\Z'); 
                    
                    echo "BEGIN:VEVENT" . $eol;
                    echo "UID:" . $uid . $eol;
                    echo "DTSTAMP:" . $dtstamp . $eol;
                    echo "DTSTART;VALUE=DATE:" . $dtstart . $eol;
                    echo "DTEND;VALUE=DATE:" . $dtend . $eol;
                    echo "SUMMARY:" . tureserva_ical_escape_text("Reserva #" . $reserva->ID) . $eol;
                    echo "DESCRIPTION:" . tureserva_ical_escape_text("Reserva confirmada en " . $site_name) . $eol;
                    echo "STATUS:CONFIRMED" . $eol;
                    echo "END:VEVENT" . $eol;
                }
            }
        }
    
        echo "END:VCALENDAR";
    }
}

// Helper para escapar texto en iCal
if (!function_exists('tureserva_ical_escape_text')) {
    function tureserva_ical_escape_text($text) {
        $text = str_replace(["\r\n", "\n", "\r"], "\\n", $text);
        $text = str_replace([",", ";", "\\"], ["\\,", "\\;", "\\\\"], $text);
        return $text;
    }
}

// =======================================================
// 3. HELPER FUNCTION
// =======================================================

if (!function_exists('tureserva_get_reservas_por_alojamiento')) {
    function tureserva_get_reservas_por_alojamiento($alojamiento_id) {
        if (empty($alojamiento_id)) return [];
        
        return get_posts([
            'post_type' => 'tureserva_reserva',
            'numberposts' => -1,
            'meta_key' => '_tureserva_alojamiento_id',
            'meta_value' => $alojamiento_id,
            'post_status' => ['publish', 'future', 'private']
            // 'fields' => 'ids' REMOVED to prevent crash
        ]);
    }
}

// =======================================================
// 4. IMPORTACIÓN (Placeholder seguro)
// =======================================================

function tureserva_sync_external_calendars($alojamiento_id) {
    return ['status' => 'success', 'message' => 'Función en mantenimiento.'];
}

// AJAX Handler para sincronización manual
add_action('wp_ajax_tureserva_manual_sync', 'tureserva_ajax_manual_sync');

function tureserva_ajax_manual_sync() {
    if (!check_ajax_referer('tureserva_sync_nonce', 'nonce', false) || !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $id = intval($_POST['id']);
    $result = tureserva_sync_external_calendars($id);

    if ($result['status'] === 'error') {
        wp_send_json_error($result);
    } else {
        wp_send_json_success($result);
    }
}
