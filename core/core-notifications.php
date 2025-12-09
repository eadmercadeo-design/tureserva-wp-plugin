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
// =======================================================
// 🔧 CONFIGURACIÓN PREDETERMINADA
// =======================================================
function tureserva_get_email_config() {
    // 1. Intentar obtener la nueva opción plural
    $admin_emails_str = get_option( 'tureserva_admin_emails' );
    
    // 2. Fallback a opción singular (legacy) si la nueva no existe
    if ( false === $admin_emails_str ) {
        $admin_emails_str = get_option( 'tureserva_admin_email' );
    }

    // 3. Fallback al email general del sitio si está vacío
    if ( empty( $admin_emails_str ) ) {
        $admin_emails_str = get_option( 'admin_email' );
    }
    
    // Convertir lista separada por comas en array
    $admin_emails = array_map( 'trim', explode( ',', (string)$admin_emails_str ) );
    $admin_emails = array_filter( $admin_emails ); // Eliminar vacíos
    $admin_emails = array_unique( $admin_emails ); // Eliminar duplicados

    return array(
        'admin_email' => $admin_emails, // Retorna array
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
    
    // Obtener emails de admin desde la configuración centralizada
    $config = tureserva_get_email_config();
    $admin_emails = $config['admin_email'];

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
    
    // Si hay emails de admin configurados, enviar
    if ( ! empty( $admin_emails ) ) {
        tureserva_enviar_email( $admin_emails, $asunto_admin, $mensaje_admin );
    }

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

// =======================================================
// 💸 NOTIFICACIÓN: PAGO RECIBIDO (RECIBO)
// =======================================================
add_action( 'tureserva_pago_confirmado', 'tureserva_notificar_pago_recibido', 10, 2 );
function tureserva_notificar_pago_recibido( $reserva_id, $datos_pago ) {

    $detalles = tureserva_obtener_detalles_reserva( $reserva_id );
    $cliente  = $detalles['cliente'];
    $aloj     = get_the_title( $detalles['alojamiento'] );
    $monto    = number_format_i18n( $datos_pago['amount'] / 100, 2 ); // Stripe envía centavos
    if (isset($datos_pago['object']) && $datos_pago['object'] !== 'charge') {
         // Si no es Stripe directo, asumimos monto normal
         $monto = number_format_i18n( $datos_pago['amount'], 2 );
    }
    
    $moneda   = strtoupper( $datos_pago['currency'] ?? 'USD' );
    $id_pago  = $datos_pago['id'] ?? 'Manual';

    $asunto = '🧾 Recibo de pago — Reserva #' . $reserva_id;
    $mensaje = "
        <h2>¡Pago recibido con éxito!</h2>
        <p>Hola <strong>{$cliente['nombre']}</strong>,</p>
        <p>Hemos recibido tu pago correctamente. Aquí tienes los detalles:</p>
        <ul>
            <li><strong>Concepto:</strong> Reserva en {$aloj}</li>
            <li><strong>Monto:</strong> \${$monto} {$moneda}</li>
            <li><strong>ID Transacción:</strong> {$id_pago}</li>
            <li><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</li>
        </ul>
        <p>Tu reserva está confirmada. ¡Gracias!</p>
    ";

    tureserva_enviar_email( $cliente['email'], $asunto, $mensaje );
}

