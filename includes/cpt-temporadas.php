<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * CPT: Temporadas (Versión final estilo MotoPress)
 * ==========================================================
 */
function tureserva_register_temporadas_cpt() {

    $labels = array(
        'name'                  => 'Temporadas',
        'singular_name'         => 'Temporada',
        'menu_name'             => 'Temporadas',
        'add_new'               => 'Añadir nueva temporada',
        'add_new_item'          => 'Añadir nueva temporada',
        'edit_item'             => 'Editar temporada',
        'new_item'              => 'Nueva temporada',
        'view_item'             => 'Ver temporada',
        'search_items'          => 'Buscar temporadas',
        'not_found'             => 'No se encontraron temporadas',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'edit.php?post_type=alojamiento', // dentro de Alojamiento
        'supports'              => array('title'),
        'menu_position'         => 7,
        'has_archive'           => false,
        'show_in_rest'          => true,
    );

    register_post_type('temporada', $args);
}
add_action('init', 'tureserva_register_temporadas_cpt');


/**
 * ==========================================================
 * METABOX: Info de temporada (Fechas + Días aplicados)
 * ==========================================================
 */
function tureserva_add_temporada_metabox() {
    add_meta_box(
        'tureserva_temporada_metabox',
        'Info de temporada',
        'tureserva_render_temporada_metabox',
        'temporada',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_temporada_metabox');

function tureserva_render_temporada_metabox($post) {
    $fecha_inicio = get_post_meta($post->ID, '_tureserva_fecha_inicio', true);
    $fecha_fin = get_post_meta($post->ID, '_tureserva_fecha_fin', true);
    $dias = (array) get_post_meta($post->ID, '_tureserva_dias_aplicados', true);

    wp_nonce_field('tureserva_save_temporada', 'tureserva_temporada_nonce');
    ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_fecha_inicio">Fecha de inicio <span style="color:red;">*</span></label></th>
            <td><input type="date" id="tureserva_fecha_inicio" name="tureserva_fecha_inicio" value="<?php echo esc_attr($fecha_inicio); ?>" required></td>
        </tr>
        <tr>
            <th><label for="tureserva_fecha_fin">Fecha final <span style="color:red;">*</span></label></th>
            <td><input type="date" id="tureserva_fecha_fin" name="tureserva_fecha_fin" value="<?php echo esc_attr($fecha_fin); ?>" required></td>
        </tr>
        <tr>
            <th><label for="tureserva_dias_aplicados">Aplicado para días <span style="color:red;">*</span></label></th>
            <td>
                <select id="tureserva_dias_aplicados" name="tureserva_dias_aplicados[]" multiple size="7" style="min-width:150px;">
                    <?php
                    $dias_semana = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
                    foreach ($dias_semana as $dia) {
                        $selected = in_array($dia, $dias) ? 'selected' : '';
                        echo "<option value='{$dia}' {$selected}>{$dia}</option>";
                    }
                    ?>
                </select>
                <p class="description">Mantenga presionada la tecla Ctrl / Cmd para seleccionar múltiples.</p>
            </td>
        </tr>
    </table>

    <?php
}

/**
 * ==========================================================
 * GUARDAR DATOS DE LA TEMPORADA
 * ==========================================================
 */
function tureserva_save_temporada_metabox($post_id) {
    if (!isset($_POST['tureserva_temporada_nonce']) || !wp_verify_nonce($_POST['tureserva_temporada_nonce'], 'tureserva_save_temporada')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_tureserva_fecha_inicio', sanitize_text_field($_POST['tureserva_fecha_inicio']));
    update_post_meta($post_id, '_tureserva_fecha_fin', sanitize_text_field($_POST['tureserva_fecha_fin']));
    update_post_meta($post_id, '_tureserva_dias_aplicados', isset($_POST['tureserva_dias_aplicados']) ? array_map('sanitize_text_field', $_POST['tureserva_dias_aplicados']) : []);
}
add_action('save_post_temporada', 'tureserva_save_temporada_metabox');


/**
 * ==========================================================
 * COLUMNAS PERSONALIZADAS EN LA LISTA
 * ==========================================================
 */
function tureserva_temporadas_columns($columns) {
    $new_columns = array(
        'cb'      => '<input type="checkbox" />',
        'title'   => 'Título',
        'inicio'  => 'Inicio',
        'final'   => 'Final',
        'dias'    => 'Días'
    );
    return $new_columns;
}
add_filter('manage_temporada_posts_columns', 'tureserva_temporadas_columns');

function tureserva_temporadas_custom_column($column, $post_id) {
    switch ($column) {
        case 'inicio':
            echo esc_html(get_post_meta($post_id, '_tureserva_fecha_inicio', true));
            break;
        case 'final':
            echo esc_html(get_post_meta($post_id, '_tureserva_fecha_fin', true));
            break;
        case 'dias':
            $dias = get_post_meta($post_id, '_tureserva_dias_aplicados', true);
            echo $dias ? esc_html(implode(', ', $dias)) : '—';
            break;
    }
}
add_action('manage_temporada_posts_custom_column', 'tureserva_temporadas_custom_column', 10, 2);
