<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Integración con WooCommerce — TuReserva
 * ==========================================================
 * Permite habilitar la conexión entre TuReserva y WooCommerce.
 * Cuando se activa, las reservas se sincronizan como pedidos,
 * usando las pasarelas activas de WooCommerce (Stripe, PayPal, etc.)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR AJUSTES
// =======================================================
if (isset($_POST['tureserva_woo_guardar']) && check_admin_referer('tureserva_woo_guardar_nonce')) {
    update_option('tureserva_woo_enable', isset($_POST['tureserva_woo_enable']) ? 1 : 0);
    update_option('tureserva_woo_auto_sync', isset($_POST['tureserva_woo_auto_sync']) ? 1 : 0);
    update_option('tureserva_woo_order_prefix', sanitize_text_field($_POST['tureserva_woo_order_prefix']));
    update_option('tureserva_woo_status_map', sanitize_text_field($_POST['tureserva_woo_status_map']));
    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de WooCommerce guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$activo        = get_option('tureserva_woo_enable', 0);
$auto_sync     = get_option('tureserva_woo_auto_sync', 1);
$prefix        = get_option('tureserva_woo_order_prefix', 'RES-');
$status_map    = get_option('tureserva_woo_status_map', 'completed');
?>

<h2>🛒 <?php _e('Integración con WooCommerce', 'tureserva'); ?></h2>

<?php if (!is_plugin_active('woocommerce/woocommerce.php')) : ?>
    <div class="notice notice-error"><p>
        ⚠️ <?php _e('WooCommerce no está activo. Active el plugin para usar esta integración.', 'tureserva'); ?>
    </p></div>
<?php else : ?>

    <?php wp_nonce_field('tureserva_woo_guardar_nonce'); ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_woo_enable"><?php _e('Activar integración', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_woo_enable" name="tureserva_woo_enable" value="1" <?php checked($activo, 1); ?>>
                <p class="description"><?php _e('Permite procesar las reservas a través del checkout de WooCommerce usando sus pasarelas activas.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_woo_auto_sync"><?php _e('Sincronizar automáticamente', 'tureserva'); ?></label></th>
            <td>
                <input type="checkbox" id="tureserva_woo_auto_sync" name="tureserva_woo_auto_sync" value="1" <?php checked($auto_sync, 1); ?>>
                <p class="description"><?php _e('Crea y sincroniza automáticamente un pedido en WooCommerce cuando se genera una reserva.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_woo_order_prefix"><?php _e('Prefijo para pedidos', 'tureserva'); ?></label></th>
            <td>
                <input type="text" name="tureserva_woo_order_prefix" value="<?php echo esc_attr($prefix); ?>" class="small-text">
                <p class="description"><?php _e('Se añadirá delante del ID de reserva al crear un pedido en WooCommerce (ejemplo: RES-1023).', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_woo_status_map"><?php _e('Estado del pedido tras pago', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_woo_status_map">
                    <option value="completed" <?php selected($status_map, 'completed'); ?>><?php _e('Completado', 'tureserva'); ?></option>
                    <option value="processing" <?php selected($status_map, 'processing'); ?>><?php _e('Procesando', 'tureserva'); ?></option>
                    <option value="on-hold" <?php selected($status_map, 'on-hold'); ?>><?php _e('En espera', 'tureserva'); ?></option>
                    <option value="pending" <?php selected($status_map, 'pending'); ?>><?php _e('Pendiente', 'tureserva'); ?></option>
                </select>
                <p class="description"><?php _e('Estado que se asignará al pedido WooCommerce cuando el pago sea confirmado.', 'tureserva'); ?></p>
            </td>
        </tr>
    </table>

<?php endif; ?>
