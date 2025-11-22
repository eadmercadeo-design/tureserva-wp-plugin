<?php
/**
 * EMAILS DEL CLIENTE — TuReserva
 * Configura los correos automáticos enviados al cliente.
 */

if (!defined('ABSPATH')) exit;

// Plantillas por defecto
$default_confirm_body = "Hola %customer_first_name%,\n\n¡Gracias por tu reserva! Aquí tienes los detalles:\n\nID Reserva: %booking_id%\nLlegada: %check_in_date%\nSalida: %check_out_date%\n\nNos vemos pronto.";
$default_cancel_body = "Hola %customer_first_name%,\n\nTu reserva #%booking_id% ha sido cancelada.\n\nSi tienes dudas, contáctanos.";
$default_pre_arrival_body = "Hola %customer_first_name%,\n\n¡Falta poco para tu llegada! Aquí tienes información importante:\n\n📍 Cómo llegar: [Mapa]\n🔑 Clave WiFi: 123456\n🚪 Acceso: La llave está en la caja de seguridad.\n\n¡Te esperamos!";
$default_location_body = "Hola %customer_first_name%,\n\nAquí tienes nuestros datos de contacto y ubicación:\n\nDirección: Calle Principal 123\nTeléfono: +123456789\nGoogle Maps: [Enlace]\n\n¡Buen viaje!";

?>

<div class="tureserva-email-config">
    
    <!-- 1. Confirmación de Reserva -->
    <?php tureserva_render_email_card(
        'tureserva_email_client_confirmation', 
        __('Confirmación de Reserva', 'tureserva'), 
        'dashicons-saved', 
        __('Se envía al cliente cuando la reserva es confirmada exitosamente.', 'tureserva'),
        '¡Reserva confirmada! #%booking_id%',
        $default_confirm_body
    ); ?>

    <!-- 2. Cancelación de Reserva -->
    <?php tureserva_render_email_card(
        'tureserva_email_client_cancel', 
        __('Cancelación de Reserva', 'tureserva'), 
        'dashicons-dismiss', 
        __('Se envía al cliente si la reserva es cancelada.', 'tureserva'),
        'Reserva cancelada: #%booking_id%',
        $default_cancel_body
    ); ?>

    <!-- 3. Recordatorio Pre-Llegada (Check-in Info) -->
    <?php 
    // Campo extra para definir horas antes
    $hours_before = get_option('tureserva_email_client_pre_arrival_hours', 24);
    $extra_pre_arrival = '
        <div class="ts-form-group" style="margin-bottom:0;">
            <label>Enviar este correo</label>
            <div style="display:flex; align-items:center; gap:10px;">
                <input type="number" name="tureserva_email_client_pre_arrival_hours" value="' . esc_attr($hours_before) . '" style="width:80px;" min="1">
                <span>horas antes de la llegada (Check-in)</span>
            </div>
            <p class="ts-helper">Útil para enviar instrucciones de acceso, claves WiFi, etc.</p>
        </div>
    ';
    
    tureserva_render_email_card(
        'tureserva_email_client_pre_arrival', 
        __('Instrucciones de Llegada (Pre-Checkin)', 'tureserva'), 
        'dashicons-location-alt', 
        __('Se envía automáticamente X horas antes de la llegada del cliente.', 'tureserva'),
        'Información importante para tu llegada - Reserva #%booking_id%',
        $default_pre_arrival_body,
        $extra_pre_arrival
    ); ?>

    <!-- 4. Ubicación y Contacto -->
    <?php tureserva_render_email_card(
        'tureserva_email_client_location', 
        __('Ubicación y Datos de Contacto', 'tureserva'), 
        'dashicons-map', 
        __('Correo dedicado con mapas, teléfonos y guía de cómo llegar.', 'tureserva'),
        'Cómo llegar y datos de contacto - Reserva #%booking_id%',
        $default_location_body
    ); ?>

    <!-- TABLA DE TAGS (Reutilizada visualmente) -->
    <div class="tureserva-card">
        <h3>🏷️ Etiquetas Disponibles</h3>
        <p>Usa estas etiquetas para personalizar los correos del cliente.</p>
        <table class="ts-tags-table">
            <thead><tr><th>Descripción</th><th>Etiqueta</th></tr></thead>
            <tbody>
                <tr><td>Nombre del Cliente</td><td><code>%customer_first_name%</code></td></tr>
                <tr><td>ID Reserva</td><td><code>%booking_id%</code></td></tr>
                <tr><td>Fecha Llegada</td><td><code>%check_in_date%</code></td></tr>
                <tr><td>Dirección Alojamiento</td><td><code>%accommodation_address%</code></td></tr>
                <tr><td>Mapa (URL)</td><td><code>%map_url%</code></td></tr>
                <tr><td>Instrucciones Acceso</td><td><code>%access_instructions%</code></td></tr>
                <tr><td>Clave WiFi</td><td><code>%wifi_password%</code></td></tr>
            </tbody>
        </table>
    </div>

</div>
