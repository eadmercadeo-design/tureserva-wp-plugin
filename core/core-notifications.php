<?php
/**
 * ==========================================================
 * CORE: Notificaciones Automáticas — TuReserva
 * ==========================================================
 * Envía notificaciones por correo y WhatsApp según eventos:
 *  - Nueva reserva
 *  - Confirmación
 *  - Cancelación
 * Usa los hooks:
 *  - tureserva_reserva_creada
 *  - tureserva_reserva_estado_actualizado
 *  - tureserva_reserva_cancelada
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔧 CONFIGURACIÓN PREDETERMINADA
// =======================================================
function tureserva_get_email_config() {
    return array(
        'admin_email' => get_option( 'tureserva_admin_email', get_option( 'admin_email' ) ),
        'from_name'   => get_option( 'tureserva_from_name', 'TuReserva' ),
        'from_email'  => get_option( 'tureserva_from_email', get_option( 'admin_email' ) ),
    );
}

// =======================================================
// 💌 ENVÍO GENERAL DE EMAIL
// =======================================================
function tureserva_enviar_email( $destinatario, $asunto, $mensaje_html ) {
    $config = tureserva_get_email_config();

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>'
    );

    wp_mail( $destinatario, $asunto, wpautop( $mensaje_html ), $headers );
}

// =======================================================
// 💬 ENVÍO OPCIONAL DE MENSAJE WHATSAPP (API externa)
// =======================================================
function tureserva_enviar_whatsapp( $telefono, $mensaje ) {
    // Placeholder: futura integración con WhatsApp Cloud API o Chat-API.
    // Ejemplo de implementación:
    //
    // $api_url = get_option('tureserva_whatsapp_api_url');
    // $token   = get_option('tureserva_whatsapp_token');
    // wp_remote_post( $api_url, array(
    //     'headers' => array('Authorization' => 'Bearer ' . $token),
    //     'body'    => array('to' => $telefono, 'message' => $mensaje)
    // ) );
    //
    // Por ahora solo logueamos el envío:
    error_log('[WhatsApp] Mensaje a ' . $telefono . ': ' . $mensaje);
}

// =======================================================
// 📩 NOTIFICACIÓN: NUEVA RESERVA
// =======================================================
add_action( 'tureserva_reserva_creada', 'tureserva_notificar_nueva_reserva', 10, 2 );
function tureserva_notificar_nueva_reserva( $reserva_id, $data ) {

    $cliente_nombre = sanitize_text_field( $data['cliente']['nombre'] );
    $cliente_email  = sanitize_email( $data['cliente']['email'] );
    $cliente_tel    = sanitize_text_field( $data['cliente']['telefono'] );

    $alojamiento    = get_the_title( $data['alojamiento_id'] );
    $check_in       = date_i18n( 'd/m/Y', strtotime( $data['check_in'] ) );
    $check_out      = date_i18n( 'd/m/Y', strtotime( $data['check_out'] ) );

    $precio_total   = number_format_i18n( $data['precio_total'] ?? 0, 2 );
    $admin_email    = get_option( 'tureserva_admin_email', get_option( 'admin_email' ) );

    // ✉️ Correo al administrador
    $asunto_admin = '🔔 Nueva reserva recibida — ' . $alojamiento;
    $mensaje_admin = "
        <h2>Nueva reserva en TuReserva</h2>
        <p><strong>Cliente:</strong> {$cliente_nombre}</p>
        <p><strong>Alojamiento:</strong> {$alojamiento}</p>
        <p><strong>Check-in:</strong> {$check_in}</p>
        <p><strong>Check-out:</strong> {$check_out}</p>
        <p><strong>Importe total:</strong> \${$precio_total}</p>
        <p><strong>Teléfono:</strong> {$cliente_tel}</p>
        <p><strong>Email:</strong> {$cliente_email}</p>
        <hr>
        <p>Ver más detalles en el panel de reservas del sitio.</p>
    ";
    tureserva_enviar_email( $admin_email, $asunto_admin, $mensaje_admin );

    // ✉️ Correo al cliente
    $asunto_cliente = 'TuReserva — Confirmación de solicitud de reserva';
    $mensaje_cliente = "
        <h2>¡Gracias por tu reserva, {$cliente_nombre}!</h2>
        <p>Hemos recibido tu solicitud para <strong>{$alojamiento}</strong>.</p>
        <ul>
            <li><strong>Entrada:</strong> {$check_in}</li>
            <li><strong>Salida:</strong> {$check_out}</li>
            <li><strong>Importe total:</strong> \${$precio_total}</li>
        </ul>
        <p>Pronto recibirás la confirmación final de nuestro equipo.</p>
        <p>— El equipo de TuReserva</p>
    ";
    tureserva_enviar_email( $cliente_email, $asunto_cliente, $mensaje_cliente );

    // 💬 Notificación por WhatsApp (si hay teléfono)
    if ( ! empty( $cliente_tel ) ) {
        $mensaje = "Hola {$cliente_nombre}, gracias por tu reserva en {$alojamiento}. 
Entrada: {$check_in}, Salida: {$check_out}. Total: \${$precio_total}.
Pronto recibirás la confirmación.";
        tureserva_enviar_whatsapp( $cliente_tel, $mensaje );
    }
}

// =======================================================
// 📩 NOTIFICACIÓN: CAMBIO DE ESTADO (CONFIRMADA / CANCELADA)
// =======================================================
add_action( 'tureserva_reserva_estado_actualizado', 'tureserva_notificar_cambio_estado', 10, 2 );
function tureserva_notificar_cambio_estado( $reserva_id, $nuevo_estado ) {

    $detalles = tureserva_obtener_detalles_reserva( $reserva_id );
    $cliente  = $detalles['cliente'];
    $aloj     = get_the_title( $detalles['alojamiento'] );

    $check_in  = date_i18n( 'd/m/Y', strtotime( $detalles['check_in'] ) );
    $check_out = date_i18n( 'd/m/Y', strtotime( $detalles['check_out'] ) );
    $total     = number_format_i18n( $detalles['precio_total'], 2 );

    if ( $nuevo_estado === 'confirmada' ) {
        $asunto = '✅ Reserva confirmada — ' . $aloj;
        $mensaje = "
            <h2>¡Tu reserva ha sido confirmada!</h2>
            <p><strong>Alojamiento:</strong> {$aloj}</p>
            <p><strong>Check-in:</strong> {$check_in}</p>
            <p><strong>Check-out:</strong> {$check_out}</p>
            <p><strong>Total:</strong> \${$total}</p>
            <p>Gracias por elegirnos. ¡Te esperamos!</p>
        ";
    } elseif ( $nuevo_estado === 'cancelada' ) {
        $asunto = '❌ Reserva cancelada — ' . $aloj;
        $mensaje = "
            <h2>Tu reserva ha sido cancelada</h2>
            <p><strong>Alojamiento:</strong> {$aloj}</p>
            <p><strong>Fechas:</strong> {$check_in} – {$check_out}</p>
            <p>Si fue un error, contáctanos para ayudarte.</p>
        ";
    } else {
        return;
    }

    // Enviar email al cliente
    tureserva_enviar_email( $cliente['email'], $asunto, $mensaje );

    // Enviar WhatsApp opcional
    if ( ! empty( $cliente['telefono'] ) ) {
        $mensaje_txt = strip_tags( wp_strip_all_tags( $mensaje ) );
        tureserva_enviar_whatsapp( $cliente['telefono'], $mensaje_txt );
    }
}
