<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Pago con Stripe — TuReserva
 * ==========================================================
 * Configuración de la pasarela Stripe (API Keys, modo sandbox, etc.)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR AJUSTES
// =======================================================
if (isset($_POST['tureserva_stripe_guardar']) && check_admin_referer('tureserva_stripe_guardar_nonce')) {
    update_option('tureserva_stripe_activo', isset($_POST['tureserva_stripe_activo']) ? 1 : 0);
    update_option('tureserva_stripe_sandbox', isset($_POST['tureserva_stripe_sandbox']) ? 1 : 0);
    update_option('tureserva_stripe_titulo', sanitize_text_field($_POST['tureserva_stripe_titulo']));
    update_option('tureserva_stripe_descripcion', sanitize_textarea_field($_POST['tureserva_stripe_descripcion']));
    update_option('tureserva_stripe_public_key', sanitize_text_field($_POST['tureserva_stripe_public_key']));
    update_option('tureserva_stripe_secret_key', sanitize_text_field($_POST['tureserva_stripe_secret_key']));
    update_option('tureserva_stripe_webhook_secret', sanitize_text_field($_POST['tureserva_stripe_webhook_secret']));
    update_option('tureserva_stripe_metodos', isset($_POST['tureserva_stripe_metodos']) ? array_map('sanitize_text_field', $_POST['tureserva_stripe_metodos']) : []);
    update_option('tureserva_stripe_localizacion', sanitize_text_field($_POST['tureserva_stripe_localizacion']));

    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de Stripe guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$activo         = get_option('tureserva_stripe_activo', 0);
$sandbox        = get_option('tureserva_stripe_sandbox', 1);
$titulo         = get_option('tureserva_stripe_titulo', __('Pagar con tarjeta (Stripe)', 'tureserva'));
$descripcion    = get_option('tureserva_stripe_descripcion', __('Pague con su tarjeta de crédito vía Stripe.', 'tureserva'));
$public_key     = get_option('tureserva_stripe_public_key', '');
$secret_key     = get_option('tureserva_stripe_secret_key', '');
$webhook_secret = get_option('tureserva_stripe_webhook_secret', '');
$metodos        = get_option('tureserva_stripe_metodos', ['card']);
$localizacion   = get_option('tureserva_stripe_localizacion', 'auto');
?>

<h2>💳 <?php _e('Pago con tarjeta (Stripe)', 'tureserva'); ?></h2>
<p><?php _e('Configure su cuenta de Stripe para aceptar pagos con tarjeta y otros métodos compatibles.', 'tureserva'); ?></p>

<!-- ⚠️ NO abrir un nuevo <form> aquí -->
<?php wp_nonce_field('tureserva_stripe_guardar_nonce'); ?>

<table class="form-table">
    <tr>
        <th><label for="tureserva_stripe_activo"><?php _e('Activar “Pagar con tarjeta (Stripe)”', 'tureserva'); ?></label></th>
        <td>
            <input type="checkbox" id="tureserva_stripe_activo" name="tureserva_stripe_activo" value="1" <?php checked($activo, 1); ?>>
            <p class="description">
                <?php _e('Habilita la pasarela Stripe para aceptar pagos con tarjeta.', 'tureserva'); ?><br>
                <?php _e('La opción Force Secure Checkout está desactivada. Active SSL para usar Stripe en modo real.', 'tureserva'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_sandbox"><?php _e('Modo de prueba', 'tureserva'); ?></label></th>
        <td>
            <input type="checkbox" id="tureserva_stripe_sandbox" name="tureserva_stripe_sandbox" value="1" <?php checked($sandbox, 1); ?>>
            <p class="description"><?php _e('Active para utilizar el entorno de pruebas de Stripe (Sandbox).', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_titulo"><?php _e('Título', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_stripe_titulo" value="<?php echo esc_attr($titulo); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_descripcion"><?php _e('Descripción', 'tureserva'); ?></label></th>
        <td><textarea name="tureserva_stripe_descripcion" class="large-text" rows="3"><?php echo esc_textarea($descripcion); ?></textarea></td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_public_key"><?php _e('Clave pública', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_stripe_public_key" value="<?php echo esc_attr($public_key); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_secret_key"><?php _e('Clave secreta', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_stripe_secret_key" value="<?php echo esc_attr($secret_key); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_webhook_secret"><?php _e('Secreto de webhook', 'tureserva'); ?></label></th>
        <td><input type="text" name="tureserva_stripe_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><?php _e('Formas de pago', 'tureserva'); ?></th>
        <td>
            <label><input type="checkbox" name="tureserva_stripe_metodos[]" value="card" <?php checked(in_array('card', $metodos)); ?>> <?php _e('Pagos con tarjeta', 'tureserva'); ?></label><br>
            <label><input type="checkbox" name="tureserva_stripe_metodos[]" value="ideal" <?php checked(in_array('ideal', $metodos)); ?>> iDEAL</label><br>
            <label><input type="checkbox" name="tureserva_stripe_metodos[]" value="giropay" <?php checked(in_array('giropay', $metodos)); ?>> Giropay</label><br>
            <label><input type="checkbox" name="tureserva_stripe_metodos[]" value="sepa" <?php checked(in_array('sepa', $metodos)); ?>> Débito directo SEPA</label><br>
            <label><input type="checkbox" name="tureserva_stripe_metodos[]" value="klarna" <?php checked(in_array('klarna', $metodos)); ?>> Klarna</label>
        </td>
    </tr>

    <tr>
        <th><label for="tureserva_stripe_localizacion"><?php _e('Página de pago localizada', 'tureserva'); ?></label></th>
        <td>
            <select name="tureserva_stripe_localizacion">
                <option value="auto" <?php selected($localizacion, 'auto'); ?>><?php _e('Automático', 'tureserva'); ?></option>
                <option value="en" <?php selected($localizacion, 'en'); ?>>English</option>
                <option value="es" <?php selected($localizacion, 'es'); ?>>Español</option>
                <option value="fr" <?php selected($localizacion, 'fr'); ?>>Français</option>
            </select>
            <p class="description"><?php _e('Muestra la página de pago en el idioma preferido del usuario.', 'tureserva'); ?></p>
        </td>
    </tr>
</table>

<!-- ⚠️ No cierres el form aquí; lo maneja el archivo principal -->
