<?php
/**
 * ==========================================================
 * META BOXES – RESERVAS
 * ==========================================================
 * Crea los campos personalizados del CPT "reservas"
 * para gestionar la información detallada de cada reserva.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎯 REGISTRO DEL META BOX PRINCIPAL
// ==========================================================
function tureserva_add_reservas_metaboxes() {
    add_meta_box(
        'tureserva_reserva_detalles',
        'Detalles de la Reserva',
        'tureserva_render_reserva_metabox',
        'reservas',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_reservas_metaboxes');

// ==========================================================
// 🧾 RENDER DEL FORMULARIO
// ==========================================================
function tureserva_render_reserva_metabox($post) {
    $checkin   = get_post_meta($post->ID, '_tureserva_checkin', true);
    $checkout  = get_post_meta($post->ID, '_tureserva_checkout', true);
    $adultos   = get_post_meta($post->ID, '_tureserva_adultos', true);
    $ninos     = get_post_meta($post->ID, '_tureserva_ninos', true);
    $alojamiento = get_post_meta($post->ID, '_tureserva_alojamiento', true);
    $precio    = get_post_meta($post->ID, '_tureserva_precio', true);
    $cliente   = get_post_meta($post->ID, '_tureserva_cliente', true);
    $estado    = get_post_meta($post->ID, '_tureserva_estado', true);

    wp_nonce_field('tureserva_save_reserva', 'tureserva_reserva_nonce');
    ?>

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
        <label class="tureserva-label">Tipo de alojamiento</label>
        <select name="tureserva_alojamiento">
            <?php
            $alojamientos = get_posts(array('post_type' => 'alojamientos', 'numberposts' => -1));
            foreach ($alojamientos as $a) {
                echo '<option value="' . esc_attr($a->ID) . '"' . selected($alojamiento, $a->ID, false) . '>' . esc_html($a->post_title) . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Precio total (USD)</label>
        <input type="number" step="0.01" name="tureserva_precio" value="<?php echo esc_attr($precio); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Cliente</label>
        <input type="text" name="tureserva_cliente" value="<?php echo esc_attr($cliente); ?>">
    </div>

    <div class="tureserva-field">
        <label class="tureserva-label">Estado</label>
        <select name="tureserva_estado">
            <?php
            $estados = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'];
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
// ==========================================================
function tureserva_save_reserva_metabox($post_id) {
    if (!isset($_POST['tureserva_reserva_nonce']) || !wp_verify_nonce($_POST['tureserva_reserva_nonce'], 'tureserva_save_reserva')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'tureserva_checkin', 'tureserva_checkout', 'tureserva_adultos',
        'tureserva_ninos', 'tureserva_alojamiento', 'tureserva_precio',
        'tureserva_cliente', 'tureserva_estado'
    ];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            update_post_meta($post_id, '_' . $f, sanitize_text_field($_POST[$f]));
        }
    }
}
add_action('save_post_reservas', 'tureserva_save_reserva_metabox');
