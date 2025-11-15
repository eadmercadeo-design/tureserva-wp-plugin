<?php
/**
 * ==========================================================
 * CPT: Temporadas — TuReserva (versión corregida y optimizada)
 * ==========================================================
 * - Se muestra dentro del menú Alojamientos
 * - Estandarizado según estructura del sistema TuReserva
 * - Metabox mejorado y validación adicional
 * - Columns optimizadas
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔧 REGISTRO DEL CPT
// ==========================================================
function tureserva_register_temporadas_cpt()
{
    $labels = array(
        'name'               => __('Temporadas', 'tureserva'),
        'singular_name'      => __('Temporada', 'tureserva'),
        'menu_name'          => __('Temporadas', 'tureserva'),
        'add_new'            => __('Añadir nueva', 'tureserva'),
        'add_new_item'       => __('Añadir nueva temporada', 'tureserva'),
        'edit_item'          => __('Editar temporada', 'tureserva'),
        'new_item'           => __('Nueva temporada', 'tureserva'),
        'view_item'          => __('Ver temporada', 'tureserva'),
        'search_items'       => __('Buscar temporadas', 'tureserva'),
        'not_found'          => __('No se encontraron temporadas', 'tureserva'),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => false,
        'show_ui'           => true,

        /**
         * 👇 Esto integra TEMPORADAS dentro de ALOJAMIENTOS:
         * Alojamientos →
         *     - Todos los alojamientos
         *     - Tarifas
         *     - Atributos
         *     - Temporadas  👈
         */
        'show_in_menu'      => 'edit.php?post_type=tureserva_alojamiento',

        'supports'          => array('title'),
        'menu_position'     => 7,
        'has_archive'       => false,
        'publicly_queryable'=> false,

        // Gutenberg OFF para compatibilidad con metaboxes
        'show_in_rest'      => false,
        'rewrite'           => false
    );

    register_post_type('temporada', $args);
}
add_action('init', 'tureserva_register_temporadas_cpt');


// ==========================================================
// 🧾 METABOX — Fechas y días aplicados
// ==========================================================
function tureserva_add_temporada_metabox()
{
    add_meta_box(
        'tureserva_temporada_metabox',
        __('Información de temporada', 'tureserva'),
        'tureserva_render_temporada_metabox',
        'temporada',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_temporada_metabox');


function tureserva_render_temporada_metabox($post)
{
    $fecha_inicio = get_post_meta($post->ID, '_tureserva_fecha_inicio', true);
    $fecha_fin = get_post_meta($post->ID, '_tureserva_fecha_fin', true);
    $dias = (array)get_post_meta($post->ID, '_tureserva_dias_aplicados', true);

    wp_nonce_field('tureserva_save_temporada', 'tureserva_temporada_nonce');
    ?>

    <style>
        .tureserva-table input,
        .tureserva-table select {
            width: 100%;
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    </style>

    <table class="form-table tureserva-table">
        <tr>
            <th><label>Fecha de inicio *</label></th>
            <td><input type="date" name="tureserva_fecha_inicio" value="<?php echo esc_attr($fecha_inicio); ?>"></td>
        </tr>
        <tr>
            <th><label>Fecha final *</label></th>
            <td><input type="date" name="tureserva_fecha_fin" value="<?php echo esc_attr($fecha_fin); ?>"></td>
        </tr>

        <tr>
            <th><label>Días aplicados *</label></th>
            <td>
                <select name="tureserva_dias_aplicados[]" multiple size="7" style="min-width:160px;">
                    <?php
                    $dias_semana = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
                    foreach ($dias_semana as $dia) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($dia),
                            in_array($dia, $dias) ? 'selected' : '',
                            esc_html($dia)
                        );
                    }
                    ?>
                </select>
                <p class="description">Use Ctrl/Cmd para seleccionar múltiples días.</p>
            </td>
        </tr>
    </table>

    <?php
}


// ==========================================================
// 💾 GUARDAR METADATOS
// ==========================================================
function tureserva_save_temporada_metabox($post_id)
{
    if (!isset($_POST['tureserva_temporada_nonce']) ||
        !wp_verify_nonce($_POST['tureserva_temporada_nonce'], 'tureserva_save_temporada')) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $inicio = sanitize_text_field($_POST['tureserva_fecha_inicio']);
    $fin = sanitize_text_field($_POST['tureserva_fecha_fin']);

    // Validación: fecha final debe ser >= fecha inicio
    if ($inicio && $fin && strtotime($fin) < strtotime($inicio)) {
        // Guardamos igual, pero prevenimos errores lógicos
        $tmp = $fin;
        $fin = $inicio;
        $inicio = $tmp;
    }

    update_post_meta($post_id, '_tureserva_fecha_inicio', $inicio);
    update_post_meta($post_id, '_tureserva_fecha_fin', $fin);

    $dias = isset($_POST['tureserva_dias_aplicados']) ? array_map('sanitize_text_field', $_POST['tureserva_dias_aplicados']) : [];
    update_post_meta($post_id, '_tureserva_dias_aplicados', $dias);
}
add_action('save_post_temporada', 'tureserva_save_temporada_metabox');


// ==========================================================
// 📊 COLUMNAS PERSONALIZADAS
// ==========================================================
function tureserva_temporadas_columns($columns)
{
    $new = [
        'inicio'  => __('Inicio', 'tureserva'),
        'final'   => __('Final', 'tureserva'),
        'dias'    => __('Días aplicados', 'tureserva'),
    ];

    return array_merge($columns, $new);
}
add_filter('manage_temporada_posts_columns', 'tureserva_temporadas_columns');


function tureserva_temporadas_custom_column($column, $post_id)
{
    switch ($column) {
        case 'inicio':
            echo esc_html(get_post_meta($post_id, '_tureserva_fecha_inicio', true));
            break;

        case 'final':
            echo esc_html(get_post_meta($post_id, '_tureserva_fecha_fin', true));
            break;

        case 'dias':
            $d = get_post_meta($post_id, '_tureserva_dias_aplicados', true);
            echo $d ? esc_html(implode(', ', $d)) : '—';
            break;
    }
}
add_action('manage_temporada_posts_custom_column', 'tureserva_temporadas_custom_column', 10, 2);
