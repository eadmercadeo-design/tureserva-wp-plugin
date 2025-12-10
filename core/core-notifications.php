<?php
/**
 * ==========================================================
 * CORE: Notificaciones y Correos — TuReserva
 * ==========================================================
 * Maneja el envío de correos transaccionales:
 * - Nueva reserva (Admin + Cliente)
 * - Reserva confirmada
 * - Reserva cancelada
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📧 HELPER: Obtener correos de administración
// =======================================================
function tureserva_get_admin_emails() {
    $raw_emails = get_option( 'tureserva_admin_email', get_option( 'admin_email' ) );
    $emails_arr = explode( ',', $raw_emails );
    
    // Limpiar espacios y validar
    $clean_emails = array();
    foreach ( $emails_arr as $email ) {
        $email = sanitize_email( trim( $email ) );
        if ( is_email( $email ) ) {
            $clean_emails[] = $email;
        }
    }
    
    // Fallback: si no hay validos, usar el general de WP
    if ( empty( $clean_emails ) ) {
        $clean_emails[] = get_option( 'admin_email' );
    }
    
    return $clean_emails;
}

// =======================================================
// 🆕 HOOK: Nueva Reserva Creada
// =======================================================
add_action( 'tureserva_reserva_creada', 'tureserva_notificar_nueva_reserva', 10, 2 );

function tureserva_notificar_nueva_reserva( $reserva_id, $data ) {
    
    // 1. Notificar al Administrador(es)
    // ---------------------------------------------------
    $admin_emails = tureserva_get_admin_emails();
    $asunto_admin = sprintf( __( '[%s] Nueva reserva #%d recibida', 'tureserva' ), get_bloginfo( 'name' ), $reserva_id );
    
    $mensaje_admin  = "Hola Admin,\n\n";
    $mensaje_admin .= "Se ha recibido una nueva reserva.\n\n";
    $mensaje_admin .= "Detalles:\n";
    $mensaje_admin .= "ID: #{$reserva_id}\n";
    $mensaje_admin .= "Cliente: {$data['cliente']['nombre']} ({$data['cliente']['email']})\n";
    $mensaje_admin .= "Fecha: {$data['check_in']} al {$data['check_out']}\n";
    $mensaje_admin .= "Alojamiento ID: {$data['alojamiento_id']}\n\n";
    $mensaje_admin .= "Revisa el panel para más detalles:\n";
    $mensaje_admin .= admin_url( 'post.php?post=' . $reserva_id . '&action=edit' );

    foreach ( $admin_emails as $admin_email ) {
        wp_mail( $admin_email, $asunto_admin, $mensaje_admin );
    }

    // 2. Notificar al Cliente (Recibo de solicitud)
    // ---------------------------------------------------
    // Usamos la plantilla guardada en opciones
    $template_nueva = get_option( 'tureserva_email_nueva_reserva', '' );
    
    if ( ! empty( $template_nueva ) && ! empty( $data['cliente']['email'] ) ) {
        
        $asunto_cliente = sprintf( __( '[%s] Hemos recibido tu reserva #%d', 'tureserva' ), get_bloginfo( 'name' ), $reserva_id );
        
        // Reemplazo básico de variables
        $mensaje_cliente = strtr( $template_nueva, array(
            '{nombre_cliente}' => $data['cliente']['nombre'],
            '{alojamiento}'    => get_the_title( $data['alojamiento_id'] ),
            '{check_in}'       => $data['check_in'],
            '{check_out}'      => $data['check_out'],
            '{reserva_id}'     => $reserva_id
        ));

        wp_mail( $data['cliente']['email'], $asunto_cliente, nl2br( $mensaje_cliente ), array('Content-Type: text/html; charset=UTF-8') );
    }
}

// =======================================================
// ✅ HOOK: Cambio de Estado (Confirmación / Cancelación)
// =======================================================
add_action( 'tureserva_reserva_estado_actualizado', 'tureserva_notificar_cambio_estado', 10, 2 );

function tureserva_notificar_cambio_estado( $reserva_id, $nuevo_estado ) {
    
    // Solo enviamos correos por Confirmación o Cancelación
    if ( ! in_array( $nuevo_estado, ['confirmada', 'cancelada'] ) ) return;

    $cliente_email = get_post_meta( $reserva_id, '_tureserva_cliente_email', true );
    if ( empty( $cliente_email ) ) return;
    
    $cliente_nombre = get_post_meta( $reserva_id, '_tureserva_cliente_nombre', true );
    $alojamiento_id = get_post_meta( $reserva_id, '_tureserva_alojamiento_id', true );
    $check_in       = get_post_meta( $reserva_id, '_tureserva_checkin', true );
    $check_out      = get_post_meta( $reserva_id, '_tureserva_checkout', true );
    
    $template = '';
    $asunto   = '';

    if ( $nuevo_estado === 'confirmada' ) {
        $template = get_option( 'tureserva_email_confirmada', '' );
        $asunto   = sprintf( __( '[%s] Tu reserva #%d está CONFIRMADA', 'tureserva' ), get_bloginfo( 'name' ), $reserva_id );
    } elseif ( $nuevo_estado === 'cancelada' ) {
        $template = get_option( 'tureserva_email_cancelada', '' );
        $asunto   = sprintf( __( '[%s] Tu reserva #%d ha sido CANCELADA', 'tureserva' ), get_bloginfo( 'name' ), $reserva_id );
    }

    if ( ! empty( $template ) ) {
        $mensaje = strtr( $template, array(
            '{nombre_cliente}' => $cliente_nombre,
            '{alojamiento}'    => get_the_title( $alojamiento_id ),
            '{check_in}'       => $check_in,
            '{check_out}'      => $check_out,
            '{reserva_id}'     => $reserva_id
        ));

        wp_mail( $cliente_email, $asunto, nl2br( $mensaje ), array('Content-Type: text/html; charset=UTF-8') );
    }
}
