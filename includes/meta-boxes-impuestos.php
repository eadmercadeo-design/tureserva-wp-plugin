<?php
/**
 * ==========================================================
 * META BOXES: Impuestos y Cargos — TuReserva
 * ==========================================================
 * Gestiona los campos para:
 * - Cargos (charges)
 * - Impuestos Alojamiento (tax_accommodation)
 * - Impuestos Servicios (tax_service)
 * - Impuestos Gratis (tax_free)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX
// ==========================================================
function tureserva_add_impuestos_metaboxes() {
    add_meta_box(
        'tureserva_impuesto_data',
        __('Configuración', 'tureserva'),
        'tureserva_render_impuesto_metabox',
        'tureserva_impuesto',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_impuestos_metaboxes');

// ==========================================================
// 🎨 RENDERIZADO
// ==========================================================
function tureserva_render_impuesto_metabox($post) {
    wp_nonce_field('tureserva_save_impuesto', 'tureserva_impuesto_nonce');

    // Determinar tipo
    $type = get_post_meta($post->ID, '_tureserva_tax_type', true);
    if (!$type && isset($_GET['tax_type'])) {
        $type = sanitize_text_field($_GET['tax_type']);
    }
    
    // Default si no hay tipo (aunque debería haber)
    if (!$type) $type = 'charge';

    echo '<input type="hidden" name="tureserva_tax_type" value="' . esc_attr($type) . '">';

    // Recuperar datos comunes
    $amount       = get_post_meta($post->ID, '_tureserva_amount', true);
    $calc_type    = get_post_meta($post->ID, '_tureserva_calc_type', true); // fixed, percent
    $description  = get_post_meta($post->ID, '_tureserva_description', true);
    $alojamientos = get_post_meta($post->ID, '_tureserva_alojamientos', true) ?: [];
    $temporadas   = get_post_meta($post->ID, '_tureserva_temporadas', true) ?: [];

    // Títulos
    $titles = [
        'charge'            => 'Configuración del Cargo',
        'tax_accommodation' => 'Configuración del Impuesto de Alojamiento',
        'tax_service'       => 'Configuración del Impuesto de Servicio',
        'tax_free'          => 'Configuración de Impuesto Exento'
    ];
    $title = isset($titles[$type]) ? $titles[$type] : 'Configuración';

    ?>
    <style>
        .ts-tax-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .ts-tax-header { font-size: 15px; font-weight: 600; color: #2271b1; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .ts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .ts-field { margin-bottom: 20px; }
        .ts-label { display: block; font-weight: 600; margin-bottom: 6px; color: #3c434a; }
        .ts-input, .ts-select, .ts-textarea { width: 100%; padding: 8px; border: 1px solid #dcdcde; border-radius: 4px; }
        .ts-helper { font-size: 12px; color: #646970; margin-top: 5px; }
        .ts-multiselect { height: 120px; overflow-y: auto; border: 1px solid #dcdcde; padding: 10px; background: #fff; border-radius: 4px; }
        .ts-checkbox-label { display: block; margin-bottom: 4px; }
    </style>

    <div class="ts-tax-wrapper">
        <div class="ts-tax-header"><?php echo esc_html($title); ?></div>

        <?php 
        // ===================================================
        // 🔹 A. CARGOS (Charges)
        // ===================================================
        if ($type === 'charge') {
            $charge_type = get_post_meta($post->ID, '_tureserva_charge_type', true); // per_reservation, per_night, per_guest, per_accommodation
            ?>
            <div class="ts-grid-2">
                <div class="ts-field">
                    <label class="ts-label">Tipo de Cargo</label>
                    <select name="tureserva_charge_type" class="ts-select">
                        <option value="per_reservation" <?php selected($charge_type, 'per_reservation'); ?>>Fijo por reserva</option>
                        <option value="per_night" <?php selected($charge_type, 'per_night'); ?>>Fijo por noche</option>
                        <option value="per_guest" <?php selected($charge_type, 'per_guest'); ?>>Fijo por huésped</option>
                        <option value="per_accommodation" <?php selected($charge_type, 'per_accommodation'); ?>>Fijo por alojamiento</option>
                    </select>
                </div>
                <div class="ts-field">
                    <label class="ts-label">Monto / Valor ($)</label>
                    <input type="number" name="tureserva_amount" value="<?php echo esc_attr($amount); ?>" class="ts-input" step="0.01" min="0">
                </div>
            </div>
            
            <div class="ts-field">
                <label class="ts-label">Descripción (Opcional)</label>
                <textarea name="tureserva_description" class="ts-textarea" rows="2"><?php echo esc_textarea($description); ?></textarea>
            </div>
            <?php
        }

        // ===================================================
        // 🔹 B. IMPUESTOS ALOJAMIENTO
        // ===================================================
        if ($type === 'tax_accommodation') {
            $apply_on = get_post_meta($post->ID, '_tureserva_apply_on', true); // nightly_rate, total
            $display_mode = get_post_meta($post->ID, '_tureserva_display_mode', true); // include, separate
            ?>
            <div class="ts-grid-2">
                <div class="ts-field">
                    <label class="ts-label">Tipo de Cálculo</label>
                    <select name="tureserva_calc_type" class="ts-select">
                        <option value="percent" <?php selected($calc_type, 'percent'); ?>>Porcentaje (%)</option>
                        <option value="fixed" <?php selected($calc_type, 'fixed'); ?>>Monto Fijo ($)</option>
                    </select>
                </div>
                <div class="ts-field">
                    <label class="ts-label">Valor</label>
                    <input type="number" name="tureserva_amount" value="<?php echo esc_attr($amount); ?>" class="ts-input" step="0.01" min="0">
                </div>
            </div>

            <div class="ts-grid-2">
                <div class="ts-field">
                    <label class="ts-label">Aplicar sobre</label>
                    <select name="tureserva_apply_on" class="ts-select">
                        <option value="nightly_rate" <?php selected($apply_on, 'nightly_rate'); ?>>Tarifa por noche</option>
                        <option value="total" <?php selected($apply_on, 'total'); ?>>Total de la reserva</option>
                    </select>
                </div>
                <div class="ts-field">
                    <label class="ts-label">Modo de visualización</label>
                    <select name="tureserva_display_mode" class="ts-select">
                        <option value="separate" <?php selected($display_mode, 'separate'); ?>>Mostrar como línea separada</option>
                        <option value="include" <?php selected($display_mode, 'include'); ?>>Incluido en el precio (subtotal)</option>
                    </select>
                </div>
            </div>
            <?php
        }

        // ===================================================
        // 🔹 C. IMPUESTOS SERVICIOS
        // ===================================================
        if ($type === 'tax_service') {
            $services = get_post_meta($post->ID, '_tureserva_services', true) ?: [];
            $apply_mode = get_post_meta($post->ID, '_tureserva_service_apply_mode', true); // unit, reservation
            ?>
            <div class="ts-grid-2">
                <div class="ts-field">
                    <label class="ts-label">Tipo de Cálculo</label>
                    <select name="tureserva_calc_type" class="ts-select">
                        <option value="percent" <?php selected($calc_type, 'percent'); ?>>Porcentaje (%)</option>
                        <option value="fixed" <?php selected($calc_type, 'fixed'); ?>>Monto Fijo ($)</option>
                    </select>
                </div>
                <div class="ts-field">
                    <label class="ts-label">Valor</label>
                    <input type="number" name="tureserva_amount" value="<?php echo esc_attr($amount); ?>" class="ts-input" step="0.01" min="0">
                </div>
            </div>

            <div class="ts-field">
                <label class="ts-label">Servicios Afectados</label>
                <div class="ts-multiselect">
                    <?php
                    $all_services = get_posts(['post_type' => 'tureserva_servicio', 'posts_per_page' => -1]);
                    foreach ($all_services as $svc) {
                        $checked = in_array($svc->ID, $services) ? 'checked' : '';
                        echo '<label class="ts-checkbox-label">';
                        echo '<input type="checkbox" name="tureserva_services[]" value="' . $svc->ID . '" ' . $checked . '> ' . esc_html($svc->post_title);
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <div class="ts-field">
                <label class="ts-label">Aplicación</label>
                <select name="tureserva_service_apply_mode" class="ts-select">
                    <option value="unit" <?php selected($apply_mode, 'unit'); ?>>Por unidad de servicio</option>
                    <option value="reservation" <?php selected($apply_mode, 'reservation'); ?>>Por reserva completa</option>
                </select>
            </div>
            <?php
        }

        // ===================================================
        // 🔹 D. IMPUESTOS GRATIS
        // ===================================================
        if ($type === 'tax_free') {
            $visibility = get_post_meta($post->ID, '_tureserva_visibility', true); // show, hide
            ?>
            <div class="ts-field">
                <label class="ts-label">Tipo de Impuesto</label>
                <input type="text" value="Tasa 0% (Exento)" class="ts-input" readonly style="background:#f0f0f1; color:#666;">
            </div>
            <div class="ts-field">
                <label class="ts-label">Visibilidad</label>
                <select name="tureserva_visibility" class="ts-select">
                    <option value="show" <?php selected($visibility, 'show'); ?>>Incluir en factura (mostrando 0%)</option>
                    <option value="hide" <?php selected($visibility, 'hide'); ?>>No mostrar</option>
                </select>
            </div>
            <?php
        }

        // ===================================================
        // 🌍 APLICAR A (Común para Charge, Tax Accom, Tax Free)
        // ===================================================
        if ($type !== 'tax_service') {
            ?>
            <div class="ts-field">
                <label class="ts-label">Aplicar a Alojamientos</label>
                <div class="ts-multiselect">
                    <?php
                    $all_alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => -1]);
                    foreach ($all_alojamientos as $aloj) {
                        $checked = in_array($aloj->ID, $alojamientos) ? 'checked' : '';
                        echo '<label class="ts-checkbox-label">';
                        echo '<input type="checkbox" name="tureserva_alojamientos[]" value="' . $aloj->ID . '" ' . $checked . '> ' . esc_html($aloj->post_title);
                        echo '</label>';
                    }
                    ?>
                </div>
                <p class="ts-helper">Si no seleccionas ninguno, se aplicará a TODOS.</p>
            </div>
            <?php
        }

        // ===================================================
        // 📅 TEMPORADAS (Solo Charges y Tax Accom)
        // ===================================================
        if ($type === 'charge' || $type === 'tax_accommodation') {
            ?>
            <div class="ts-field">
                <label class="ts-label">Aplicar por Temporada (Opcional)</label>
                <div class="ts-multiselect">
                    <?php
                    $all_temporadas = get_posts(['post_type' => 'tureserva_temporada', 'posts_per_page' => -1]);
                    if ($all_temporadas) {
                        foreach ($all_temporadas as $temp) {
                            $checked = in_array($temp->ID, $temporadas) ? 'checked' : '';
                            echo '<label class="ts-checkbox-label">';
                            echo '<input type="checkbox" name="tureserva_temporadas[]" value="' . $temp->ID . '" ' . $checked . '> ' . esc_html($temp->post_title);
                            echo '</label>';
                        }
                    } else {
                        echo '<span style="color:#999;">No hay temporadas creadas.</span>';
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>

    </div>
    <?php
}

// ==========================================================
// 💾 GUARDAR DATOS
// ==========================================================
function tureserva_save_impuesto_metabox($post_id) {
    if (!isset($_POST['tureserva_impuesto_nonce']) || !wp_verify_nonce($_POST['tureserva_impuesto_nonce'], 'tureserva_save_impuesto')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Guardar tipo
    if (isset($_POST['tureserva_tax_type'])) {
        update_post_meta($post_id, '_tureserva_tax_type', sanitize_text_field($_POST['tureserva_tax_type']));
    }

    // Campos simples
    $simple_fields = [
        'tureserva_amount', 'tureserva_calc_type', 'tureserva_description',
        'tureserva_charge_type', 'tureserva_apply_on', 'tureserva_display_mode',
        'tureserva_service_apply_mode', 'tureserva_visibility'
    ];
    foreach ($simple_fields as $field) {
        if (isset($_POST[$field])) update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
    }

    // Arrays
    $array_fields = ['tureserva_alojamientos', 'tureserva_temporadas', 'tureserva_services'];
    foreach ($array_fields as $field) {
        $val = isset($_POST[$field]) ? (array)$_POST[$field] : [];
        update_post_meta($post_id, '_' . $field, $val);
    }
}
add_action('save_post_tureserva_impuesto', 'tureserva_save_impuesto_metabox');
