<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Configuración Global de Pagos — TuReserva
 * ==========================================================
 * Ajustes generales del sistema de pago:
 * - Usuario paga (total o depósito)
 * - Tipo y cantidad del depósito
 * - SSL seguro
 * - Páginas de confirmación / error
 * - Pasarela por defecto
 * - Tiempo de pago pendiente
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 GUARDAR CAMBIOS
// =======================================================
if (isset($_POST['tureserva_pago_guardar']) && check_admin_referer('tureserva_pago_guardar_nonce')) {
    $campos = [
        'tureserva_pago_usuario_paga',
        'tureserva_pago_tipo_deposito',
        'tureserva_pago_cantidad_deposito',
        'tureserva_pago_marco_tiempo',
        'tureserva_pago_ssl',
        'tureserva_pago_pagina_recibida',
        'tureserva_pago_pagina_fallida',
        'tureserva_pago_predeterminado',
        'tureserva_pago_tiempo_pendiente'
    ];
    foreach ($campos as $campo) {
        $valor = isset($_POST[$campo]) ? sanitize_text_field($_POST[$campo]) : '';
        update_option($campo, $valor);
    }
    echo '<div class="updated notice"><p>✅ ' . __('Ajustes de pago guardados correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔄 OBTENER VALORES ACTUALES
// =======================================================
$usuario_paga       = get_option('tureserva_pago_usuario_paga', 'total');
$tipo_deposito      = get_option('tureserva_pago_tipo_deposito', 'porciento');
$cantidad_deposito  = get_option('tureserva_pago_cantidad_deposito', '10');
$marco_tiempo       = get_option('tureserva_pago_marco_tiempo', '');
$ssl                = get_option('tureserva_pago_ssl', 0);
$pagina_recibida    = get_option('tureserva_pago_pagina_recibida', '');
$pagina_fallida     = get_option('tureserva_pago_pagina_fallida', '');
$predeterminado     = get_option('tureserva_pago_predeterminado', '');
$tiempo_pendiente   = get_option('tureserva_pago_tiempo_pendiente', '60');

// Obtener lista de páginas disponibles
$paginas = get_pages();
?>

<h2>⚙️ <?php _e('Configuración Global de Pagos', 'tureserva'); ?></h2>
<p><?php _e('Administre las opciones generales del sistema de pago, incluyendo depósitos, SSL y páginas de confirmación.', 'tureserva'); ?></p>

<form method="post">
    <?php wp_nonce_field('tureserva_pago_guardar_nonce'); ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_pago_usuario_paga"><?php _e('Usuario paga', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_pago_usuario_paga" id="tureserva_pago_usuario_paga">
                    <option value="total" <?php selected($usuario_paga, 'total'); ?>><?php _e('Total', 'tureserva'); ?></option>
                    <option value="deposito" <?php selected($usuario_paga, 'deposito'); ?>><?php _e('Depósito', 'tureserva'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_tipo_deposito"><?php _e('Tipo del depósito', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_pago_tipo_deposito" id="tureserva_pago_tipo_deposito">
                    <option value="porciento" <?php selected($tipo_deposito, 'porciento'); ?>><?php _e('Por ciento', 'tureserva'); ?></option>
                    <option value="fijo" <?php selected($tipo_deposito, 'fijo'); ?>><?php _e('Cantidad fija', 'tureserva'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_cantidad_deposito"><?php _e('Cantidad del depósito', 'tureserva'); ?></label></th>
            <td><input type="number" name="tureserva_pago_cantidad_deposito" id="tureserva_pago_cantidad_deposito" value="<?php echo esc_attr($cantidad_deposito); ?>" min="0" step="1"></td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_marco_tiempo"><?php _e('Marco de tiempo de depósito (días)', 'tureserva'); ?></label></th>
            <td>
                <input type="number" name="tureserva_pago_marco_tiempo" id="tureserva_pago_marco_tiempo" value="<?php echo esc_attr($marco_tiempo); ?>" min="0" step="1">
                <p class="description"><?php _e('Aplicar el depósito solo a reservas realizadas al menos el número de días antes del check-in. Si se deja vacío, aplica siempre.', 'tureserva'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_ssl"><?php _e('Pagos seguros', 'tureserva'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" name="tureserva_pago_ssl" value="1" <?php checked($ssl, 1); ?>>
                    <?php _e('Utilizar SSL (HTTPS) en las páginas de pago. Requiere un certificado SSL válido.', 'tureserva'); ?>
                </label>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_pagina_recibida"><?php _e('Página de reserva recibida', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_pago_pagina_recibida" id="tureserva_pago_pagina_recibida">
                    <option value=""><?php _e('— Seleccione una página —', 'tureserva'); ?></option>
                    <?php foreach ($paginas as $p) : ?>
                        <option value="<?php echo $p->ID; ?>" <?php selected($pagina_recibida, $p->ID); ?>>
                            <?php echo esc_html($p->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_pagina_fallida"><?php _e('Página de transacción fallida', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_pago_pagina_fallida" id="tureserva_pago_pagina_fallida">
                    <option value=""><?php _e('— Seleccione una página —', 'tureserva'); ?></option>
                    <?php foreach ($paginas as $p) : ?>
                        <option value="<?php echo $p->ID; ?>" <?php selected($pagina_fallida, $p->ID); ?>>
                            <?php echo esc_html($p->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_predeterminado"><?php _e('Pasarela de pago predeterminada', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_pago_predeterminado" id="tureserva_pago_predeterminado">
                    <option value=""><?php _e('— Seleccione una opción —', 'tureserva'); ?></option>
                    <option value="stripe" <?php selected($predeterminado, 'stripe'); ?>>Stripe</option>
                    <option value="paypal" <?php selected($predeterminado, 'paypal'); ?>>PayPal</option>
                    <option value="transferencia" <?php selected($predeterminado, 'transferencia'); ?>>Transferencia</option>
                    <option value="manual" <?php selected($predeterminado, 'manual'); ?>>Manual / Efectivo</option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="tureserva_pago_tiempo_pendiente"><?php _e('Tiempo de pago pendiente (minutos)', 'tureserva'); ?></label></th>
            <td>
                <input type="number" name="tureserva_pago_tiempo_pendiente" id="tureserva_pago_tiempo_pendiente" value="<?php echo esc_attr($tiempo_pendiente); ?>" min="1" step="1">
                <p class="description"><?php _e('Periodo en minutos que un usuario tiene para completar el pago antes de que la reserva se marque como abandonada.', 'tureserva'); ?></p>
            </td>
        </tr>
    </table>

    <?php submit_button(__('Guardar cambios', 'tureserva'), 'primary', 'tureserva_pago_guardar'); ?>
</form>
