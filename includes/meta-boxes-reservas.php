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
    $estado      = get_post_meta($post->ID, '_tureserva_estado', true);

    // Seguridad
    wp_nonce_field('tureserva_save_reserva', 'tureserva_reserva_nonce');
    ?>

    <!-- Estilos básicos para presentación -->
    <style>
        .tureserva-field {margin-bottom:15px;}
        .tureserva-label {font-weight:600; display:block; margin-bottom:3px;}
        input[type="date"], input[type="number"], select, input[type="text"] {
            width:100%; padding:6px; border:1px solid #ccc; border-radius:6px;
        }
    </style>

    <div class="tureserva-field">
        <label class="tureserva-label">Check-in</label>
        <input type="date" name="tureserva_checkin" value="<?php echo esc_attr($checkin); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Check-out</label>
        <input type="date" name="tureserva_checkout" value="<?php echo esc_attr($checkout); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Adultos</label>
        <input type="number" name="tureserva_adultos" value="<?php echo esc_attr($adultos); ?>" min="1">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Niños</label>
        <input type="number" name="tureserva_ninos" value="<?php echo esc_attr($ninos); ?>" min="0">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Alojamiento</label>
        <select name="tureserva_alojamiento_id">
            <option value="0"><?php _e('Seleccionar alojamiento', 'tureserva'); ?></option>
            <?php
            $alojamientos = get_posts(array(
                'post_type'      => 'tureserva_alojamiento',
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

    <div class="tureserva-field">
        <label class="tureserva-label">Precio total (USD)</label>
        <input type="number" step="0.01" name="tureserva_precio_total" value="<?php echo esc_attr($precio); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Cliente</label>
        <input type="text" name="tureserva_cliente_nombre" value="<?php echo esc_attr($cliente); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Estado</label>
        <select name="tureserva_estado">
            <?php
            $estados = [
                'pendiente'  => 'Pendiente',
                'confirmada' => 'Confirmada',
                'cancelada'  => 'Cancelada'
            ];
            foreach ($estados as $valor => $label) {
                echo '<option value="' . esc_attr($valor) . '"' . selected($estado, $valor, false) . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
    </div>

    <?php
}


// ==========================================================
// 💾 GUARDAR DATOS DEL META BOX
