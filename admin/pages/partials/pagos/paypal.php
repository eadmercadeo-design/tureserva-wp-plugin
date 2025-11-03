<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Pago con PayPal — TuReserva
 * ==========================================================
 * Permite configurar el método de pago PayPal, incluyendo
 * activación, modo de prueba, correo comercial e IPN.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR AJUSTES
// =======================================================
if (isset($_POST['tureserva_paypal_guardar']) && check_admin_referer('tureserva_paypal_guardar_nonce')) {
    update_option('tureserva_paypal_activo', isset($_POST['tureserva_paypal_activo']) ? 1 : 0);
    update_option('tureserva_paypal_sandbox', isset($_POST['tureserva_paypal_sandbox']) ? 1 : 0);
    update_option('tureserva_paypal_titulo', sanitize_text_field($_POST['tureserva_paypal_titulo']));
    update_option('tureserva_paypal_descripcion', sanitize_textarea_field($_POST['tureserva_paypal_descripcion']));
    update_option('tureserva_paypal_email', sanitize_email($_POST['tureserva_paypal_email']));
    update_option('tureserva_paypal_ipn_disable', isset($_POST['tureserva_paypal_ipn_disable']) ? 1 : 0);

    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de PayPal guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$activo        = get_option('tureserva_paypal_activo', 0);
$sandbox       = get_option('tureserva_paypal_sandbox', 1);
$titulo        = get_option('tureserva_paypal_titulo', __('PayPal', 'tureserva'));
$descripcion   = get_option('tureserva_paypal_descripcion', __('Pagar a través de PayPal.', 'tureserva'));
$email         = get_option('tureserva_paypal_email', '');
$ipn_disable   = get_option('tureserva_paypal_ipn_disable', 0);
?>

<h2>💰 <?php _e('Pago con PayPal', 'tureserva'); ?></h2>
<p><?php _e('Configure su cuenta de PayPal para recibir pagos seguros. Puede habilitar el modo Sandbox para pruebas.', 'tureserva'); ?></p>

<form method="post">
    <?php wp_nonce_field('tureserva_paypal_guardar_nonce'); ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_paypal_activo"><?php _e('Activar “PayPal”', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_paypal_activo" name="tureserva_paypal_activo" value="1" <?php checked($activo, 1); ?>>
                <span class="description"><?php _e('Habilita la pasarela PayPal para aceptar pagos.', 'tureserva'); ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_paypal_sandbox"><?php _e('Modo de prueba', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_paypal_sandbox" name="tureserva_paypal_sandbox" value="1" <?php checked($sandbox, 1); ?>>
                <span class="description"><?php _e('Activar el modo de Sandbox para pruebas.', 'tureserva'); ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_paypal_titulo"><?php _e('Título', 'tureserva'); ?></label></th>
            <td>
                <input type="text" id="tureserva_paypal_titulo" name="tureserva_paypal_titulo" value="<?php echo esc_attr($titulo); ?>" class="regular-text">
                <p class="description"><?php _e('El título del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_paypal_descripcion"><?php _e('Descripción', 'tureserva'); ?></label></th>
            <td>
                <textarea id="tureserva_paypal_descripcion" name="tureserva_paypal_descripcion" class="large-text" rows="2"><?php echo esc_textarea($descripcion); ?></textarea>
                <p class="description"><?php _e('La descripción del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_paypal_email"><?php _e('Email comercial de PayPal', 'tureserva'); ?></label></th>
            <td>
                <input type="email" id="tureserva_paypal_email" name="tureserva_paypal_email" value="<?php echo esc_attr($email); ?>" class="regular-text">
                <p class="description"><?php _e('Correo electrónico de la cuenta comercial de PayPal donde se recibirán los pagos.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_paypal_ipn_disable"><?php _e('Desactivar la verificación IPN', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_paypal_ipn_disable" name="tureserva_paypal_ipn_disable" value="1" <?php checked($ipn_disable, 1); ?>>
                <p class="description">
                    <?php _e('Si se activa, se omite la verificación IPN. Recomendado solo para pruebas locales o entornos sin HTTPS.', 'tureserva'); ?><br>
                    <?php _e('En producción, mantenga esta opción desactivada para validar notificaciones automáticas de pago.', 'tureserva'); ?>
                </p>
            </td>
        </tr>
    </table>

    <?php submit_button(__('Guardar cambios', 'tureserva'), 'primary', 'tureserva_paypal_guardar'); ?>
</form>
