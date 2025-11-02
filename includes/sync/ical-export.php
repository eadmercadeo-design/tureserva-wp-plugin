<?php
/**
 * ==========================================================
 * EXPORTADOR iCal — Genera calendarios externos por alojamiento
 * ==========================================================
 * Cada alojamiento tendrá un endpoint público:
 * /wp-json/tureserva/v1/ical/{alojamiento_id}
 *
 * Compatible con Airbnb, Booking, Google Calendar, etc.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📡 REGISTRAR ENDPOINT REST
// =======================================================
add_action('rest_api_init', function () {
    register_rest_route('tureserva/v1', '/ical/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'tureserva_generate_ical_feed',
        'permission_callback' => '__return_true', // Público
    ]);
});

// =======================================================
// 🧠 GENERAR CONTENIDO DEL ARCHIVO .ICS
// =======================================================
function tureserva_generate_ical_feed($data) {
    $alojamiento_id = intval($data['id']);
    $alojamiento    = get_post($alojamiento_id);

    if (!$alojamiento || $alojamiento->post_type !== 'alojamiento') {
        return new WP_Error('invalid_alojamiento', 'Alojamiento no encontrado', ['status' => 404]);
    }

    // =======================================================
    // 📅 Obtener reservas del alojamiento
    // =======================================================
    $reservas = get_posts([
        'post_type'   => 'reserva',
        'meta_query'  => [
            [
                'key'   => '_tureserva_alojamiento',
                'value' => $alojamiento_id,
            ]
        ],
        'post_status' => ['publish', 'confirmed', 'pending'],
        'numberposts' => -1,
    ]);

    // =======================================================
    // 🧾 Encabezado ICS (cabecera estándar iCalendar)
    // =======================================================
    $ical  = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//TuReserva//iCal Export//ES\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";
    $ical .= "METHOD:PUBLISH\r\n";
    $ical .= "X-WR-CALNAME:" . tureserva_escape_ical_text($alojamiento->post_title) . "\r\n";
    $ical .= "X-WR-TIMEZONE:UTC\r\n";

    // =======================================================
    // 🔁 Agregar eventos
    // =======================================================
    foreach ($reservas as $r) {
        $checkin   = get_post_meta($r->ID, '_tureserva_checkin', true);
        $checkout  = get_post_meta($r->ID, '_tureserva_checkout', true);
        $estado    = strtolower(get_post_meta($r->ID, '_tureserva_estado', true)) ?: 'confirmada';
        $cliente   = get_post_meta($r->ID, '_tureserva_cliente_nombre', true);
        $email     = get_post_meta($r->ID, '_tureserva_cliente_email', true);

        // Definir estado ICS
        $ical_status = match ($estado) {
            'cancelada', 'cancelado' => 'CANCELLED',
            'pendiente'              => 'TENTATIVE',
            default                  => 'CONFIRMED',
        };

        $uid = 'reserva-' . $r->ID . '@tureserva.local';
        $summary = sprintf('%s — %s', $alojamiento->post_title, ucfirst($estado));
        $description = sprintf("Cliente: %s\nEmail: %s", $cliente ?: 'Desconocido', $email ?: 'N/D');

        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:$uid\r\n";
        $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical .= "DTSTART;VALUE=DATE:" . gmdate('Ymd', strtotime($checkin)) . "\r\n";
        $ical .= "DTEND;VALUE=DATE:" . gmdate('Ymd', strtotime($checkout)) . "\r\n";
        $ical .= "SUMMARY:" . tureserva_escape_ical_text($summary) . "\r\n";
        $ical .= "DESCRIPTION:" . tureserva_escape_ical_text($description) . "\r\n";
        $ical .= "STATUS:$ical_status\r\n";
        $ical .= "END:VEVENT\r\n";
    }

    // =======================================================
    // ✅ Final del archivo
    // =======================================================
    $ical .= "END:VCALENDAR\r\n";

    // =======================================================
    // 📤 Salida HTTP con headers correctos
    // =======================================================
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="tureserva-' . $alojamiento_id . '.ics"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo $ical;
    exit;
}

// =======================================================
// 🧹 ESCAPAR CARACTERES PARA COMPATIBILIDAD iCal
// =======================================================
/**
 * Escapa caracteres especiales según RFC 5545
 * 
 * @param string $text Texto a limpiar
 * @return string Texto limpio compatible con iCal
 */
function tureserva_escape_ical_text($text) {
    $text = wp_strip_all_tags($text);
    $text = str_replace(["\\", ";", ",", "\n", "\r"], ["\\\\", "\;", "\,", "\\n", ""], $text);
    return trim($text);
}
