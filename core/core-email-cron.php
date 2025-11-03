<?php
/**
 * ==========================================================
 * CORE — Email Cron & Campañas
 * ==========================================================
 * Gestiona envíos automáticos de correos:
 * - Pre-checkin (1h antes del ingreso)
 * - Campañas masivas a huéspedes anteriores
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🕓 PROGRAMAR CRON AUTOMÁTICO
// =======================================================
add_action('tureserva_check_precheckin_emails', 'tureserva_envio_precheckin_automatico');

// Al activar el plugin, programamos un evento que corre cada 30 min
add_action('wp', function() {
    if (!wp_next_scheduled('tureserva_check_precheckin_emails')) {
        wp_schedule_event(time(), 'half_hour', 'tureserva_check_precheckin_emails');
    }
});

// Intervalo personalizado: cada 30 min
add_filter('cron_schedules', function($schedules) {
    $schedules['half_hour'] = [
        'interval' => 1800,
        'display'  => __('Cada 30 minutos', 'tureserva')
    ];
    return $schedules;
});

// =======================================================
// 🕐 FUNCIÓN: Enviar email 1 h antes del check-in
// =======================================================
function tureserva_envio_precheckin_automatico() {
    global $wpdb;

    // Verificar si la función está activa
    $activo = get_option('tureserva_cliente_email_precheckin_activo', false);
    if ( ! $activo ) return;

    $tema   = get_option('tureserva_cliente_email_precheckin_tema');
    $cuerpo = get_option('tureserva_cliente_email_precheckin_cuerpo');

    // Buscar reservas con check-in en 1 h
    $ahora = current_time('timestamp');
    $hora_objetivo = date('Y-m-d H:i:s', $ahora + 3600);

    $reservas = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title 
             FROM {$wpdb->posts}
             WHERE post_type = 'reserva' 
             AND post_status = 'publish'
             AND meta_value BETWEEN %s AND %s",
             date('Y-m-d H:i:s', $ahora + 3540),
             $hora_objetivo
        )
    );

    if ( empty($reservas) ) return;

    foreach ($reservas as $reserva) {
        $email_cliente = get_post_meta($reserva->ID, '_tureserva_email_cliente', true);
        $nombre_cliente = get_post_meta($reserva->ID, '_tureserva_nombre_cliente', true);
        $checkin_hora = get_post_meta($reserva->ID, '_tureserva_checkin_hora', true);
        $wifi_ssid = get_post_meta($reserva->ID, '_tureserva_wifi_ssid', true);
        $wifi_pass = get_post_meta($reserva->ID, '_tureserva_wifi_pass', true);
        $codigo_acceso = get_post_meta($reserva->ID, '_tureserva_codigo_acceso', true);
        $instrucciones = get_post_meta($reserva->ID, '_tureserva_instrucciones', true);
        $telefono_host = get_post_meta($reserva->ID, '_tureserva_telefono_host', true);
        $hotel_nombre = get_bloginfo('name');

        // Reemplazar etiquetas
        $mensaje = strtr($cuerpo, [
            '%customer_name%'     => $nombre_cliente,
            '%check_in_time%'     => $checkin_hora,
            '%room_access_code%'  => $codigo_acceso,
            '%wifi_ssid%'         => $wifi_ssid,
            '%wifi_password%'     => $wifi_pass,
            '%entry_instructions%' => $instrucciones,
            '%host_phone%'        => $telefono_host,
            '%hotel_name%'        => $hotel_nombre
        ]);

        // Enviar correo
        if ($email_cliente) {
            wp_mail($email_cliente, $tema, nl2br($mensaje));
            tureserva_log_email('precheckin', $email_cliente);
        }
    }
}

// =======================================================
// 🎯 FUNCIÓN: Enviar campañas manuales (AJAX)
// =======================================================
add_action('wp_ajax_tureserva_enviar_campania', 'tureserva_enviar_campania_ajax');

function tureserva_enviar_campania_ajax() {
    if ( ! current_user_can('manage_options') ) wp_send_json_error('Permiso denegado.');

    global $wpdb;

    $tema   = get_option('tureserva_cliente_email_campania_tema');
    $cuerpo = get_option('tureserva_cliente_email_campania_cuerpo');
    $hotel  = get_bloginfo('name');

    // Obtener todos los emails únicos de huéspedes
    $emails = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_tureserva_email_cliente'");

    if (empty($emails)) wp_send_json_error('No hay huéspedes registrados.');

    foreach ($emails as $email) {
        $mensaje = strtr($cuerpo, [
            '%customer_name%' => '',
            '%promotion_details%' => __('Promociones vigentes', 'tureserva'),
            '%hotel_name%' => $hotel
        ]);
        wp_mail($email, $tema, nl2br($mensaje));
        tureserva_log_email('campania', $email);
    }

    wp_send_json_success('Campaña enviada correctamente.');
}

// =======================================================
// 🧾 FUNCIÓN: Registrar logs de envío
// =======================================================
function tureserva_log_email($tipo, $destino) {
    $logs = get_option('tureserva_email_logs', []);
    $logs[] = [
        'tipo' => $tipo,
        'destino' => $destino,
        'fecha' => current_time('mysql')
    ];
    update_option('tureserva_email_logs', $logs);
}
