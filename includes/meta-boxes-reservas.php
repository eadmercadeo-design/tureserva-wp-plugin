<?php
/**
 * ==========================================================
 * META BOXES – RESERVAS (versión corregida y comentada)
 * ==========================================================
 * Este archivo crea y gestiona el meta box del CPT:
 *      tureserva_reserva  (singular — nombre correcto)
 *
 * CAMBIOS IMPORTANTES:
 * ------------------------------------------
 * ✔ Corrección del CPT: antes decía tureserva_reservas (NO EXISTE)
 * ✔ Hook de guardado corregido: save_post_tureserva_reserva
 * ✔ Metabox correctamente enlazado al CPT singular
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX PRINCIPAL
// ==========================================================
/**
 * Antes se vinculaba a un CPT incorrecto:
 *      tureserva_reservas  ❌
 *
 * Ahora usamos el CPT real registrado en cpt-reservas.php:
 *      tureserva_reserva  ✔ 
 */
function tureserva_add_reservas_metaboxes()
{
    add_meta_box(
        'tureserva_reserva_detalles',
        __('Detalles de la Reserva', 'tureserva'),
        'tureserva_render_reserva_metabox',
        'tureserva_reserva',   // ✔ CPT correcto
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_reservas_metaboxes');


// ==========================================================
// 🧾 RENDER DEL FORMULARIO DEL META BOX
// ==========================================================
// ==========================================================
// 🧾 RENDER DEL FORMULARIO DEL META BOX
// ==========================================================
function tureserva_render_reserva_metabox($post)
{
    // Recuperar metadatos
    $checkin     = get_post_meta($post->ID, '_tureserva_checkin', true);
    $checkout    = get_post_meta($post->ID, '_tureserva_checkout', true);
    $adultos     = get_post_meta($post->ID, '_tureserva_adultos', true);
    $ninos       = get_post_meta($post->ID, '_tureserva_ninos', true);
    $alojamiento = get_post_meta($post->ID, '_tureserva_alojamiento_id', true);
    $precio      = get_post_meta($post->ID, '_tureserva_precio_total', true);
    $cliente     = get_post_meta($post->ID, '_tureserva_cliente_nombre', true);
    $email       = get_post_meta($post->ID, '_tureserva_cliente_email', true);
    $telefono    = get_post_meta($post->ID, '_tureserva_cliente_telefono', true);
    $estado      = get_post_meta($post->ID, '_tureserva_estado', true);
    
    // Servicios guardados (array de IDs)
    $servicios_guardados = get_post_meta($post->ID, '_tureserva_servicios', true);
    if (!is_array($servicios_guardados)) $servicios_guardados = [];

    // Seguridad
    wp_nonce_field('tureserva_save_reserva', 'tureserva_reserva_nonce');
    ?>

    <style>
        .ts-meta-box { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
        .ts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .ts-field { margin-bottom: 15px; }
        .ts-label { font-weight: 600; display: block; margin-bottom: 5px; color: #2c3338; }
        .ts-input { width: 100%; padding: 8px; border: 1px solid #dcdcde; border-radius: 4px; box-sizing: border-box; }
        .ts-section-title { font-size: 14px; font-weight: 700; border-bottom: 1px solid #eee; padding-bottom: 5px; margin: 20px 0 15px 0; color: #2271b1; text-transform: uppercase; letter-spacing: 0.5px; }
        .ts-services-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; background: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #eee; }
        .ts-service-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .ts-helper { font-size: 12px; color: #666; margin-top: 4px; }
    </style>

    <div class="ts-meta-box">
        
        <!-- SECCIÓN 1: ESTADO Y ALOJAMIENTO -->
        <div class="ts-grid-2">
            <div class="ts-field">
                <label class="ts-label">Estado de la Reserva</label>
                <select name="tureserva_estado" class="ts-input" style="font-weight:bold;">
                    <?php
                    $estados = [
                        'pendiente'  => '🟠 Pendiente',
                        'confirmada' => '🟢 Confirmada',
                        'cancelada'  => '🔴 Cancelada'
                    ];
                    foreach ($estados as $valor => $label) {
                        echo '<option value="' . esc_attr($valor) . '"' . selected($estado, $valor, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="ts-field">
                <label class="ts-label">Alojamiento Asignado</label>
                <select name="tureserva_alojamiento_id" class="ts-input">
                    <option value="0"><?php _e('— Seleccionar alojamiento —', 'tureserva'); ?></option>
                    <?php
                    // 🔥 CORRECCIÓN: Usar 'trs_alojamiento' en lugar de 'tureserva_alojamiento'
                    $alojamientos = get_posts(array(
                        'post_type'      => 'trs_alojamiento',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'orderby'        => 'title',
                        'order'          => 'ASC'
                    ));

                    foreach ($alojamientos as $a) {
                        $selected = selected($alojamiento, $a->ID, false);
                        echo '<option value="' . esc_attr($a->ID) . '"' . $selected . '>' . esc_html($a->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- SECCIÓN 2: FECHAS Y HUÉSPEDES -->
        <div class="ts-section-title">Fechas y Ocupación</div>
        <div class="ts-grid-2">
            <div class="ts-field">
                <label class="ts-label">Fecha de Llegada (Check-in)</label>
                <input type="date" name="tureserva_checkin" value="<?php echo esc_attr($checkin); ?>" class="ts-input">
            </div>
            <div class="ts-field">
                <label class="ts-label">Fecha de Salida (Check-out)</label>
                <input type="date" name="tureserva_checkout" value="<?php echo esc_attr($checkout); ?>" class="ts-input">
            </div>
        </div>
        <div class="ts-grid-2">
            <div class="ts-field">
                <label class="ts-label">Adultos</label>
                <input type="number" name="tureserva_adultos" value="<?php echo esc_attr($adultos); ?>" min="1" class="ts-input">
            </div>
            <div class="ts-field">
                <label class="ts-label">Niños</label>
                <input type="number" name="tureserva_ninos" value="<?php echo esc_attr($ninos); ?>" min="0" class="ts-input">
            </div>
        </div>

        <!-- SECCIÓN 3: CLIENTE -->
        <div class="ts-section-title">Datos del Cliente</div>
        <div class="ts-field">
            <label class="ts-label">Nombre Completo</label>
            <input type="text" name="tureserva_cliente_nombre" value="<?php echo esc_attr($cliente); ?>" class="ts-input">
        </div>
        <div class="ts-grid-2">
            <div class="ts-field">
                <label class="ts-label">Correo Electrónico</label>
                <input type="email" name="tureserva_cliente_email" value="<?php echo esc_attr($email); ?>" class="ts-input">
            </div>
            <div class="ts-field">
                <label class="ts-label">Teléfono</label>
                <input type="text" name="tureserva_cliente_telefono" value="<?php echo esc_attr($telefono); ?>" class="ts-input">
            </div>
        </div>

        <!-- SECCIÓN 4: SERVICIOS ADICIONALES -->
        <div class="ts-section-title">Servicios Adicionales</div>
        <div class="ts-services-list">
            <?php
            $servicios_disponibles = get_posts([
                'post_type' => 'tureserva_servicio',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ]);

            if ($servicios_disponibles) {
                foreach ($servicios_disponibles as $servicio) {
                    $checked = in_array($servicio->ID, $servicios_guardados) ? 'checked' : '';
                    $precio_servicio = get_post_meta($servicio->ID, 'tureserva_precio', true);
                    $precio_txt = $precio_servicio ? " ($" . number_format($precio_servicio, 2) . ")" : '';
                    
                    echo '<label class="ts-service-item">';
                    echo '<input type="checkbox" name="tureserva_servicios[]" value="' . esc_attr($servicio->ID) . '" ' . $checked . '>';
                    echo '<span>' . esc_html($servicio->post_title) . $precio_txt . '</span>';
                    echo '</label>';
                }
            } else {
                echo '<p style="color:#666; font-style:italic;">No hay servicios registrados.</p>';
            }
            ?>
        </div>

        <!-- SECCIÓN 5: TOTAL Y PAGOS -->
        <div class="ts-section-title">Facturación</div>
        <div class="ts-grid-2">
            <div class="ts-field">
                <label class="ts-label">Precio Total (USD)</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:18px; font-weight:bold;">$</span>
                    <input type="number" step="0.01" name="tureserva_precio_total" value="<?php echo esc_attr($precio); ?>" class="ts-input" style="font-size:16px; font-weight:bold; color:#2271b1;">
                </div>
            </div>
            
            <?php 
            // Recuperar lo pagado (si existe)
            $pagado = get_post_meta($post->ID, '_tureserva_total_pagado', true) ?: 0;
            $pendiente = floatval($precio) - floatval($pagado);
            $color_pendiente = $pendiente > 0 ? '#d63638' : '#00a32a';
            ?>
            
            <div class="ts-field">
                <label class="ts-label">Pagado hasta ahora</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:18px; font-weight:bold;">$</span>
                    <input type="number" step="0.01" name="tureserva_total_pagado" value="<?php echo esc_attr($pagado); ?>" class="ts-input">
                </div>
                <p class="ts-helper" style="color:<?php echo $color_pendiente; ?>; font-weight:bold;">
                    Pendiente: $<?php echo number_format($pendiente, 2); ?>
                </p>
            </div>
        </div>

        <p class="ts-helper">Si cambias fechas o servicios, recuerda actualizar el precio total manualmente.</p>

    </div>
    <?php
}

// ==========================================================
// 💾 GUARDAR DATOS DEL META BOX
// ==========================================================
function tureserva_save_reserva_metabox($post_id)
{
    // Verificar nonce
    if (!isset($_POST['tureserva_reserva_nonce']) || !wp_verify_nonce($_POST['tureserva_reserva_nonce'], 'tureserva_save_reserva')) {
        return;
    }

    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Guardar campos simples
    $campos = [
        'tureserva_checkin',
        'tureserva_checkout',
        'tureserva_adultos',
        'tureserva_ninos',
        'tureserva_alojamiento_id',
        'tureserva_precio_total',
        'tureserva_total_pagado', // Nuevo campo
        'tureserva_cliente_nombre',
        'tureserva_cliente_email',
        'tureserva_cliente_telefono',
        'tureserva_estado'
    ];

    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, '_' . $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    // Guardar Servicios (Array)
    if (isset($_POST['tureserva_servicios'])) {
        $servicios = array_map('intval', $_POST['tureserva_servicios']);
        update_post_meta($post_id, '_tureserva_servicios', $servicios);
    } else {
        update_post_meta($post_id, '_tureserva_servicios', []);
    }
}
add_action('save_post_tureserva_reserva', 'tureserva_save_reserva_metabox');

