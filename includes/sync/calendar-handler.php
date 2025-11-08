<?php
/**
 * ==========================================================
 * HANDLER — Sincronización con calendarios externos
 * ==========================================================
 * Importa calendarios iCal de Airbnb / Booking y los envía a Supabase
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

require_once TURESERVA_PATH . 'includes/sync/ical-parser.php';
require_once TURESERVA_PATH . 'includes/sync/calendar-logger.php';

// =======================================================
// 🔁 Sincronizar todos los alojamientos (acción manual o CRON)
// =======================================================
add_action('admin_post_tureserva_sync_all_calendars', 'tureserva_sync_all_calendars');

function tureserva_sync_all_calendars() {
    $alojamientos = get_posts([
        'post_type'   => 'tureserva_alojamiento',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    if (empty($alojamientos)) {
        wp_die(__('No se encontraron alojamientos para sincronizar.', 'tureserva'));
    }

    foreach ($alojamientos as $a) {

        $ical_url = get_post_meta($a->ID, '_tureserva_ical_url', true);
        if (!$ical_url) {
            continue;
        }

        $ical_data = wp_remote_get($ical_url);
        if (is_wp_error($ical_data)) {
            continue;
        }

        $body = wp_remote_retrieve_body($ical_data);
        if (empty($body)) continue;

        $events = tureserva_parse_ical($body);
        $count_total = count($events);
        $count_ok = 0;

        foreach ($events as $e) {
            if (empty($e['start']) || empty($e['end'])) continue;

            $start = substr($e['start'], 0, 8);
            $end   = substr($e['end'], 0, 8);

            $check_in  = date('Y-m-d', strtotime($start));
            $check_out = date('Y-m-d', strtotime($end));

            $args = [
                'post_type'  => 'reserva',
                'meta_query' => [
                    [
                        'key'   => '_tureserva_checkin',
                        'value' => $check_in,
                    ],
                    [
                        'key'   => '_tureserva_checkout',
                        'value' => $check_out,
                    ],
                    [
                        'key'   => '_tureserva_alojamiento',
                        'value' => $a->ID,
                    ],
                ],
            ];

            $existing = get_posts($args);

            if (empty($existing)) {
                $new_id = wp_insert_post([
                    'post_title'  => 'Reserva importada - ' . $a->post_title,
                    'post_type'   => 'reserva',
                    'post_status' => 'publish',
                ]);

                update_post_meta($new_id, '_tureserva_alojamiento', $a->ID);
                update_post_meta($new_id, '_tureserva_checkin', $check_in);
                update_post_meta($new_id, '_tureserva_checkout', $check_out);
                update_post_meta($new_id, '_tureserva_fuente', 'iCal');
                $count_ok++;

                // 📤 Enviar a Supabase
                tureserva_send_reserva_to_supabase($new_id, $a->ID);
            }
        }

        // Guardar en el log
        tureserva_add_sync_log([
            'alojamiento' => $a->post_title,
            'estado'      => 'Completado',
            'total'       => $count_total,
            'exitoso'     => $count_ok,
            'fecha'       => current_time('mysql'),
        ]);
    }

    // Redirigir al panel
    wp_redirect(admin_url('edit.php?post_type=tureserva_reservas&page=tureserva-calendar-sync&synced=1'));
    exit;
}

// =======================================================
// ☁️ Enviar una reserva a Supabase
// =======================================================
function tureserva_send_reserva_to_supabase($reserva_id, $alojamiento_id) {
    $url = get_option('tureserva_supabase_url');
    $key = get_option('tureserva_supabase_api_key');

    if (!$url || !$key) return;

    $body = [
        'id_reserva'     => $reserva_id,
        'alojamiento_id' => $alojamiento_id,
        'check_in'       => get_post_meta($reserva_id, '_tureserva_checkin', true),
        'check_out'      => get_post_meta($reserva_id, '_tureserva_checkout', true),
        'adultos'        => get_post_meta($reserva_id, '_tureserva_adultos', true) ?: 1,
        'ninos'          => get_post_meta($reserva_id, '_tureserva_ninos', true) ?: 0,
        'cliente_nombre' => get_post_meta($reserva_id, '_tureserva_cliente_nombre', true),
        'cliente_email'  => get_post_meta($reserva_id, '_tureserva_cliente_email', true),
        'estado'         => get_post_meta($reserva_id, '_tureserva_estado', true) ?: 'pendiente',
        'fecha_sync'     => current_time('mysql'),
    ];

    $args = [
        'headers' => [
            'apikey' => $key,
            'Content-Type' => 'application/json',
        ],
        'body'   => json_encode([$body]),
        'method' => 'POST',
        'timeout' => 20,
    ];

    wp_remote_post("$url/tureserva_reservas", $args);
}
