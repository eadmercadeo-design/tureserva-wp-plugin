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
// 🔢 Generar código único de pago
// =======================================================
function tureserva_generar_codigo_pago($post_id = 0) {
    $ultimo = get_posts([
        'post_type' => 'tureserva_pagos',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $numero = 1;
    if ($ultimo && isset($ultimo[0])) {
        $ultimo_codigo = get_post_meta($ultimo[0]->ID, '_tureserva_pago_id', true);
        if ($ultimo_codigo && preg_match('/PG-(\d+)/', $ultimo_codigo, $matches)) {
            $numero = intval($matches[1]) + 1;
        }
    }
    return 'PG-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
}

// =======================================================
// 🧾 Renderizar Detalles del Pago
// =======================================================
function tureserva_render_metabox_pago_detalles($post) {
    $meta = get_post_meta($post->ID);
    ?>
    <table class="form-table">
        <tr>
    <th><label>Identidad</label></th>
    <td>
        <?php
        $codigo_pago = $meta['_tureserva_pago_id'][0] ?? '';
        if (empty($codigo_pago)) {
            $codigo_pago = tureserva_generar_codigo_pago($post->ID);
        }
        ?>
        <input type="text" name="_tureserva_pago_id" value="<?php echo esc_attr($codigo_pago); ?>" readonly />
    </td>
</tr>
       <tr>
    <th><label><?php _e('Cantidad', 'tureserva'); ?></label></th>
    <td>
        <input 
            type="text"
            name="_tureserva_pago_monto"
            id="tureserva_pago_monto"
            value="<?php echo esc_attr($meta['_tureserva_pago_monto'][0] ?? ''); ?>"
            class="small-text"
            style="width:150px;text-align:right;"
            placeholder="0 o 0.00"
        />
        <p class="description"><?php _e('Monto total del pago (puede incluir o no decimales, use punto como separador).', 'tureserva'); ?></p>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campo = document.getElementById('tureserva_pago_monto');
            if (!campo) return;

            // ✅ Bloquea letras, comas y notación científica
            campo.addEventListener('keydown', (e) => {
                const invalid = ['e', 'E', '+', '-', ',', ' '];
                if (invalid.includes(e.key)) e.preventDefault();
            });

            // ✅ Limpieza mínima al salir del campo
            campo.addEventListener('blur', () => {
                let valor = campo.value.trim();

                // Si está vacío → deja vacío
                if (valor === '') return;

                // Reemplaza comas por punto
                valor = valor.replace(',', '.');

                // Permite enteros o decimales válidos
                if (!/^\d+(\.\d{1,2})?$/.test(valor)) {
                    alert('Por favor ingrese un valor válido (solo números y punto decimal).');
                    campo.value = '';
                    campo.focus();
                    return;
                }

                // Quita ceros innecesarios (ej: 100.00 → 100 o 100.5 → 100.5)
                const numero = parseFloat(valor);
                campo.value = numero % 1 === 0 ? numero.toFixed(0) : numero.toFixed(2);
            });
        });
        </script>
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
