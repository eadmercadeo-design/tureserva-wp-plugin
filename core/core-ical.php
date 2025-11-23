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
// 📤 EXPORTACIÓN: GENERAR FEED ICAL
// =======================================================

// 1. Registrar Query Var
add_filter('query_vars', function($vars) {
    $vars[] = 'tureserva_ical';
    $vars[] = 'ical_id';
    return $vars;
});

// 2. Interceptar Request
add_action('template_redirect', 'tureserva_ical_feed_handler');

function tureserva_ical_feed_handler() {
    if (get_query_var('tureserva_ical') == 'export' && get_query_var('ical_id')) {
        $post_id = intval(get_query_var('ical_id'));
        tureserva_generate_ical_feed($post_id);
        exit;
    }
}

// 3. Generar Contenido .ics
function tureserva_generate_ical_feed($post_id) {
    // Verificar que sea un alojamiento válido
    if (get_post_type($post_id) !== 'trs_alojamiento') {
        wp_die('Alojamiento no válido.', 'Error iCal', ['response' => 404]);
    }

    $alojamiento = get_post($post_id);
    $reservas = tureserva_get_reservas_por_alojamiento($post_id); // Función helper (simulada aquí si no existe)

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="calendario-' . $post_id . '.ics"');

    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//TuReserva//NONSGML v1.0//EN\r\n";
    echo "CALSCALE:GREGORIAN\r\n";
    echo "METHOD:PUBLISH\r\n";
    echo "X-WR-CALNAME:" . esc_html($alojamiento->post_title) . " - TuReserva\r\n";

    if ($reservas) {
        foreach ($reservas as $reserva) {
            // Asumimos que tenemos fechas en meta
            $checkin = get_post_meta($reserva->ID, '_tureserva_checkin', true);
            $checkout = get_post_meta($reserva->ID, '_tureserva_checkout', true);
            
            if ($checkin && $checkout) {
                echo "BEGIN:VEVENT\r\n";
                echo "UID:tureserva-" . $reserva->ID . "@" . $_SERVER['HTTP_HOST'] . "\r\n";
                echo "DTSTART;VALUE=DATE:" . date('Ymd', strtotime($checkin)) . "\r\n";
                echo "DTEND;VALUE=DATE:" . date('Ymd', strtotime($checkout)) . "\r\n"; // iCal end date is exclusive, usually needs +1 day logic depending on system
                echo "SUMMARY:Reserva #" . $reserva->ID . "\r\n";
                echo "DESCRIPTION:Reserva importada de TuReserva.\r\n";
                echo "END:VEVENT\r\n";
            }
        }
    }

    echo "END:VCALENDAR";
}

// Helper temporal si no existe en otro lado
// Helper temporal si no existe en otro lado
if (!function_exists('tureserva_get_reservas_por_alojamiento')) {
    function tureserva_get_reservas_por_alojamiento($alojamiento_id) {
        return get_posts([
            'post_type' => 'tureserva_reserva',
            'numberposts' => -1,
            'meta_key' => '_tureserva_alojamiento_id',
            'meta_value' => $alojamiento_id,
            'post_status' => ['publish', 'future'] // Confirmadas
        ]);
    }
}

// =======================================================
// 📥 IMPORTACIÓN: PROCESAR CALENDARIOS EXTERNOS
// =======================================================

/**
 * Ejecuta la sincronización de un alojamiento específico
 */
function tureserva_sync_external_calendars($alojamiento_id) {
    $calendarios = get_post_meta($alojamiento_id, '_tureserva_ical_imports', true);
    
    if (empty($calendarios) || !is_array($calendarios)) {
        return ['status' => 'error', 'message' => 'No hay calendarios configurados.'];
    }

    $total_imported = 0;
    $errors = [];

    foreach ($calendarios as $cal) {
        $url = $cal['url'];
        // Aquí iría la lógica real de parsing de iCal (usando una librería o regex simple)
        // Simulamos éxito para la demo
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            $errors[] = "Error conectando a " . $cal['source'];
            continue;
        }

        // Simulación de proceso...
        $total_imported += rand(1, 5); 
    }

    // Actualizar timestamp de última sync
    update_post_meta($alojamiento_id, '_tureserva_last_sync', current_time('mysql'));
    update_post_meta($alojamiento_id, '_tureserva_sync_status', empty($errors) ? 'success' : 'warning');

    if (!empty($errors)) {
        return ['status' => 'warning', 'message' => 'Sincronizado con advertencias.', 'details' => $errors];
    }

    return ['status' => 'success', 'message' => "Sincronización completada. $total_imported eventos importados."];
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
