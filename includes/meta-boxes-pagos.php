<?php
/**
 * ==========================================================
 * META BOXES – PAGOS
 * ==========================================================
 * Permite asociar cada pago con una reserva y
 * registrar información básica del método y estado.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX
// ==========================================================
function tureserva_add_pagos_metaboxes() {
    add_meta_box(
        'tureserva_pago_detalles',
        'Detalles del Pago',
        'tureserva_render_pagos_metabox',
        'pagos',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_pagos_metaboxes');

// ==========================================================
// 💳 FORMULARIO DE PAGO
// ==========================================================
function tureserva_render_pagos_metabox($post) {
    $reserva = get_post_meta($post->ID, '_tureserva_pago_reserva', true);
    $metodo  = get_post_meta($post->ID, '_tureserva_pago_metodo', true);
    $monto   = get_post_meta($post->ID, '_tureserva_pago_monto', true);
    $estado  = get_post_meta($post->ID, '_tureserva_pago_estado', true);

    wp_nonce_field('tureserva_save_pago', 'tureserva_pago_nonce');
    ?>

    <div class="tureserva-field">
        <label class="tureserva-label">Reserva asociada</label>
        <select name="tureserva_pago_reserva">
            <?php
            $reservas = get_posts(array('post_type' => 'reservas', 'numberposts' => -1));
            foreach ($reservas as $r) {
                echo '<option value="' . esc_attr($r->ID) . '"' . selected($reserva, $r->ID, false) . '>' . esc_html($r->post_title) . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Método de pago</label>
        <select name="tureserva_pago_metodo">
            <option value="stripe" <?php selected($metodo, 'stripe'); ?>>Stripe</option>
            <option value="paypal" <?php selected($metodo, 'paypal'); ?>>PayPal</option>
            <option value="efectivo" <?php selected($metodo, 'efectivo'); ?>>Efectivo</option>
        </select>
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Monto (USD)</label>
        <input type="number" step="0.01" name="tureserva_pago_monto" value="<?php echo esc_attr($monto); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Estado del pago</label>
        <select name="tureserva_pago_estado">
            <?php
            $estados = ['pendiente' => 'Pendiente', 'completado' => 'Completado', 'fallido' => 'Fallido'];
            foreach ($estados as $valor => $label) {
                echo '<option value="' . esc_attr($valor) . '"' . selected($estado, $valor, false) . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
    </div>

    <?php
}

// ==========================================================
// 💾 GUARDAR DATOS
// ==========================================================
function tureserva_save_pagos_metabox($post_id) {
    if (!isset($_POST['tureserva_pago_nonce']) || !wp_verify_nonce($_POST['tureserva_pago_nonce'], 'tureserva_save_pago')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'tureserva_pago_reserva',
        'tureserva_pago_metodo',
        'tureserva_pago_monto',
        'tureserva_pago_estado',
    ];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            update_post_meta($post_id, '_' . $f, sanitize_text_field($_POST[$f]));
        }
    }
}
add_action('save_post_pagos', 'tureserva_save_pagos_metabox');
