<?php
/**
 * ==========================================================
 * METABOX: Panel lateral de Pago — TuReserva
 * ==========================================================
 * Replica la estructura de MotoPress:
 * - Estado del pago
 * - Fechas de creación / modificación
 * - Registro de logs
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧾 Registrar Metaboxes Laterales
// =======================================================
add_action('add_meta_boxes', function() {
    add_meta_box(
        'tureserva_pago_status',
        __('Actualizar pago', 'tureserva'),
        'tureserva_render_metabox_pago_status',
        'tureserva_pagos',
        'side',
        'high'
    );

    add_meta_box(
        'tureserva_pago_logs',
        __('Registros', 'tureserva'),
        'tureserva_render_metabox_pago_logs',
        'tureserva_pagos',
        'side',
        'default'
    );
});

// =======================================================
// 💳 Renderizar “Actualizar pago”
// =======================================================
function tureserva_render_metabox_pago_status($post) {
    $estado = get_post_meta($post->ID, '_tureserva_pago_estado', true) ?: 'pendiente';
    $fecha_creado = get_the_date('', $post->ID);
    $fecha_modificado = get_the_modified_date('', $post->ID);

    ?>
    <p>
        <label for="tureserva_pago_estado"><strong><?php _e('Status:', 'tureserva'); ?></strong></label><br>
        <select name="_tureserva_pago_estado" id="tureserva_pago_estado" style="width:100%;">
            <option value="pendiente" <?php selected($estado, 'pendiente'); ?>>Pendiente</option>
            <option value="completado" <?php selected($estado, 'completado'); ?>>Completado</option>
            <option value="fallido" <?php selected($estado, 'fallido'); ?>>Fallido</option>
            <option value="reembolsado" <?php selected($estado, 'reembolsado'); ?>>Reembolsado</option>
        </select>
    </p>

    <p>
        <small><strong><?php _e('Creado:', 'tureserva'); ?></strong> <?php echo esc_html($fecha_creado); ?></small><br>
        <small><strong><?php _e('Cambiado:', 'tureserva'); ?></strong> <?php echo esc_html($fecha_modificado); ?></small>
    </p>

    <p>
        <button type="submit" class="button button-primary" name="tureserva_crear_pago" value="1">
            <?php _e('Crear pago', 'tureserva'); ?>
        </button>
    </p>
    <?php
}

// =======================================================
// 🕓 Renderizar “Registros”
// =======================================================
function tureserva_render_metabox_pago_logs($post) {
    $logs = get_post_meta($post->ID, '_tureserva_pago_logs', true) ?: [];
    if (!is_array($logs)) $logs = [];

    echo '<textarea style="width:100%;height:100px;" readonly>';
    foreach ($logs as $log) {
        echo '[' . esc_html($log['fecha']) . '] ' . esc_html($log['mensaje']) . "\n";
    }
    echo '</textarea>';

    echo '<p><small>';
    echo '<strong>Fecha:</strong> ' . esc_html(current_time('mysql')) . '<br>';
    echo '<strong>Mensaje:</strong> ';
    echo !empty($logs) ? end($logs)['mensaje'] : __('Sin registros recientes', 'tureserva');
    echo '</small></p>';
}

// =======================================================
// 💾 Guardar estado y registro
// =======================================================
add_action('save_post_tureserva_pagos', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Guardar estado
    if (isset($_POST['_tureserva_pago_estado'])) {
        update_post_meta($post_id, '_tureserva_pago_estado', sanitize_text_field($_POST['_tureserva_pago_estado']));
    }

    // Registrar logs automáticos
    $logs = get_post_meta($post_id, '_tureserva_pago_logs', true) ?: [];
    if (!is_array($logs)) $logs = [];

    $nuevo_log = [
        'fecha' => current_time('mysql'),
        'mensaje' => 'El estado se cambió de Nueva a ' . ucfirst($_POST['_tureserva_pago_estado'] ?? 'Pendiente')
    ];
    $logs[] = $nuevo_log;

    update_post_meta($post_id, '_tureserva_pago_logs', $logs);
});
