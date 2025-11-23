<?php
/**
 * ==========================================================
 * META BOXES: Reglas de Reserva — TuReserva
 * ==========================================================
 * Gestiona los campos para cada tipo de regla.
 * Detecta el tipo de regla vía $_GET['rule_type'] o metadato guardado.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX
// ==========================================================
function tureserva_add_reglas_metaboxes() {
    add_meta_box(
        'tureserva_regla_data',
        __('Configuración de la Regla', 'tureserva'),
        'tureserva_render_regla_metabox',
        'tureserva_regla',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_reglas_metaboxes');

// ==========================================================
// 🎨 RENDERIZADO
// ==========================================================
function tureserva_render_regla_metabox($post) {
    wp_nonce_field('tureserva_save_regla', 'tureserva_regla_nonce');

    // Determinar tipo de regla (nuevo o existente)
    $type = get_post_meta($post->ID, '_tureserva_rule_type', true);
    if (!$type && isset($_GET['rule_type'])) {
        $type = sanitize_text_field($_GET['rule_type']);
    }
    
    // Guardar el tipo como campo oculto si es nuevo
    echo '<input type="hidden" name="tureserva_rule_type" value="' . esc_attr($type) . '">';

    // Recuperar datos comunes
    $alojamientos = get_post_meta($post->ID, '_tureserva_alojamientos', true) ?: [];
    $temporadas   = get_post_meta($post->ID, '_tureserva_temporadas', true) ?: [];
    
    // Títulos y descripciones según tipo
    $titles = [
        'arrival_days'   => 'Días de Llegada Permitidos',
        'departure_days' => 'Días de Salida Permitidos',
        'min_stay'       => 'Estancia Mínima',
        'max_stay'       => 'Estancia Máxima',
        'block'          => 'Bloquear Alojamiento',
        'min_advance'    => 'Antelación Mínima de Reserva',
        'max_advance'    => 'Antelación Máxima de Reserva',
        'buffer'         => 'Búfer de Reservas (Tiempo de preparación)'
    ];
    
    $title = isset($titles[$type]) ? $titles[$type] : 'Configuración de Regla';

    ?>
    <style>
        .ts-rule-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .ts-rule-header { font-size: 16px; font-weight: 600; color: #2271b1; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .ts-field { margin-bottom: 20px; }
        .ts-label { display: block; font-weight: 600; margin-bottom: 5px; }
        .ts-input { width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .ts-checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .ts-checkbox-label { display: flex; align-items: center; gap: 5px; cursor: pointer; }
        .ts-helper { font-size: 12px; color: #666; margin-top: 5px; font-style: italic; }
        .ts-multiselect { height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px; }
    </style>

    <div class="ts-rule-wrapper">
        <div class="ts-rule-header"><?php echo esc_html($title); ?></div>

        <?php 
        // ---------------------------------------------------
        // 🔹 CAMPOS ESPECÍFICOS POR TIPO
        // ---------------------------------------------------
        
        // A. DÍAS DE LLEGADA / SALIDA
        if ($type === 'arrival_days' || $type === 'departure_days') {
            $days = get_post_meta($post->ID, '_tureserva_days', true) ?: [];
            $week = ['mon'=>'Lunes','tue'=>'Martes','wed'=>'Miércoles','thu'=>'Jueves','fri'=>'Viernes','sat'=>'Sábado','sun'=>'Domingo'];
            ?>
            <div class="ts-field">
                <label class="ts-label">Seleccionar días permitidos</label>
                <div class="ts-checkbox-group">
                    <?php foreach ($week as $key => $label): ?>
                        <label class="ts-checkbox-label">
                            <input type="checkbox" name="tureserva_days[]" value="<?php echo $key; ?>" <?php checked(in_array($key, $days)); ?>>
                            <?php echo $label; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="ts-helper">Desmarca los días en los que NO se permite <?php echo $type === 'arrival_days' ? 'la llegada' : 'la salida'; ?>.</p>
            </div>
            <?php
        }

        // B. ESTANCIA MÍNIMA / MÁXIMA
        if ($type === 'min_stay' || $type === 'max_stay') {
            $nights = get_post_meta($post->ID, '_tureserva_nights', true);
            ?>
            <div class="ts-field">
                <label class="ts-label">Número de noches</label>
                <input type="number" name="tureserva_nights" value="<?php echo esc_attr($nights); ?>" class="ts-input" min="1">
            </div>
            <?php
        }

        // C. ANTELACIÓN MÍNIMA / MÁXIMA
        if ($type === 'min_advance' || $type === 'max_advance') {
            $days_advance = get_post_meta($post->ID, '_tureserva_days_advance', true);
            ?>
            <div class="ts-field">
                <label class="ts-label">Días de antelación</label>
                <input type="number" name="tureserva_days_advance" value="<?php echo esc_attr($days_advance); ?>" class="ts-input" min="0">
                <p class="ts-helper">
                    <?php echo $type === 'min_advance' ? 'Mínimo de días antes del check-in para reservar.' : 'Máximo de días permitidos para reservar con antelación.'; ?>
                </p>
            </div>
            <?php
        }

        // D. BLOQUEO
        if ($type === 'block') {
            $date_from = get_post_meta($post->ID, '_tureserva_date_from', true);
            $date_to   = get_post_meta($post->ID, '_tureserva_date_to', true);
            $reason    = get_post_meta($post->ID, '_tureserva_reason', true);
            ?>
            <div class="ts-field">
                <label class="ts-label">Rango de Fechas</label>
                <div style="display:flex; gap:10px;">
                    <input type="date" name="tureserva_date_from" value="<?php echo esc_attr($date_from); ?>" class="ts-input">
                    <span style="align-self:center;">hasta</span>
                    <input type="date" name="tureserva_date_to" value="<?php echo esc_attr($date_to); ?>" class="ts-input">
                </div>
            </div>
            <div class="ts-field">
                <label class="ts-label">Motivo (Opcional)</label>
                <input type="text" name="tureserva_reason" value="<?php echo esc_attr($reason); ?>" class="ts-input" placeholder="Ej: Mantenimiento">
            </div>
            <?php
        }

        // E. BÚFER
        if ($type === 'buffer') {
            $buffer_days = get_post_meta($post->ID, '_tureserva_buffer_days', true);
            ?>
            <div class="ts-field">
                <label class="ts-label">Días de bloqueo entre reservas</label>
                <input type="number" name="tureserva_buffer_days" value="<?php echo esc_attr($buffer_days); ?>" class="ts-input" min="1">
                <p class="ts-helper">Tiempo necesario para limpieza o preparación entre check-out y check-in.</p>
            </div>
            <?php
        }
        ?>

        <!-- --------------------------------------------------- -->
        <!-- 🌍 APLICAR A (Común para casi todos) -->
        <!-- --------------------------------------------------- -->
        <?php if ($type !== 'block'): // El bloqueo tiene su propia lógica de selección ?>
            <div class="ts-field">
                <label class="ts-label">Aplicar a Alojamientos</label>
                <div class="ts-multiselect">
                    <?php
                    $all_alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => -1]);
                    foreach ($all_alojamientos as $aloj) {
                        $checked = in_array($aloj->ID, $alojamientos) ? 'checked' : '';
                        echo '<label class="ts-checkbox-label" style="margin-bottom:5px;">';
                        echo '<input type="checkbox" name="tureserva_alojamientos[]" value="' . $aloj->ID . '" ' . $checked . '> ' . esc_html($aloj->post_title);
                        echo '</label>';
                    }
                    ?>
                </div>
                <p class="ts-helper">Si no seleccionas ninguno, se aplicará a TODOS.</p>
            </div>

            <div class="ts-field">
                <label class="ts-label">Aplicar a Temporadas</label>
                <div class="ts-multiselect">
                    <?php
                    $all_temporadas = get_posts(['post_type' => 'tureserva_temporada', 'posts_per_page' => -1]);
                    if ($all_temporadas) {
                        foreach ($all_temporadas as $temp) {
                            $checked = in_array($temp->ID, $temporadas) ? 'checked' : '';
                            echo '<label class="ts-checkbox-label" style="margin-bottom:5px;">';
                            echo '<input type="checkbox" name="tureserva_temporadas[]" value="' . $temp->ID . '" ' . $checked . '> ' . esc_html($temp->post_title);
                            echo '</label>';
                        }
                    } else {
                        echo '<span style="color:#999;">No hay temporadas creadas.</span>';
                    }
                    ?>
                </div>
                <p class="ts-helper">Opcional. Si seleccionas temporadas, la regla solo aplicará en esas fechas.</p>
            </div>
        <?php else: // Para BLOQUEO, solo alojamientos ?>
            <div class="ts-field">
                <label class="ts-label">Alojamiento(s) a Bloquear</label>
                <div class="ts-multiselect">
                    <?php
                    $all_alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => -1]);
                    foreach ($all_alojamientos as $aloj) {
                        $checked = in_array($aloj->ID, $alojamientos) ? 'checked' : '';
                        echo '<label class="ts-checkbox-label" style="margin-bottom:5px;">';
                        echo '<input type="checkbox" name="tureserva_alojamientos[]" value="' . $aloj->ID . '" ' . $checked . '> ' . esc_html($aloj->post_title);
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php
}

// ==========================================================
// 💾 GUARDAR DATOS
// ==========================================================
function tureserva_save_regla_metabox($post_id) {
    if (!isset($_POST['tureserva_regla_nonce']) || !wp_verify_nonce($_POST['tureserva_regla_nonce'], 'tureserva_save_regla')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Guardar tipo
    if (isset($_POST['tureserva_rule_type'])) {
        update_post_meta($post_id, '_tureserva_rule_type', sanitize_text_field($_POST['tureserva_rule_type']));
    }

    // Campos simples
    $simple_fields = [
        'tureserva_nights', 'tureserva_days_advance', 
        'tureserva_date_from', 'tureserva_date_to', 'tureserva_reason',
        'tureserva_buffer_days'
    ];
    foreach ($simple_fields as $field) {
        if (isset($_POST[$field])) update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
    }

    // Arrays
    $array_fields = ['tureserva_days', 'tureserva_alojamientos', 'tureserva_temporadas'];
    foreach ($array_fields as $field) {
        $val = isset($_POST[$field]) ? (array)$_POST[$field] : [];
        update_post_meta($post_id, '_' . $field, $val);
    }
}
add_action('save_post_tureserva_regla', 'tureserva_save_regla_metabox');
