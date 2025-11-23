<?php
/**
 * ==========================================================
 * META BOXES: Cupones — TuReserva
 * ==========================================================
 * Gestiona los campos personalizados y la interfaz de usuario
 * para la configuración de cupones de descuento.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX
// ==========================================================
function tureserva_add_cupones_metaboxes() {
    add_meta_box(
        'tureserva_cupon_data',
        __('Información del Cupón', 'tureserva'),
        'tureserva_render_cupon_metabox',
        'tureserva_cupon',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_cupones_metaboxes');

// ==========================================================
// 🎨 RENDERIZADO DE LA INTERFAZ
// ==========================================================
function tureserva_render_cupon_metabox($post) {
    wp_nonce_field('tureserva_save_cupon', 'tureserva_cupon_nonce');

    // Recuperar valores
    $descripcion    = get_post_meta($post->ID, '_tureserva_descripcion', true);
    $tipo_cupon     = get_post_meta($post->ID, '_tureserva_tipo_cupon', true);
    $monto          = get_post_meta($post->ID, '_tureserva_monto', true);
    $fecha_caducidad= get_post_meta($post->ID, '_tureserva_fecha_caducidad', true);
    $alojamientos   = get_post_meta($post->ID, '_tureserva_alojamientos', true) ?: [];
    
    $checkin_after  = get_post_meta($post->ID, '_tureserva_checkin_after', true);
    $checkout_before= get_post_meta($post->ID, '_tureserva_checkout_before', true);
    
    $min_days_before= get_post_meta($post->ID, '_tureserva_min_days_before', true);
    $max_days_before= get_post_meta($post->ID, '_tureserva_max_days_before', true);
    
    $min_days       = get_post_meta($post->ID, '_tureserva_min_days', true);
    $max_days       = get_post_meta($post->ID, '_tureserva_max_days', true);
    $limite_uso     = get_post_meta($post->ID, '_tureserva_limite_uso', true);
    $uso_actual     = get_post_meta($post->ID, '_tureserva_uso_actual', true) ?: 0;

    ?>
    <style>
        /* 🎨 ESTILOS PREMIUM TIPO MOTOPRESS */
        .ts-cupon-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 100%;
        }
        .ts-section-header {
            font-size: 14px;
            font-weight: 700;
            color: #2271b1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 10px;
            margin: 25px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ts-section-header:first-child { margin-top: 0; }
        
        .ts-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .ts-field-group { margin-bottom: 15px; }
        
        .ts-label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #3c434a;
        }
        
        .ts-input, .ts-select, .ts-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3338;
            transition: border-color 0.2s;
        }
        
        .ts-input:focus, .ts-select:focus, .ts-textarea:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        
        .ts-helper {
            font-size: 12px;
            color: #646970;
            margin-top: 5px;
            font-style: italic;
        }
        
        .ts-tooltip {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: #dcdcde;
            color: #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 16px;
            font-size: 11px;
            cursor: help;
            margin-left: 5px;
        }

        /* Multiselect simple styling */
        .ts-multiselect-container {
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 10px;
            max-height: 150px;
            overflow-y: auto;
            background: #fff;
        }
        .ts-checkbox-item {
            display: block;
            margin-bottom: 5px;
        }
        
        /* Readonly field */
        .ts-readonly {
            background-color: #f0f0f1;
            color: #646970;
            cursor: not-allowed;
        }
    </style>

    <div class="ts-cupon-wrapper">

        <!-- 📌 DATOS PRINCIPALES -->
        <div class="ts-section-header">
            <span class="dashicons dashicons-tag"></span> Datos Principales
        </div>

        <div class="ts-field-group">
            <label class="ts-label">Descripción</label>
            <textarea name="tureserva_descripcion" class="ts-textarea" rows="3"><?php echo esc_textarea($descripcion); ?></textarea>
            <p class="ts-helper">Breve descripción del cupón para uso interno.</p>
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Tipo de Cupón</label>
                <select name="tureserva_tipo_cupon" class="ts-select">
                    <option value="percentage" <?php selected($tipo_cupon, 'percentage'); ?>>Porcentaje (%)</option>
                    <option value="fixed" <?php selected($tipo_cupon, 'fixed'); ?>>Monto fijo</option>
                    <option value="per_night" <?php selected($tipo_cupon, 'per_night'); ?>>Descuento por noche</option>
                    <option value="total" <?php selected($tipo_cupon, 'total'); ?>>Descuento total</option>
                </select>
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Monto del Cupón</label>
                <input type="number" name="tureserva_monto" value="<?php echo esc_attr($monto); ?>" class="ts-input" step="0.01" min="0">
                <p class="ts-helper">Valor del descuento según el tipo seleccionado.</p>
            </div>
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Fecha de Caducidad</label>
                <input type="date" name="tureserva_fecha_caducidad" value="<?php echo esc_attr($fecha_caducidad); ?>" class="ts-input">
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Tipos de Alojamiento</label>
                <div class="ts-multiselect-container">
                    <?php
                    $all_alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => -1]);
                    if ($all_alojamientos) {
                        foreach ($all_alojamientos as $aloj) {
                            $checked = in_array($aloj->ID, $alojamientos) ? 'checked' : '';
                            echo '<label class="ts-checkbox-item">';
                            echo '<input type="checkbox" name="tureserva_alojamientos[]" value="' . $aloj->ID . '" ' . $checked . '> ' . esc_html($aloj->post_title);
                            echo '</label>';
                        }
                    } else {
                        echo '<span style="color:#666;">No hay alojamientos registrados.</span>';
                    }
                    ?>
                </div>
                <p class="ts-helper">Deja vacío para aplicar a todos.</p>
            </div>
        </div>

        <!-- 📅 RESTRICCIONES POR FECHA -->
        <div class="ts-section-header">
            <span class="dashicons dashicons-calendar-alt"></span> Condiciones de Check-in / Check-out
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Check-in después de</label>
                <input type="date" name="tureserva_checkin_after" value="<?php echo esc_attr($checkin_after); ?>" class="ts-input">
                <p class="ts-helper">Válido para estancias que inicien después de esta fecha.</p>
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Check-out antes de</label>
                <input type="date" name="tureserva_checkout_before" value="<?php echo esc_attr($checkout_before); ?>" class="ts-input">
                <p class="ts-helper">Válido para estancias que terminen antes de esta fecha.</p>
            </div>
        </div>

        <!-- ⏳ ANTICIPACIÓN DE RESERVA -->
        <div class="ts-section-header">
            <span class="dashicons dashicons-clock"></span> Reglas de Anticipación
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Min days before check-in <span class="ts-tooltip" title="Early Bird">?</span></label>
                <input type="number" name="tureserva_min_days_before" value="<?php echo esc_attr($min_days_before); ?>" class="ts-input" min="0">
                <p class="ts-helper">Para descuentos "Early Bird" (reserva anticipada).</p>
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Max days before check-in <span class="ts-tooltip" title="Last Minute">?</span></label>
                <input type="number" name="tureserva_max_days_before" value="<?php echo esc_attr($max_days_before); ?>" class="ts-input" min="0">
                <p class="ts-helper">Para descuentos "Last Minute" (última hora).</p>
            </div>
        </div>

        <!-- 🔒 LÍMITES Y CONTROLES -->
        <div class="ts-section-header">
            <span class="dashicons dashicons-lock"></span> Límites y Controles
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Estancia Mínima (días)</label>
                <input type="number" name="tureserva_min_days" value="<?php echo esc_attr($min_days); ?>" class="ts-input" min="1">
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Estancia Máxima (días)</label>
                <input type="number" name="tureserva_max_days" value="<?php echo esc_attr($max_days); ?>" class="ts-input" min="1">
            </div>
        </div>

        <div class="ts-grid-2">
            <div class="ts-field-group">
                <label class="ts-label">Límite de Uso Total</label>
                <input type="number" name="tureserva_limite_uso" value="<?php echo esc_attr($limite_uso); ?>" class="ts-input" min="0">
                <p class="ts-helper">Veces que se puede usar este cupón (0 = ilimitado).</p>
            </div>
            <div class="ts-field-group">
                <label class="ts-label">Veces Utilizado</label>
                <input type="text" value="<?php echo esc_attr($uso_actual); ?>" class="ts-input ts-readonly" readonly>
                <p class="ts-helper">Contador automático.</p>
            </div>
        </div>

    </div>
    <?php
}

// ==========================================================
// 💾 GUARDADO DE DATOS
// ==========================================================
function tureserva_save_cupon_metabox($post_id) {
    if (!isset($_POST['tureserva_cupon_nonce']) || !wp_verify_nonce($_POST['tureserva_cupon_nonce'], 'tureserva_save_cupon')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        '_tureserva_descripcion',
        '_tureserva_tipo_cupon',
        '_tureserva_monto',
        '_tureserva_fecha_caducidad',
        '_tureserva_checkin_after',
        '_tureserva_checkout_before',
        '_tureserva_min_days_before',
        '_tureserva_max_days_before',
        '_tureserva_min_days',
        '_tureserva_max_days',
        '_tureserva_limite_uso'
    ];

    foreach ($fields as $field) {
        $key = str_replace('_tureserva_', 'tureserva_', $field); // input name vs meta key
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
        }
    }

    // Guardar array de alojamientos
    if (isset($_POST['tureserva_alojamientos'])) {
        $alojamientos = array_map('intval', $_POST['tureserva_alojamientos']);
        update_post_meta($post_id, '_tureserva_alojamientos', $alojamientos);
    } else {
        update_post_meta($post_id, '_tureserva_alojamientos', []);
    }
}
add_action('save_post_tureserva_cupon', 'tureserva_save_cupon_metabox');
