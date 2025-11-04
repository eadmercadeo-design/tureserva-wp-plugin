<?php
/**
 * ==========================================================
 * METABOX: Panel lateral de Pago — TuReserva
 * ==========================================================
 * Incluye:
 * - Selector de estado
 * - Fechas
 * - Logs
 * - Botón “Cobrar con tarjeta (Stripe)”
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧾 Registrar Metaboxes Laterales
// =======================================================
add_action('add_meta_boxes', function() {
    add_meta_box(
        'tureserva_pago_sidebar',
        __('Actualizar pago', 'tureserva'),
        'tureserva_render_pago_sidebar',
        'tureserva_pagos',
        'side',
        'high'
    );

    add_meta_box(
        'tureserva_pago_logs',
        __('Registros', 'tureserva'),
        'tureserva_render_pago_logs',
        'tureserva_pagos',
        'side',
        'default'
    );
});

// =======================================================
// 💳 Renderizar “Actualizar pago”
// =======================================================
function tureserva_render_pago_sidebar($post) {
    $estado = get_post_meta($post->ID, '_tureserva_pago_estado', true) ?: 'pendiente';
    $monto  = get_post_meta($post->ID, '_tureserva_pago_monto', true);
    $moneda = get_post_meta($post->ID, '_tureserva_pago_moneda', true) ?: 'USD';
    $reserva_id = get_post_meta($post->ID, '_tureserva_reserva_id', true);
    $fecha_creado = get_the_date('', $post->ID);
    $fecha_modificado = get_the_modified_date('', $post->ID);

    ?>
    <p>
        <label for="_tureserva_pago_estado"><strong><?php _e('Estado del pago:', 'tureserva'); ?></strong></label><br>
        <select name="_tureserva_pago_estado" id="_tureserva_pago_estado" style="width:100%;">
            <option value="pendiente" <?php selected($estado, 'pendiente'); ?>><?php _e('Pendiente', 'tureserva'); ?></option>
            <option value="completado" <?php selected($estado, 'completado'); ?>><?php _e('Completado', 'tureserva'); ?></option>
            <option value="fallido" <?php selected($estado, 'fallido'); ?>><?php _e('Fallido', 'tureserva'); ?></option>
            <option value="reembolsado" <?php selected($estado, 'reembolsado'); ?>><?php _e('Reembolsado', 'tureserva'); ?></option>
        </select>
    </p>

    <p style="font-size:12px;color:#555;">
        <strong><?php _e('Creado:', 'tureserva'); ?></strong> <?php echo esc_html($fecha_creado); ?><br>
        <strong><?php _e('Actualizado:', 'tureserva'); ?></strong> <?php echo esc_html($fecha_modificado); ?>
    </p>

    <p>
        <button type="submit" class="button button-primary" name="tureserva_crear_pago" value="1">
            <?php echo $post->ID ? __('Actualizar pago', 'tureserva') : __('Crear pago', 'tureserva'); ?>
        </button>
    </p>

    <?php if ($estado === 'pendiente') : ?>
        <hr>
        <p>
            <button type="button"
                class="button button-secondary"
                id="tureserva-stripe-charge"
                data-reserva="<?php echo esc_attr($reserva_id); ?>"
                data-monto="<?php echo esc_attr($monto); ?>"
                data-moneda="<?php echo esc_attr($moneda); ?>">
                💳 <?php _e('Cobrar con tarjeta (Stripe)', 'tureserva'); ?>
            </button>
        </p>
        <div id="tureserva-stripe-result" style="font-size:12px;color:#444;"></div>
    <?php endif; ?>
    <?php
}

// =======================================================
// 🕓 Renderizar “Registros”
// =======================================================
function tureserva_render_pago_logs($post) {
    $logs = get_post_meta($post->ID, '_tureserva_pago_log', true) ?: [];

    echo '<div style="max-height:150px;overflow:auto;background:#f9f9f9;border:1px solid #ddd;padding:5px;">';
    if (empty($logs)) {
        echo '<p style="font-size:12px;color:#777;">' . __('Sin registros recientes.', 'tureserva') . '</p>';
    } else {
        foreach (array_reverse($logs) as $log) {
            echo '<p style="margin:0;padding:4px 0;border-bottom:1px solid #eee;font-size:12px;">';
            echo '<strong>' . esc_html($log['fecha']) . '</strong><br>';
            echo esc_html($log['mensaje']);
            if (!empty($log['usuario'])) {
                echo ' <em>(' . esc_html($log['usuario']) . ')</em>';
            }
            echo '</p>';
        }
    }
    echo '</div>';
    echo '<p style="margin-top:6px;font-size:11px;color:#777;">';
    echo '<strong>' . __('Última actualización:', 'tureserva') . '</strong> ' . esc_html(current_time('mysql'));
    echo '</p>';
}

// =======================================================
// 💾 Guardar estado y registrar logs automáticos
// =======================================================
add_action('save_post_tureserva_pagos', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['_tureserva_pago_estado'])) {
        update_post_meta($post_id, '_tureserva_pago_estado', sanitize_text_field($_POST['_tureserva_pago_estado']));
    }

    $logs = get_post_meta($post_id, '_tureserva_pago_log', true) ?: [];
    if (!is_array($logs)) $logs = [];

    $nuevo_log = [
        'fecha'   => current_time('mysql'),
        'mensaje' => sprintf('El estado se cambió a "%s"', ucfirst($_POST['_tureserva_pago_estado'] ?? 'Pendiente')),
        'usuario' => wp_get_current_user()->display_name
    ];

    $logs[] = $nuevo_log;
    update_post_meta($post_id, '_tureserva_pago_log', $logs);
});

// =======================================================
// ⚡ Script para botón “Cobrar con Stripe”
// =======================================================
add_action('admin_footer', function() {
    $pantalla = get_current_screen();
    if ($pantalla && $pantalla->post_type === 'tureserva_pagos') : ?>
    <script>
    (function($){
        $('#tureserva-stripe-charge').on('click', function(){
            const btn = $(this);
            const reserva = btn.data('reserva');
            const monto = btn.data('monto');
            const moneda = btn.data('moneda');
            const resultado = $('#tureserva-stripe-result');

            if(!reserva || !monto){
                resultado.text('⚠️ No se puede procesar: faltan datos de reserva o monto.');
                return;
            }

            resultado.text('Procesando pago con Stripe... ⏳');
            btn.prop('disabled', true);

            $.post(ajaxurl, {
                action: 'tureserva_procesar_pago',
                reserva_id: reserva,
                amount: monto,
                token: 'tok_visa' // token de prueba
            }, function(response){
                if(response.success){
                    resultado.html('✅ Pago procesado correctamente.');
                    location.reload();
                } else {
                    resultado.html('❌ Error: ' + (response.data || 'No se pudo procesar el pago.'));
                }
                btn.prop('disabled', false);
            });
        });
    })(jQuery);
    </script>
    <?php endif;
});
