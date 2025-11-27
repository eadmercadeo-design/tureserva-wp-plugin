<?php
/**
 * ==========================================================
 * CORE: Sincronización iCal — TuReserva (Renamed Functions)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 1. HOOKS BÁSICOS
// =======================================================

function trs_ical_register_query_vars($vars) {
    $vars[] = 'tureserva_ical';
    $vars[] = 'ical_id';
    return $vars;
}
add_filter('query_vars', 'trs_ical_register_query_vars');

function trs_ical_feed_handler() {
    if (get_query_var('tureserva_ical') == 'export' && get_query_var('ical_id')) {
        $post_id = intval(get_query_var('ical_id'));
        trs_ical_generate_feed($post_id);
        exit;
    }
}
add_action('template_redirect', 'trs_ical_feed_handler');

// =======================================================
// 2. LÓGICA DE EXPORTACIÓN
// =======================================================

function trs_ical_generate_feed($post_id) {
    // 1. Verificar CPT
    $type = get_post_type($post_id);
    if ($type !== 'trs_alojamiento' && $type !== 'tureserva_alojamiento') {
<?php
/**
 * ==========================================================
 * CORE: Sincronización iCal — TuReserva (Renamed Functions)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 1. HOOKS BÁSICOS
// =======================================================

function trs_ical_register_query_vars($vars) {
    $vars[] = 'tureserva_ical';
    $vars[] = 'ical_id';
    return $vars;
}
add_filter('query_vars', 'trs_ical_register_query_vars');

function trs_ical_feed_handler() {
    if (get_query_var('tureserva_ical') == 'export' && get_query_var('ical_id')) {
        $post_id = intval(get_query_var('ical_id'));
        trs_ical_generate_feed($post_id);
        exit;
    }
}
add_action('template_redirect', 'trs_ical_feed_handler');

// =======================================================
// 2. LÓGICA DE EXPORTACIÓN
// =======================================================

function trs_ical_generate_feed($post_id) {
    // 1. Verificar CPT
    $type = get_post_type($post_id);
    if ($type !== 'trs_alojamiento' && $type !== 'tureserva_alojamiento') {
        status_header(404);
        wp_die('Alojamiento no válido.', 'Error iCal', ['response' => 404]);
    }

    $alojamiento = get_post($post_id);
    if (!$alojamiento) {
         status_header(404);
         wp_die('Alojamiento no encontrado.', 'Error iCal', ['response' => 404]);
    }

    // 2. Headers
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="calendario-' . $post_id . '.ics"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    // 3. Contenido
    $eol = "\r\n";
    $site_name = get_bloginfo('name');
    
    echo "BEGIN:VCALENDAR" . $eol;
    echo "VERSION:2.0" . $eol;
    echo "PRODID:-//" . sanitize_title($site_name) . "//TuReserva v1.0//EN" . $eol;
    echo "CALSCALE:GREGORIAN" . $eol;
    echo "METHOD:PUBLISH" . $eol;
    echo "X-WR-CALNAME:" . trs_ical_escape($alojamiento->post_title . ' - ' . $site_name) . $eol;
    
    $timezone_string = get_option('timezone_string') ?: 'UTC';
    echo "X-WR-TIMEZONE:" . $timezone_string . $eol;

    // Obtener reservas
    $reservas = trs_ical_get_reservas($post_id);

    if ($reservas && is_array($reservas)) {
        foreach ($reservas as $reserva) {
            if (!is_object($reserva)) continue;

            $estado = get_post_meta($reserva->ID, '_tureserva_estado', true);
            if ($estado !== 'confirmada') continue;

            $checkin = get_post_meta($reserva->ID, '_tureserva_checkin', true);
            $checkout = get_post_meta($reserva->ID, '_tureserva_checkout', true);
            
            if ($checkin && $checkout) {
                $dtstart = date('Ymd', strtotime($checkin));
                $dtend = date('Ymd', strtotime($checkout)); 

                $uid_string = $reserva->ID . $checkin . $checkout . site_url();
                $uid = 'trs-' . $reserva->ID . '-' . md5($uid_string) . '@' . $_SERVER['HTTP_HOST'];
                $dtstamp = gmdate('Ymd\THis\Z'); 
                
                echo "BEGIN:VEVENT" . $eol;
                echo "UID:" . $uid . $eol;
                echo "DTSTAMP:" . $dtstamp . $eol;
                echo "DTSTART;VALUE=DATE:" . $dtstart . $eol;
                echo "DTEND;VALUE=DATE:" . $dtend . $eol;
                echo "SUMMARY:" . trs_ical_escape("Reserva #" . $reserva->ID) . $eol;
                echo "DESCRIPTION:" . trs_ical_escape("Reserva confirmada en " . $site_name) . $eol;
                echo "STATUS:CONFIRMED" . $eol;
                echo "END:VEVENT" . $eol;
            }
        }
    }

    echo "END:VCALENDAR";
}

// =======================================================
// 3. HELPERS
// =======================================================

function trs_ical_get_reservas($alojamiento_id) {
    if (empty($alojamiento_id)) return [];
    return get_posts([
        'post_type'   => 'tureserva_reserva',
        'numberposts' => -1,
        'meta_key'    => '_tureserva_alojamiento_id',
        'meta_value'  => $alojamiento_id,
        'post_status' => ['publish', 'future', 'private']
    ]);
}

function trs_ical_escape($text) {
    $text = str_replace(["\r\n", "\n", "\r"], "\\n", $text);
    $text = str_replace([",", ";", "\\"], ["\\,", "\\;", "\\\\"], $text);
    return $text;
}

// =======================================================
// 4. IMPORTACIÓN (Placeholder)
// =======================================================

function trs_ical_sync_external($alojamiento_id) {
    return ['status' => 'success', 'message' => 'Función en mantenimiento.'];
}

// AJAX
add_action('wp_ajax_tureserva_manual_sync', 'trs_ical_ajax_sync');

function trs_ical_ajax_sync() {
    if (!check_ajax_referer('tureserva_sync_nonce', 'nonce', false) || !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $id = intval($_POST['id']);
    $result = trs_ical_sync_external($id);

    if ($result['status'] === 'error') {
        wp_send_json_error($result);
    } else {
        wp_send_json_success($result);
    }
}
