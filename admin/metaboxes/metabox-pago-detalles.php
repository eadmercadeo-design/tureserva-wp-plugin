<?php
/**
 * ==========================================================
 * METABOX: Detalles del Pago — TuReserva
 * ==========================================================
 * Versión inspirada en MotoPress.
 * - Incluye Detalles del Pago + Información de Facturación
 * - Guarda automáticamente los metadatos personalizados
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Registrar Metabox
// =======================================================
add_action('add_meta_boxes', function() {
    add_meta_box(
        'tureserva_pago_detalles',
        __('Detalles de pago', 'tureserva'),
        'tureserva_render_metabox_pago_detalles',
        'tureserva_pagos',
        'normal',
        'high'
    );

    add_meta_box(
        'tureserva_pago_facturacion',
        __('Información de facturación', 'tureserva'),
        'tureserva_render_metabox_pago_facturacion',
        'tureserva_pagos',
        'normal',
        'default'
    );
});

// =======================================================
// 🧾 Renderizar Detalles del Pago
// =======================================================
function tureserva_render_metabox_pago_detalles($post) {
    $meta = get_post_meta($post->ID);
    ?>
    <table class="form-table">
        <tr><th><label>Identidad</label></th>
            <td><input type="text" name="_tureserva_pago_id" value="<?php echo esc_attr($meta['_tureserva_pago_id'][0] ?? ''); ?>" readonly /></td>
        </tr>

        <tr><th><label>Pasarela</label></th>
            <td>
                <select name="_tureserva_pasarela">
                    <?php
                    $pasarela = $meta['_tureserva_pasarela'][0] ?? '';
                    $options = ['Stripe', 'PayPal', 'Manual'];
                    foreach ($options as $opt) {
                        printf('<option value="%s"%s>%s</option>', $opt, selected($opt, $pasarela, false), $opt);
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr><th><label>Modo de pasarela</label></th>
            <td>
                <select name="_tureserva_modo_pasarela">
                    <option value="sandbox" <?php selected(($meta['_tureserva_modo_pasarela'][0] ?? ''), 'sandbox'); ?>>Sandbox</option>
                    <option value="live" <?php selected(($meta['_tureserva_modo_pasarela'][0] ?? ''), 'live'); ?>>Producción</option>
                </select>
            </td>
        </tr>

        <tr><th><label>Cantidad</label></th>
            <td><input type="number" step="0.01" name="_tureserva_pago_monto" value="<?php echo esc_attr($meta['_tureserva_pago_monto'][0] ?? '0'); ?>" /></td>
        </tr>

        <tr><th><label>Moneda</label></th>
            <td>
                <select name="_tureserva_pago_moneda">
                    <?php
                    $moneda = $meta['_tureserva_pago_moneda'][0] ?? 'USD';
                    $opciones = [
                        'USD' => 'Dólar estadounidense ($)',
                        'EUR' => 'Euro (€)',
                        'COP' => 'Peso de Colombia ($)',
                        'PAB' => 'Balboa (B/.)'
                    ];
                    foreach ($opciones as $code => $label) {
                        printf('<option value="%s"%s>%s</option>', $code, selected($code, $moneda, false), $label);
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr><th><label>Tipo de pago</label></th>
            <td><input type="text" name="_tureserva_tipo_pago" value="<?php echo esc_attr($meta['_tureserva_tipo_pago'][0] ?? ''); ?>" /></td>
        </tr>

        <tr><th><label>ID de transacción</label></th>
            <td><input type="text" name="_tureserva_transaccion_id" value="<?php echo esc_attr($meta['_tureserva_transaccion_id'][0] ?? ''); ?>" /></td>
        </tr>

        <tr><th><label>ID de reserva</label></th>
            <td>
                <select name="_tureserva_reserva_id">
                    <option value="">— Elegir —</option>
                    <?php
                    $reserva_actual = $meta['_tureserva_reserva_id'][0] ?? '';
                    $reservas = get_posts(['post_type' => 'reserva', 'posts_per_page' => -1]);
                    foreach ($reservas as $reserva) {
                        printf(
                            '<option value="%s"%s>%s (#%d)</option>',
                            $reserva->ID,
                            selected($reserva_actual, $reserva->ID, false),
                            esc_html($reserva->post_title),
                            $reserva->ID
                        );
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

// =======================================================
// 💳 Renderizar Información de Facturación
// =======================================================
function tureserva_render_metabox_pago_facturacion($post) {
    $fields = [
        'nombre' => 'Nombre',
        'apellido' => 'Apellido',
        'email' => 'Email',
        'telefono' => 'Teléfono',
        'pais' => 'País',
        'direccion1' => 'Dirección 1',
        'direccion2' => 'Dirección 2',
        'ciudad' => 'Ciudad',
        'estado' => 'Estado/Condado',
        'codigo_postal' => 'Código postal'
    ];

    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, "_tureserva_fact_$key", true);
        printf(
            '<tr><th><label>%s</label></th><td><input type="text" name="_tureserva_fact_%s" value="%s" class="regular-text" /></td></tr>',
            esc_html($label),
            esc_attr($key),
            esc_attr($value)
        );
    }
    echo '</table>';
}

// =======================================================
// 💾 Guardar metadatos al guardar el post
// =======================================================
add_action('save_post_tureserva_pagos', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $campos = [
        '_tureserva_pago_id',
        '_tureserva_pasarela',
        '_tureserva_modo_pasarela',
        '_tureserva_pago_monto',
        '_tureserva_pago_moneda',
        '_tureserva_tipo_pago',
        '_tureserva_transaccion_id',
        '_tureserva_reserva_id'
    ];

    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    $facturacion = ['nombre', 'apellido', 'email', 'telefono', 'pais', 'direccion1', 'direccion2', 'ciudad', 'estado', 'codigo_postal'];
    foreach ($facturacion as $campo) {
        $key = "_tureserva_fact_$campo";
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }
});
