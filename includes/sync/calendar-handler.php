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
        'post_type'   => 'trs_alojamiento',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    if (empty($alojamientos)) {
        return; // Silencioso para cron
    }

    foreach ($alojamientos as $a) {

        // Obtener imports configurados (array de URLs)
        $imports = get_post_meta($a->ID, '_tureserva_ical_imports', true);
        if (empty($imports) || !is_array($imports)) {
            continue;
        }

        $count_total = 0;
        $count_ok = 0;

        foreach ($imports as $imp) {
            $ical_url = $imp['url'] ?? '';
            if (empty($ical_url)) continue;

            $ical_data = wp_remote_get($ical_url);
            if (is_wp_error($ical_data)) continue;

            $body = wp_remote_retrieve_body($ical_data);
            if (empty($body)) continue;

            $events = tureserva_parse_ical($body);
            $count_total += count($events);

            foreach ($events as $e) {
                if (empty($e['start']) || empty($e['end'])) continue;

                $start = substr($e['start'], 0, 8);
                $end   = substr($e['end'], 0, 8);
                $uid   = $e['uid'] ?? '';

                $check_in  = date('Y-m-d', strtotime($start));
                $check_out = date('Y-m-d', strtotime($end));

                // 1. Buscar si ya existe por UID
                $existing_id = 0;
                if (!empty($uid)) {
                    $query_uid = get_posts([
                        'post_type'  => 'tureserva_reserva',
                        'meta_key'   => '_tureserva_ical_uid',
                        'meta_value' => $uid,
                        'posts_per_page' => 1,
                        'post_status' => 'any'
                    ]);
                    if (!empty($query_uid)) {
                        $existing_id = $query_uid[0]->ID;
                    }
                }

                // 2. Si no existe por UID, buscar por fechas (para evitar duplicados de sistemas viejos)
                if (!$existing_id) {
                    $query_dates = get_posts([
                        'post_type'  => 'tureserva_reserva',
                        'meta_query' => [
                            'relation' => 'AND',
                            ['key' => '_tureserva_checkin', 'value' => $check_in],
                            ['key' => '_tureserva_checkout', 'value' => $check_out],
                            ['key' => '_tureserva_alojamiento_id', 'value' => $a->ID], // Corregido meta key
                        ],
                        'posts_per_page' => 1,
                        'post_status' => 'any'
                    ]);
                    if (!empty($query_dates)) {
                        $existing_id = $query_dates[0]->ID;
                    }
                }

                if ($existing_id) {
                    // ✅ ACTUALIZAR
                    update_post_meta($existing_id, '_tureserva_checkin', $check_in);
                    update_post_meta($existing_id, '_tureserva_checkout', $check_out);
                    // Si tiene UID nuevo, guardarlo
                    if (!empty($uid)) update_post_meta($existing_id, '_tureserva_ical_uid', $uid);
                } else {
                    // ✅ CREAR NUEVA
                    $new_id = wp_insert_post([
                        'post_title'  => 'Reserva iCal - ' . $a->post_title,
                        'post_type'   => 'tureserva_reserva', // Corregido slug
                        'post_status' => 'publish', // O 'confirmed' si usas estados custom
                    ]);

                    update_post_meta($new_id, '_tureserva_alojamiento_id', $a->ID); // Corregido meta key
                    update_post_meta($new_id, '_tureserva_checkin', $check_in);
                    update_post_meta($new_id, '_tureserva_checkout', $check_out);
                    update_post_meta($new_id, '_tureserva_fuente', 'iCal');
                    update_post_meta($new_id, '_tureserva_estado', 'confirmada'); // Asumimos confirmada si viene de iCal
                    if (!empty($uid)) update_post_meta($new_id, '_tureserva_ical_uid', $uid);
                    
                    $count_ok++;

                    // 📤 Enviar a Supabase
                    if (function_exists('tureserva_send_reserva_to_supabase')) {
                        tureserva_send_reserva_to_supabase($new_id, $a->ID);
                    }
                }
            }
        }

        // Guardar estado de sincronización
        update_post_meta($a->ID, '_tureserva_last_sync', current_time('mysql'));
        update_post_meta($a->ID, '_tureserva_sync_status', 'success');

        // Guardar en el log
        if (function_exists('tureserva_add_sync_log')) {
            tureserva_add_sync_log([
                'alojamiento' => $a->post_title,
                'estado'      => 'Completado',
                'total'       => $count_total,
                'exitoso'     => $count_ok,
                'fecha'       => current_time('mysql'),
            ]);
        }
    }

    // Si es petición manual, redirigir
    if (isset($_GET['action']) && $_GET['action'] === 'tureserva_manual_sync') {
        // AJAX response handled elsewhere or redirect
    } elseif (isset($_POST['action']) && $_POST['action'] === 'tureserva_sync_all_calendars') {
         wp_redirect(admin_url('edit.php?post_type=tureserva_reserva&page=tureserva-calendarios&synced=1'));
         exit;
    }
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
