<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Pago por Transferencia Bancaria — TuReserva
 * ==========================================================
 * Permite configurar los datos de pago por transferencia.
 * Basado en la referencia de MotoPress Hotel Booking.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR AJUSTES
// =======================================================
if (isset($_POST['tureserva_transfer_guardar']) && check_admin_referer('tureserva_transfer_guardar_nonce')) {
    update_option('tureserva_transfer_activo', isset($_POST['tureserva_transfer_activo']) ? 1 : 0);
    update_option('tureserva_transfer_titulo', sanitize_text_field($_POST['tureserva_transfer_titulo']));
    update_option('tureserva_transfer_descripcion', sanitize_textarea_field($_POST['tureserva_transfer_descripcion']));
    update_option('tureserva_transfer_instrucciones', sanitize_textarea_field($_POST['tureserva_transfer_instrucciones']));
    update_option('tureserva_transfer_auto_abandono', isset($_POST['tureserva_transfer_auto_abandono']) ? 1 : 0);
    update_option('tureserva_transfer_tiempo', intval($_POST['tureserva_transfer_tiempo']));
    update_option('tureserva_transfer_notificacion_activa', isset($_POST['tureserva_transfer_notificacion_activa']) ? 1 : 0);
    update_option('tureserva_transfer_tema', sanitize_text_field($_POST['tureserva_transfer_tema']));
    update_option('tureserva_transfer_cabecera', sanitize_text_field($_POST['tureserva_transfer_cabecera']));
    update_option('tureserva_transfer_plantilla', wp_kses_post($_POST['tureserva_transfer_plantilla']));

    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de Transferencia guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$activo          = get_option('tureserva_transfer_activo', 0);
$titulo          = get_option('tureserva_transfer_titulo', __('Transferencia bancaria directa', 'tureserva'));
$descripcion     = get_option('tureserva_transfer_descripcion', __('Realice el pago directamente en nuestra cuenta bancaria. Use el ID de reserva como concepto de pago.', 'tureserva'));
$instrucciones   = get_option('tureserva_transfer_instrucciones', '');
$auto_abandono   = get_option('tureserva_transfer_auto_abandono', 0);
$tiempo          = get_option('tureserva_transfer_tiempo', 120);
$notificacion    = get_option('tureserva_transfer_notificacion_activa', 0);
$tema            = get_option('tureserva_transfer_tema', __('Pague su reserva #[booking_id]', 'tureserva'));
$cabecera        = get_option('tureserva_transfer_cabecera', __('Pago de su reserva', 'tureserva'));
$plantilla       = get_option('tureserva_transfer_plantilla', __('Estimado/a [customer_first_name] [customer_last_name], hemos recibido su solicitud de reserva.', 'tureserva'));
?>

<h2>🏦 <?php _e('Pago por Transferencia Bancaria', 'tureserva'); ?></h2>
<p><?php _e('Configure las instrucciones para los clientes que seleccionen pagar por transferencia bancaria.', 'tureserva'); ?></p>

<?php wp_nonce_field('tureserva_transfer_guardar_nonce'); ?>

<table class="form-table">
    <tr>
        <th><label for="tureserva_transfer_activo"><?php _e('Activar “Transferencia bancaria directa”', 'tureserva'); ?></label></th>
        <td><input type="checkbox" id="tureserva_transfer_activo" name="tureserva_transfer_activo" value="1" <?php checked($activo, 1); ?>></td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_titulo"><?php _e('Título', 'tureserva'); ?></label></th>
        <td>
            <input type="text" name="tureserva_transfer_titulo" value="<?php echo esc_attr($titulo); ?>" class="regular-text">
            <p class="description"><?php _e('El título del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_descripcion"><?php _e('Descripción', 'tureserva'); ?></label></th>
        <td>
            <textarea name="tureserva_transfer_descripcion" rows="2" class="large-text"><?php echo esc_textarea($descripcion); ?></textarea>
            <p class="description"><?php _e('La descripción del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_instrucciones"><?php _e('Instrucciones', 'tureserva'); ?></label></th>
        <td>
            <textarea name="tureserva_transfer_instrucciones" rows="3" class="large-text"><?php echo esc_textarea($instrucciones); ?></textarea>
            <p class="description"><?php _e('Instrucciones para el cliente sobre cómo completar el pago.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_auto_abandono"><?php _e('Abandono automático de reserva', 'tureserva'); ?></label></th>
        <td>
            <input type="checkbox" name="tureserva_transfer_auto_abandono" value="1" <?php checked($auto_abandono, 1); ?>>
            <p class="description"><?php _e('Cancelar automáticamente las reservas no pagadas después de un tiempo determinado.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_tiempo"><?php _e('Tiempo de pago pendiente (minutos)', 'tureserva'); ?></label></th>
        <td>
            <input type="number" name="tureserva_transfer_tiempo" value="<?php echo esc_attr($tiempo); ?>" class="small-text">
            <p class="description"><?php _e('Período de tiempo en minutos que un usuario tiene para completar su pago antes de que la reserva se marque como abandonada.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr><td colspan="2"><hr></td></tr>

    <tr>
        <th><?php _e('Email de Instrucciones de Pago', 'tureserva'); ?></th>
        <td><p class="description"><?php _e('Correo electrónico que será enviado al cliente después de que haga una reserva.', 'tureserva'); ?></p></td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_notificacion_activa"><?php _e('Desactivar notificación', 'tureserva'); ?></label></th>
        <td><input type="checkbox" name="tureserva_transfer_notificacion_activa" value="1" <?php checked($notificacion, 1); ?>></td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_tema"><?php _e('Asunto del correo', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_transfer_tema" value="<?php echo esc_attr($tema); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_cabecera"><?php _e('Cabecera', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_transfer_cabecera" value="<?php echo esc_attr($cabecera); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="tureserva_transfer_plantilla"><?php _e('Plantilla de email', 'tureserva'); ?></label></th>
        <td>
            <?php
            wp_editor(
                $plantilla,
                'tureserva_transfer_plantilla',
                [
                    'textarea_name' => 'tureserva_transfer_plantilla',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny' => false,
                    'quicktags' => true,
                ]
            );
            ?>
            <p class="description"><?php _e('Use etiquetas como [booking_id], [customer_first_name], [checkin], [checkout] para personalizar el mensaje.', 'tureserva'); ?></p>
        </td>
    </tr>
</table>
