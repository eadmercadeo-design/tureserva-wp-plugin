<?php
/**
 * Meta Boxes: Alojamientos
 * Sistema nativo de campos personalizados (sin ACF)
 * Compatible con futuras integraciones (GitHub / Netlify / Supabase)
 */

if (!defined('ABSPATH')) exit;

// === Registrar el meta box principal === //
function tureserva_register_alojamiento_meta_boxes() {
    add_meta_box(
        'tureserva_alojamiento_detalles',
        __('Detalles del Alojamiento', 'tureserva'),
        'tureserva_render_alojamiento_meta_box',
        'alojamiento',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_register_alojamiento_meta_boxes');


// === Renderizar el meta box === //
function tureserva_render_alojamiento_meta_box($post) {
    wp_nonce_field(basename(__FILE__), 'tureserva_alojamiento_nonce');

    // Obtener valores guardados
    $fields = [
        'descripcion_corta', 'descripcion_larga', 'galeria', 'imagen_destacada', 'permitir_comentarios',
        'num_alojamientos', 'adultos', 'ninos', 'capacidad', 'camas', 'tamano',
        'tipo_cama', 'vista', 'politicas',
        'precio_base', 'precio_persona', 'moneda',
        'estado', 'orden', 'codigo_interno'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = get_post_meta($post->ID, "_tureserva_$f", true);

    // Obtener CPT servicios
    $servicios = get_posts(['post_type' => 'servicios', 'numberposts' => -1]);
    $servicios_seleccionados = (array)get_post_meta($post->ID, '_tureserva_servicios', true);

    ?>
    <div class="tureserva-tabs">
        <ul class="tabs">
            <li class="active" data-tab="info"><?php _e('Información General', 'tureserva'); ?></li>
            <li data-tab="comodidades"><?php _e('Comodidades', 'tureserva'); ?></li>
            <li data-tab="capacidad"><?php _e('Capacidad y Distribución', 'tureserva'); ?></li>
            <li data-tab="atributos"><?php _e('Atributos', 'tureserva'); ?></li>
            <li data-tab="tarifas"><?php _e('Tarifas', 'tureserva'); ?></li>
            <li data-tab="config"><?php _e('Configuración Avanzada', 'tureserva'); ?></li>
        </ul>

  <!-- Información General -->
<div class="tab-content active" id="info">
    <p><label><strong><?php _e('Descripción breve', 'tureserva'); ?></strong></label><br>
    <textarea name="tureserva_descripcion_corta" class="widefat"><?php echo esc_textarea($data['descripcion_corta']); ?></textarea></p>

    <p><label><strong><?php _e('Descripción completa', 'tureserva'); ?></strong></label><br>
    <?php
        wp_editor($data['descripcion_larga'], 'tureserva_descripcion_larga', [
            'textarea_name' => 'tureserva_descripcion_larga',
            'media_buttons' => true,
            'textarea_rows' => 6
        ]);
    ?></p>

    <!-- Galería de imágenes -->
    <p><label><strong><?php _e('Galería de imágenes', 'tureserva'); ?></strong></label></p>
    <div id="tureserva-gallery-container">
        <div class="tureserva-gallery-preview">
            <?php
            $galeria_ids = !empty($data['galeria']) ? explode(',', $data['galeria']) : [];
            foreach ($galeria_ids as $id) {
                $thumb = wp_get_attachment_image_src($id, [80, 80]);
                if ($thumb) {
                    echo '<div class="tureserva-thumb" data-id="'.$id.'" style="display:inline-block;position:relative;margin:4px;">';
                    echo '<img src="'.$thumb[0].'" style="width:80px;height:80px;border:1px solid #ccc;border-radius:4px;">';
                    echo '<span class="remove-thumb" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;text-align:center;line-height:18px;cursor:pointer;">×</span>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <input type="hidden" name="tureserva_galeria" id="tureserva_galeria" value="<?php echo esc_attr($data['galeria']); ?>">
        <button type="button" class="button" id="tureserva_gallery_button"><?php _e('Seleccionar / Editar galería', 'tureserva'); ?></button>
    </div>

    <!-- Imagen destacada -->
    <p><label><strong><?php _e('Imagen destacada', 'tureserva'); ?></strong></label></p>
    <div id="tureserva-featured-container">
        <?php if ($data['imagen_destacada']): ?>
            <div class="tureserva-featured-preview" style="position:relative;display:inline-block;">
                <?php echo wp_get_attachment_image($data['imagen_destacada'], [100,100], false, ['style'=>'border:1px solid #ccc;border-radius:4px;']); ?>
                <span class="remove-featured" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;text-align:center;line-height:18px;cursor:pointer;">×</span>
            </div>
        <?php endif; ?>
        <input type="hidden" name="tureserva_imagen_destacada" id="tureserva_imagen_destacada" value="<?php echo esc_attr($data['imagen_destacada']); ?>">
        <button type="button" class="button" id="tureserva_featured_button"><?php _e('Seleccionar imagen destacada', 'tureserva'); ?></button>
    </div>

    <p><label><input type="checkbox" name="tureserva_permitir_comentarios" value="1" <?php checked($data['permitir_comentarios'], '1'); ?>> <?php _e('Permitir comentarios de huéspedes', 'tureserva'); ?></label></p>
</div>

<script>
jQuery(document).ready(function($){

    // === GALERÍA ===
    $('#tureserva_gallery_button').on('click', function(e){
        e.preventDefault();
        let frame = wp.media({
            title: '<?php _e('Seleccionar imágenes para la galería', 'tureserva'); ?>',
            button: { text: '<?php _e('Usar estas imágenes', 'tureserva'); ?>' },
            multiple: true
        });
        frame.on('select', function(){
            let ids = [];
            let container = $('#tureserva-gallery-container .tureserva-gallery-preview');
            container.html('');
            frame.state().get('selection').each(function(attachment){
                ids.push(attachment.id);
                container.append(`
                    <div class="tureserva-thumb" data-id="${attachment.id}" style="display:inline-block;position:relative;margin:4px;">
                        <img src="${attachment.attributes.url}" style="width:80px;height:80px;border:1px solid #ccc;border-radius:4px;">
                        <span class="remove-thumb" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;text-align:center;line-height:18px;cursor:pointer;">×</span>
                    </div>
                `);
            });
            $('#tureserva_galeria').val(ids.join(','));
        });
        frame.open();
    });

    // Eliminar imágenes individuales de la galería
    $(document).on('click', '.tureserva-thumb .remove-thumb', function(){
        const id = $(this).parent().data('id').toString();
        let ids = $('#tureserva_galeria').val().split(',').filter(Boolean);
        ids = ids.filter(item => item !== id);
        $('#tureserva_galeria').val(ids.join(','));
        $(this).parent().fadeOut(200, function(){ $(this).remove(); });
    });

    // === IMAGEN DESTACADA ===
    $('#tureserva_featured_button').on('click', function(e){
        e.preventDefault();
        let frame = wp.media({
            title: '<?php _e('Seleccionar imagen destacada', 'tureserva'); ?>',
            button: { text: '<?php _e('Usar esta imagen', 'tureserva'); ?>' },
            multiple: false
        });
        frame.on('select', function(){
            let attachment = frame.state().get('selection').first().toJSON();
            $('#tureserva_imagen_destacada').val(attachment.id);
            $('#tureserva-featured-container .tureserva-featured-preview').remove();
            $('#tureserva-featured-container').prepend(`
                <div class="tureserva-featured-preview" style="position:relative;display:inline-block;">
                    <img src="${attachment.url}" style="width:100px;height:100px;border:1px solid #ccc;border-radius:4px;">
                    <span class="remove-featured" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;text-align:center;line-height:18px;cursor:pointer;">×</span>
                </div>
            `);
        });
        frame.open();
    });

    // Eliminar imagen destacada
    $(document).on('click', '.remove-featured', function(){
        $('#tureserva_imagen_destacada').val('');
        $(this).parent().fadeOut(200, function(){ $(this).remove(); });
    });
});
</script>
        <!-- Comodidades -->
        <div class="tab-content" id="comodidades">
            <p><label><strong><?php _e('Número de alojamientos reales', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_num_alojamientos" value="<?php echo esc_attr($data['num_alojamientos']); ?>" min="1"></p>

            <p><label><strong><?php _e('Servicios disponibles', 'tureserva'); ?></strong></label><br>
            <?php foreach ($servicios as $servicio): ?>
                <label style="display:block;">
                    <input type="checkbox" name="tureserva_servicios[]" value="<?php echo esc_attr($servicio->ID); ?>"
                        <?php checked(in_array($servicio->ID, $servicios_seleccionados)); ?>>
                    <?php echo esc_html($servicio->post_title); ?>
                </label>
            <?php endforeach; ?>
            </p>
        </div>

        <!-- Capacidad -->
        <div class="tab-content" id="capacidad">
            <p><label><strong><?php _e('Adultos máximos', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_adultos" value="<?php echo esc_attr($data['adultos']); ?>" min="0"></p>

            <p><label><strong><?php _e('Niños máximos', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_ninos" value="<?php echo esc_attr($data['ninos']); ?>" min="0"></p>

            <p><label><strong><?php _e('Número de camas', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_camas" value="<?php echo esc_attr($data['camas']); ?>" min="0"></p>

            <p><label><strong><?php _e('Tamaño (m²)', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_tamano" value="<?php echo esc_attr($data['tamano']); ?>" min="0"></p>
        </div>

        <!-- Atributos -->
        <div class="tab-content" id="atributos">
            <p><label><strong><?php _e('Tipo de cama', 'tureserva'); ?></strong></label><br>
            <select name="tureserva_tipo_cama" class="widefat">
                <?php
                $tipos = ['simple'=>'Simple','doble'=>'Doble','queen'=>'Queen','king'=>'King'];
                foreach ($tipos as $k=>$v) echo '<option value="'.$k.'" '.selected($data['tipo_cama'],$k,false).'>'.$v.'</option>';
                ?>
            </select></p>

            <p><label><strong><?php _e('Vista', 'tureserva'); ?></strong></label><br>
            <input type="text" name="tureserva_vista" value="<?php echo esc_attr($data['vista']); ?>" class="widefat" placeholder="<?php _e('Ej: Jardín, Lago, Montaña...', 'tureserva'); ?>"></p>

            <p><label><strong><?php _e('Políticas del alojamiento', 'tureserva'); ?></strong></label><br>
            <textarea name="tureserva_politicas" class="widefat"><?php echo esc_textarea($data['politicas']); ?></textarea></p>
        </div>

        <!-- Tarifas -->
        <div class="tab-content" id="tarifas">
            <p><label><strong><?php _e('Precio base por noche', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_precio_base" value="<?php echo esc_attr($data['precio_base']); ?>" step="0.01"></p>

            <p><label><strong><?php _e('Precio adicional por persona', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_precio_persona" value="<?php echo esc_attr($data['precio_persona']); ?>" step="0.01"></p>

            <p><label><strong><?php _e('Moneda', 'tureserva'); ?></strong></label><br>
            <input type="text" name="tureserva_moneda" value="<?php echo esc_attr($data['moneda']); ?>" placeholder="USD / COP / PAB" class="small-text"></p>
        </div>

        <!-- Configuración avanzada -->
        <div class="tab-content" id="config">
            <p><label><strong><?php _e('Estado', 'tureserva'); ?></strong></label><br>
            <select name="tureserva_estado" class="widefat">
                <option value="activo" <?php selected($data['estado'],'activo'); ?>><?php _e('Activo', 'tureserva'); ?></option>
                <option value="inactivo" <?php selected($data['estado'],'inactivo'); ?>><?php _e('Inactivo', 'tureserva'); ?></option>
            </select></p>

            <p><label><strong><?php _e('Orden de aparición', 'tureserva'); ?></strong></label><br>
            <input type="number" name="tureserva_orden" value="<?php echo esc_attr($data['orden']); ?>" min="0"></p>

            <p><label><strong><?php _e('Código interno / ID', 'tureserva'); ?></strong></label><br>
            <input type="text" name="tureserva_codigo_interno" value="<?php echo esc_attr($data['codigo_interno']); ?>" class="widefat"></p>
        </div>
    </div>

    <style>
        .tureserva-tabs ul.tabs { display:flex; gap:6px; margin:10px 0; }
        .tureserva-tabs ul.tabs li { background:#f1f1f1; padding:6px 12px; border-radius:4px; cursor:pointer; }
        .tureserva-tabs ul.tabs li.active { background:#0073aa; color:#fff; }
        .tab-content { display:none; background:#fff; padding:10px; border:1px solid #ddd; margin-bottom:10px; }
        .tab-content.active { display:block; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const tabs=document.querySelectorAll('.tureserva-tabs ul.tabs li');
            tabs.forEach(tab=>{
                tab.addEventListener('click', ()=>{
                    document.querySelector('.tureserva-tabs ul.tabs li.active').classList.remove('active');
                    tab.classList.add('active');
                    const id=tab.dataset.tab;
                    document.querySelectorAll('.tureserva-tabs .tab-content').forEach(c=>c.classList.remove('active'));
                    document.getElementById(id).classList.add('active');
                });
            });
        });
    </script>
    <?php
}


// === Guardar campos === //
function tureserva_save_alojamiento_meta($post_id) {
    if (!isset($_POST['tureserva_alojamiento_nonce']) || !wp_verify_nonce($_POST['tureserva_alojamiento_nonce'], basename(__FILE__))) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $campos = [
        'descripcion_corta','descripcion_larga','galeria','imagen_destacada','permitir_comentarios',
        'num_alojamientos','adultos','ninos','capacidad','camas','tamano',
        'tipo_cama','vista','politicas',
        'precio_base','precio_persona','moneda',
        'estado','orden','codigo_interno'
    ];
    foreach ($campos as $c) {
        $valor = isset($_POST["tureserva_$c"]) ? sanitize_text_field($_POST["tureserva_$c"]) : '';
        update_post_meta($post_id, "_tureserva_$c", $valor);
    }

    // Guardar servicios (array)
    $servicios = isset($_POST['tureserva_servicios']) ? array_map('intval', $_POST['tureserva_servicios']) : [];
    update_post_meta($post_id, '_tureserva_servicios', $servicios);

    // Activar/desactivar comentarios nativos según checkbox
    $permitir = isset($_POST['tureserva_permitir_comentarios']) ? 1 : 0;
    wp_update_post(['ID' => $post_id, 'comment_status' => $permitir ? 'open' : 'closed']);
}
add_action('save_post_alojamiento', 'tureserva_save_alojamiento_meta');


// === Columnas personalizadas en el listado === //
function tureserva_alojamiento_columns($columns) {
    unset($columns['date']);
    $columns['tipo'] = __('Tipo', 'tureserva');
    $columns['capacidad'] = __('Capacidad', 'tureserva');
    $columns['cama'] = __('Cama', 'tureserva');
    $columns['servicios'] = __('Servicios', 'tureserva');
    $columns['date'] = __('Fecha', 'tureserva');
    return $columns;
}
add_filter('manage_alojamiento_posts_columns', 'tureserva_alojamiento_columns');

function tureserva_alojamiento_column_content($column, $post_id) {
    switch($column){
        case 'tipo':
            $terms = get_the_terms($post_id, 'tipo_alojamiento');
            echo $terms ? esc_html($terms[0]->name) : '-';
            break;
        case 'capacidad':
            $adultos = get_post_meta($post_id, '_tureserva_adultos', true);
            $ninos = get_post_meta($post_id, '_tureserva_ninos', true);
            echo '👨 '.$adultos.' / 🧒 '.$ninos;
            break;
        case 'cama':
            echo ucfirst(get_post_meta($post_id, '_tureserva_tipo_cama', true));
            break;
        case 'servicios':
            $servicios = (array)get_post_meta($post_id, '_tureserva_servicios', true);
            echo count($servicios);
            break;
    }
}
add_action('manage_alojamiento_posts_custom_column', 'tureserva_alojamiento_column_content', 10, 2);
