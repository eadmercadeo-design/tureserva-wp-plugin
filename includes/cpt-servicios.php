<?php
/**
 * ==========================================================
 * CPT: Servicios — TuReserva (versión corregida y estandarizada)
 * ==========================================================
 * - Se muestra dentro del menú de Alojamientos
 * - Nombre del CPT corregido: tureserva_servicio
 * - Labels completos para mejor UX
 * - Gutenberg desactivado para mayor compatibilidad
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔧 REGISTRO DEL CPT SERVICIOS
// ==========================================================
function tureserva_register_servicio_cpt()
{
    // Etiquetas completas para WordPress Admin
    $labels = array(
        'name'               => __('Servicios', 'tureserva'),
        'singular_name'      => __('Servicio', 'tureserva'),
        'menu_name'          => __('Servicios', 'tureserva'),
        'add_new'            => __('Añadir nuevo', 'tureserva'),
        'add_new_item'       => __('Añadir nuevo servicio', 'tureserva'),
        'edit_item'          => __('Editar servicio', 'tureserva'),
        'new_item'           => __('Nuevo servicio', 'tureserva'),
        'view_item'          => __('Ver servicio', 'tureserva'),
        'search_items'       => __('Buscar servicios', 'tureserva'),
        'not_found'          => __('No se encontraron servicios', 'tureserva'),
        'not_found_in_trash' => __('No hay servicios en la papelera', 'tureserva'),
        'all_items'          => __('Todos los servicios', 'tureserva'),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => false,
        'show_ui'           => true,

        /**
         * 👇 Esto hace que Servicios aparezca dentro de Alojamientos:
         * Alojamientos →
         *      - Todos los alojamientos
         *      - Agregar nuevo
         *      - Servicios      👈 AHORA AQUÍ
         *      - Tarifas
         *      - Atributos
         */
        'show_in_menu'      => 'edit.php?post_type=trs_alojamiento',

        'menu_icon'         => 'dashicons-hammer',
        'supports'          => array('title', 'editor', 'thumbnail'),

        // Gutenberg desactivado para compatibilidad total
        'show_in_rest'      => false,

        'publicly_queryable'=> false,
        'has_archive'       => false,
        'rewrite'           => false
    );

    // CPT corregido y estandarizado
    register_post_type('tureserva_servicio', $args);
}
add_action('init', 'tureserva_register_servicio_cpt');


// ==========================================================
// 💰 METABOX: Configuración de Precio del Servicio
// ==========================================================
function tureserva_add_servicio_metabox() {
    add_meta_box(
        'tureserva_servicio_precio_mb',
        __('Configuración de Precio', 'tureserva'),
        'tureserva_render_servicio_metabox',
        'tureserva_servicio',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_servicio_metabox');

function tureserva_render_servicio_metabox($post) {
    $precio = get_post_meta($post->ID, '_tureserva_precio_servicio', true);
    $tipo = get_post_meta($post->ID, '_tureserva_tipo_servicio', true); // 'fijo' o 'por_dia'
    
    wp_nonce_field('tureserva_save_servicio', 'tureserva_servicio_nonce');
    ?>
    <table class="form-table">
        <tr>
            <th><label for="tureserva_precio_servicio"><?php _e('Precio', 'tureserva'); ?></label></th>
            <td>
                <input type="number" step="0.01" name="tureserva_precio_servicio" id="tureserva_precio_servicio" value="<?php echo esc_attr($precio); ?>" class="regular-text">
                <p class="description"><?php _e('Costo del servicio (dejar en 0 si es gratuito).', 'tureserva'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="tureserva_tipo_servicio"><?php _e('Tipo de Cobro', 'tureserva'); ?></label></th>
            <td>
                <select name="tureserva_tipo_servicio" id="tureserva_tipo_servicio">
                    <option value="fijo" <?php selected($tipo, 'fijo'); ?>><?php _e('Precio Fijo (por estancia)', 'tureserva'); ?></option>
                    <option value="por_dia" <?php selected($tipo, 'por_dia'); ?>><?php _e('Por Noche (multiplicado por duración)', 'tureserva'); ?></option>
                    <option value="por_persona" <?php selected($tipo, 'por_persona'); ?>><?php _e('Por Persona (por estancia)', 'tureserva'); ?></option>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

// ==========================================================
// 💾 GUARDAR METADATOS
// ==========================================================
function tureserva_save_servicio_metabox($post_id) {
    if (!isset($_POST['tureserva_servicio_nonce']) || !wp_verify_nonce($_POST['tureserva_servicio_nonce'], 'tureserva_save_servicio')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['tureserva_precio_servicio'])) {
        update_post_meta($post_id, '_tureserva_precio_servicio', floatval($_POST['tureserva_precio_servicio']));
    }

    if (isset($_POST['tureserva_tipo_servicio'])) {
        update_post_meta($post_id, '_tureserva_tipo_servicio', sanitize_text_field($_POST['tureserva_tipo_servicio']));
    }
}
add_action('save_post_tureserva_servicio', 'tureserva_save_servicio_metabox');
