<?php
/**
 * ==========================================================
 * EXPORTADOR iCal — Genera calendarios externos para cada alojamiento
 * ==========================================================
 * Cada alojamiento tendrá un endpoint REST público:
 * /wp-json/tureserva/v1/ical/{alojamiento_id}
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📡 Registrar endpoint REST
// =======================================================
add_action('rest_api_init', function () {
    register_rest_route('tureserva/v1', '/ical/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'tureserva_generate_ical_feed',
        'permission_callback' => '__return_true',
    ]);
});

// =======================================================
// 🧠 Generar el contenido del archivo .ICS
// =======================================================
function tureserva_generate_ical_feed($data) {
    $alojamiento_id = intval($data['id']);
    $alojamiento = get_post($alojamiento_id);
    if (!$alojamiento || $alojamiento->post_type !== 'alojamiento') {
        return new WP_Error('invalid', 'Alojamiento no encontrado', ['status' => 404]);
    }

    // Obtener reservas asociadas
    $reservas = get_posts([
        'post_type' => 'reserva',
        'meta_query' => [
            [
                'key' => '_tureserva_alojamiento',
                'value' => $alojamiento_id
            ]
        ],
        'post_status' => ['publish', 'confirmed', 'pending'],
        'numberposts' => -1
    ]);

    // Encabezado ICS
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//TuReserva//iCal Export//ES\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";
    $ical .= "METHOD:PUBLISH\r\n";

    foreach ($reservas as $r) {
        $checkin  = get_post_meta($r->ID, '_tureserva_checkin', true);
        $checkout = get_post_meta($r->ID, '_tureserva_checkout', true);
        $estado   = get_post_meta($r->ID, '_tureserva_estado', true) ?: 'confirmada';
        $cliente  = get_post_meta($r->ID, '_tureserva_cliente_nombre', true);
        $uid      = 'reserva-' . $r->ID . '@tureserva.local';

        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:$uid\r\n";
        $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical .= "DTSTART;VALUE=DATE:" . date('Ymd', strtotime($checkin)) . "\r\n";
        $ical .= "DTEND;VALUE=DATE:" . date('Ymd', strtotime($checkout)) . "\r\n";
        $ical .= "SUMMARY:" . esc_html($alojamiento->post_title) . " - " . esc_html($estado) . "\r\n";
        $ical .= "DESCRIPTION:" . esc_html($cliente ?: 'Reserva TuReserva') . "\r\n";
        $ical .= "STATUS:CONFIRMED\r\n";
        $ical .= "END:VEVENT\r\n";
    }

    $ical .= "END:VCALENDAR\r\n";

    // Encabezados HTTP para descarga o lectura
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="tureserva-' . $alojamiento_id . '.ics"');

    echo $ical;
    exit;
}
