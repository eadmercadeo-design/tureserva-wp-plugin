<?php
/**
 * EMAILS DEL ADMINISTRADOR — TuReserva
 * Configura los correos automáticos enviados al administrador.
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Función auxiliar para renderizar Cards
// =======================================================
// (Definida en ajustes-generales.php para uso global)


// Plantillas por defecto
$default_pending_body = "Hola Admin,\n\nSe ha recibido una nueva solicitud de reserva (Pendiente de pago).\n\nID Reserva: %booking_id%\nCliente: %customer_first_name% %customer_last_name%\nLlegada: %check_in_date%\nSalida: %check_out_date%\nTotal: %booking_total_price%\n\nPor favor verifica el pago.";
$default_approved_body = "Hola Admin,\n\nUna reserva ha sido confirmada manualmente.\n\nID Reserva: %booking_id%\nCliente: %customer_first_name% %customer_last_name%\n\nDetalles:\n%reserved_rooms_details%";
$default_payment_body = "Hola Admin,\n\n¡Pago recibido! La reserva #%booking_id% ha sido confirmada automáticamente.\n\nMonto: %booking_total_price%\nPasarela: Stripe/PayPal";
$default_cancel_body = "Hola Admin,\n\nLa reserva #%booking_id% ha sido CANCELADA.\n\nCliente: %customer_first_name% %customer_last_name%";

?>

<div class="tureserva-email-config">
    
    <!-- 1. Reserva Pendiente -->
    <?php tureserva_render_email_card(
        'tureserva_email_admin_pending', 
        __('Email de Reserva Pendiente', 'tureserva'), 
        'dashicons-clock', 
        __('Se envía cuando el cliente completa el formulario pero el pago no está confirmado.', 'tureserva'),
        'Nueva solicitud de reserva: #%booking_id%',
        $default_pending_body
    ); ?>

    <!-- 2. Reserva Aprobada -->
    <?php tureserva_render_email_card(
        'tureserva_email_admin_approved', 
        __('Email de Reserva Aprobada (Manual)', 'tureserva'), 
        'dashicons-yes-alt', 
        __('Se envía cuando un administrador aprueba manualmente una reserva.', 'tureserva'),
        'Reserva confirmada: #%booking_id%',
        $default_approved_body
    ); ?>

    <!-- 3. Reserva Aprobada por Pago -->
    <?php tureserva_render_email_card(
        'tureserva_email_admin_payment', 
        __('Email de Reserva Aprobada (Pago)', 'tureserva'), 
        'dashicons-money-alt', 
        __('Se envía automáticamente cuando la pasarela confirma el pago exitoso.', 'tureserva'),
        'Pago recibido - Reserva: #%booking_id%',
        $default_payment_body
    ); ?>

    <!-- 4. Reserva Cancelada -->
    <?php tureserva_render_email_card(
        'tureserva_email_admin_cancel', 
        __('Email de Reserva Cancelada', 'tureserva'), 
        'dashicons-dismiss', 
        __('Se envía cuando la reserva es cancelada por el usuario o el administrador.', 'tureserva'),
        'Reserva cancelada: #%booking_id%',
        $default_cancel_body
    ); ?>

    <!-- TABLA DE TAGS -->
    <div class="tureserva-card">
        <h3>🏷️ Etiquetas de Correo (Email Tags)</h3>
        <p>Puedes usar estas etiquetas en el asunto y cuerpo de los correos para insertar información dinámica.</p>
        
        <style>
            .ts-tags-table { width: 100%; border-collapse: collapse; font-size: 13px; }
            .ts-tags-table th, .ts-tags-table td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
            .ts-tags-table th { background: #f9f9f9; font-weight: 600; color: #555; }
            .ts-tags-table code { background: #f0f0f1; padding: 2px 5px; border-radius: 3px; color: #d63638; }
        </style>

        <table class="ts-tags-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Etiqueta</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Título del sitio</td><td><code>%site_title%</code></td></tr>
                <tr><td>ID de reserva</td><td><code>%booking_id%</code></td></tr>
                <tr><td>Enlace de edición de reserva</td><td><code>%booking_edit_link%</code></td></tr>
                <tr><td>Precio total de reserva</td><td><code>%booking_total_price%</code></td></tr>
                <tr><td>Fecha de llegada</td><td><code>%check_in_date%</code></td></tr>
                <tr><td>Fecha de salida</td><td><code>%check_out_date%</code></td></tr>
                <tr><td>Hora de llegada</td><td><code>%check_in_time%</code></td></tr>
                <tr><td>Hora de salida</td><td><code>%check_out_time%</code></td></tr>
                <tr><td>Nombre de cliente</td><td><code>%customer_first_name%</code></td></tr>
                <tr><td>Apellido de cliente</td><td><code>%customer_last_name%</code></td></tr>
                <tr><td>Email de cliente</td><td><code>%customer_email%</code></td></tr>
                <tr><td>Teléfono de cliente</td><td><code>%customer_phone%</code></td></tr>
                <tr><td>País del cliente</td><td><code>%customer_country%</code></td></tr>
                <tr><td>Dirección del cliente</td><td><code>%customer_address1%</code></td></tr>
                <tr><td>Ciudad del cliente</td><td><code>%customer_city%</code></td></tr>
                <tr><td>Estado/condado del cliente</td><td><code>%customer_state%</code></td></tr>
                <tr><td>Código postal del cliente</td><td><code>%customer_zip%</code></td></tr>
                <tr><td>Nota de cliente</td><td><code>%customer_note%</code></td></tr>
                <tr><td>Detalles de alojamiento reservado</td><td><code>%reserved_rooms_details%</code></td></tr>
                <tr><td>Desglose de precios</td><td><code>%price_breakdown%</code></td></tr>
            </tbody>
        </table>
    </div>

</div>
