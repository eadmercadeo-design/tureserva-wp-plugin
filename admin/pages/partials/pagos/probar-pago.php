<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Probar Pago — TuReserva
 * ==========================================================
 * Permite activar y personalizar un método de pago de prueba
 * similar al modo sandbox de MotoPress.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR AJUSTES
// =======================================================
if (isset($_POST['tureserva_test_pago_guardar']) && check_admin_referer('tureserva_test_pago_guardar_nonce')) {
    update_option('tureserva_test_pago_activo', isset($_POST['tureserva_test_pago_activo']) ? 1 : 0);
    update_option('tureserva_test_pago_titulo', sanitize_text_field($_POST['tureserva_test_pago_titulo']));
    update_option('tureserva_test_pago_descripcion', sanitize_textarea_field($_POST['tureserva_test_pago_descripcion']));
    update_option('tureserva_test_pago_instrucciones', sanitize_textarea_field($_POST['tureserva_test_pago_instrucciones']));

    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de "Probar pago" guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$activo = get_option('tureserva_test_pago_activo', 0);
$titulo = get_option('tureserva_test_pago_titulo', __('Probar pago', 'tureserva'));
$descripcion = get_option('tureserva_test_pago_descripcion', '');
$instrucciones = get_option('tureserva_test_pago_instrucciones', '');
?>

<h2>🧪 <?php _e('Probar pago', 'tureserva'); ?></h2>
<p><?php _e('Permite realizar pruebas de flujo de pago sin procesar transacciones reales.', 'tureserva'); ?></p>

<form method="post">
    <?php wp_nonce_field('tureserva_test_pago_guardar_nonce'); ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_test_pago_activo"><?php _e('Activar “Probar pago”', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_test_pago_activo" name="tureserva_test_pago_activo" value="1" <?php checked($activo, 1); ?>>
                <span class="description"><?php _e('Permite usar este método de pago para pruebas internas.', 'tureserva'); ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_test_pago_titulo"><?php _e('Título', 'tureserva'); ?></label></th>
            <td>
                <input type="text" id="tureserva_test_pago_titulo" name="tureserva_test_pago_titulo" value="<?php echo esc_attr($titulo); ?>" class="regular-text">
                <p class="description"><?php _e('El título del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_test_pago_descripcion"><?php _e('Descripción', 'tureserva'); ?></label></th>
            <td>
                <textarea id="tureserva_test_pago_descripcion" name="tureserva_test_pago_descripcion" class="large-text" rows="2"><?php echo esc_textarea($descripcion); ?></textarea>
                <p class="description"><?php _e('La descripción del método de pago que el cliente verá en su sitio web.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_test_pago_instrucciones"><?php _e('Instrucciones', 'tureserva'); ?></label></th>
            <td>
                <textarea id="tureserva_test_pago_instrucciones" name="tureserva_test_pago_instrucciones" class="large-text" rows="3"><?php echo esc_textarea($instrucciones); ?></textarea>
                <p class="description"><?php _e('Instrucciones para el cliente de cómo completar el pago.', 'tureserva'); ?></p>
            </td>
        </tr>
    </table>

    <?php submit_button(__('Guardar cambios', 'tureserva'), 'primary', 'tureserva_test_pago_guardar'); ?>
</form>
