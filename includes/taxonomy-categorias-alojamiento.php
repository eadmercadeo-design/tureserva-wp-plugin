<?php
/**
 * Campos personalizados para la taxonomía Categoría de Alojamiento
 * Permite asociar íconos SVG a cada categoría
 */

if (!defined('ABSPATH')) exit;

// === AGREGAR CAMPO EN EL FORMULARIO DE CREAR NUEVA CATEGORÍA === //
function tureserva_categoria_add_icon_field() { ?>
    <div class="form-field term-group">
        <label for="tureserva_icon"><?php _e('Ícono SVG', 'tureserva'); ?></label>
        <input type="hidden" id="tureserva_icon" name="tureserva_icon" value="">
        <div id="tureserva_icon_preview" style="margin-top:8px;"></div>
        <button type="button" class="button" id="tureserva_upload_icon"><?php _e('Seleccionar ícono', 'tureserva'); ?></button>
    </div>
<?php }
add_action('categoria_alojamiento_add_form_fields', 'tureserva_categoria_add_icon_field', 10, 2);


// === AGREGAR CAMPO EN EL FORMULARIO DE EDICIÓN === //
function tureserva_categoria_edit_icon_field($term) {
    $icon_id = get_term_meta($term->term_id, 'tureserva_icon', true);
    $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : '';
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="tureserva_icon"><?php _e('Ícono SVG', 'tureserva'); ?></label></th>
        <td>
            <input type="hidden" id="tureserva_icon" name="tureserva_icon" value="<?php echo esc_attr($icon_id); ?>">
            <div id="tureserva_icon_preview" style="margin-top:8px;">
                <?php if ($icon_url) echo '<img src="'.esc_url($icon_url).'" style="width:40px;height:auto;">'; ?>
            </div>
            <button type="button" class="button" id="tureserva_upload_icon"><?php _e('Seleccionar ícono', 'tureserva'); ?></button>
            <button type="button" class="button" id="tureserva_remove_icon"><?php _e('Eliminar', 'tureserva'); ?></button>
        </td>
    </tr>
<?php }
add_action('categoria_alojamiento_edit_form_fields', 'tureserva_categoria_edit_icon_field', 10, 2);


// === GUARDAR META DEL ÍCONO === //
function tureserva_save_categoria_icon($term_id) {
    if (isset($_POST['tureserva_icon']) && $_POST['tureserva_icon'] !== '') {
        update_term_meta($term_id, 'tureserva_icon', intval($_POST['tureserva_icon']));
    } else {
        delete_term_meta($term_id, 'tureserva_icon');
    }
}
add_action('created_categoria_alojamiento', 'tureserva_save_categoria_icon', 10, 2);
add_action('edited_categoria_alojamiento', 'tureserva_save_categoria_icon', 10, 2);


// === MOSTRAR COLUMNA DEL ÍCONO EN LA LISTA === //
function tureserva_add_categoria_icon_column($columns) {
    $columns['tureserva_icon'] = __('Ícono', 'tureserva');
    return $columns;
}
add_filter('manage_edit-categoria_alojamiento_columns', 'tureserva_add_categoria_icon_column');

function tureserva_show_categoria_icon_column($content, $column_name, $term_id) {
    if ($column_name === 'tureserva_icon') {
        $icon_id = get_term_meta($term_id, 'tureserva_icon', true);
        if ($icon_id) {
            $url = wp_get_attachment_url($icon_id);
            if ($url) {
                $content = '<img src="'.esc_url($url).'" style="width:30px;height:auto;">';
            }
        } else {
            $content = '—';
        }
    }
    return $content;
}
add_filter('manage_categoria_alojamiento_custom_column', 'tureserva_show_categoria_icon_column', 10, 3);


// === SCRIPTS PARA CARGADOR DE MEDIOS === //
function tureserva_categoria_icon_scripts($hook) {
    if (strpos($hook, 'edit-tags.php') === false) return;
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'categoria_alojamiento') return;
    wp_enqueue_media();
    ?>
    <script>
    jQuery(document).ready(function($){
        let frame;
        $('#tureserva_upload_icon').on('click', function(e){
            e.preventDefault();
            frame = wp.media({
                title: '<?php _e('Seleccionar ícono SVG', 'tureserva'); ?>',
                button: { text: '<?php _e('Usar este ícono', 'tureserva'); ?>' },
                multiple: false,
                library: { type: 'image/svg+xml' }
            });
            frame.on('select', function(){
                const attachment = frame.state().get('selection').first().toJSON();
                $('#tureserva_icon').val(attachment.id);
                $('#tureserva_icon_preview').html('<img src="'+attachment.url+'" style="width:40px;height:auto;">');
            });
            frame.open();
        });
        $('#tureserva_remove_icon').on('click', function(){
            $('#tureserva_icon').val('');
            $('#tureserva_icon_preview').html('');
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'tureserva_categoria_icon_scripts');

