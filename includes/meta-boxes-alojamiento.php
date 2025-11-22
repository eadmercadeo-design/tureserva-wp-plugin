<?php
/**
 * Meta Boxes: Alojamientos — TuReserva
 * Diseño estilo MotoPress/WooCommerce con Pestañas
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🎨 ENCOLAR ASSETS (CSS/JS)
// ==========================================================
add_action('admin_enqueue_scripts', 'tureserva_enqueue_alojamiento_assets');
function tureserva_enqueue_alojamiento_assets($hook) {
    global $post;
    if (($hook === 'post-new.php' || $hook === 'post.php') && 'trs_alojamiento' === $post->post_type) {
        wp_enqueue_style('tureserva-tabs-css', TURESERVA_URL . 'assets/css/admin-alojamiento-tabs.css', [], '1.0');
        wp_enqueue_script('tureserva-tabs-js', TURESERVA_URL . 'assets/js/admin-alojamiento-tabs.js', ['jquery'], '1.0', true);
        wp_enqueue_media(); // Necesario para el uploader
    }
}

// ==========================================================
// 📦 REGISTRAR META BOX PRINCIPAL
// ==========================================================
function tureserva_register_alojamiento_meta_boxes() {
    // Remover cajas por defecto para integrarlas en los tabs
    remove_meta_box('postimagediv', 'trs_alojamiento', 'side');
    remove_meta_box('categoria_alojamientodiv', 'trs_alojamiento', 'side');
    remove_meta_box('tagsdiv-tureserva_etiqueta', 'trs_alojamiento', 'side');

    add_meta_box(
        'tureserva_alojamiento_data',
        __('Datos del Alojamiento', 'tureserva'),
        'tureserva_render_alojamiento_tabs',
        'trs_alojamiento',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_register_alojamiento_meta_boxes');


// ==========================================================
// 🖥️ RENDERIZAR INTERFAZ CON PESTAÑAS
// ==========================================================
function tureserva_render_alojamiento_tabs($post) {
    wp_nonce_field(basename(__FILE__), 'tureserva_alojamiento_nonce');

    // Recuperar valores
    $meta = get_post_meta($post->ID);
    $val = function($key) use ($meta) {
        return isset($meta["_tureserva_$key"][0]) ? $meta["_tureserva_$key"][0] : '';
    };

    // Servicios (array)
    $servicios_guardados = get_post_meta($post->ID, '_tureserva_servicios', true);
    if (!is_array($servicios_guardados)) $servicios_guardados = [];

    // Galería
    $galeria_ids = $val('galeria');
    $galeria_array = $galeria_ids ? explode(',', $galeria_ids) : [];

    // Imagen Destacada
    $thumb_id = get_post_thumbnail_id($post->ID);
    $thumb_url = $thumb_id ? wp_get_attachment_thumb_url($thumb_id) : '';

    ?>
    <div class="tureserva-tabs-wrapper">
        <!-- 🧭 MENÚ LATERAL -->
        <ul class="tureserva-tabs-nav">
            <li class="active"><a href="#tab-general"><span class="dashicons dashicons-admin-home"></span> General</a></li>
            <li><a href="#tab-capacidad"><span class="dashicons dashicons-groups"></span> Capacidad</a></li>
            <li><a href="#tab-servicios"><span class="dashicons dashicons-list-view"></span> Servicios</a></li>
            <li><a href="#tab-tarifas"><span class="dashicons dashicons-money-alt"></span> Tarifas</a></li>
            <li><a href="#tab-reglas"><span class="dashicons dashicons-calendar-alt"></span> Reglas</a></li>
            <li><a href="#tab-seo"><span class="dashicons dashicons-chart-bar"></span> SEO y Datos</a></li>
        </ul>

        <!-- 📄 CONTENIDO DE LOS TABS -->
        <div class="tureserva-tabs-content">

            <!-- TAB 1: INFORMACIÓN GENERAL -->
            <div id="tab-general" class="tureserva-tab-panel active">
                
                <div class="tureserva-row">
                    <!-- Columna Izquierda: Editor y Datos -->
                    <div class="tureserva-col" style="flex: 2;">
                        <div class="tureserva-field-group">
                            <label class="tureserva-label">Descripción del Alojamiento</label>
                            <?php 
                            wp_editor(get_post_field('post_content', $post->ID), 'post_content', [
                                'textarea_name' => 'post_content',
                                'media_buttons' => true,
                                'textarea_rows' => 8,
                                'teeny' => true
                            ]); 
                            ?>
                        </div>

                        <div class="tureserva-field-group">
                            <label class="tureserva-label">Extracto Corto</label>
                            <textarea name="tureserva_descripcion_corta" class="tureserva-textarea" rows="3"><?php echo esc_textarea($val('descripcion_corta')); ?></textarea>
                        </div>
                    </div>

                    <!-- Columna Derecha: Taxonomías e Imagen -->
                    <div class="tureserva-col" style="flex: 1; border-left: 1px solid #f0f0f1; padding-left: 20px;">
                        
                        <!-- Imagen Destacada -->
                        <div class="tureserva-field-group">
                            <label class="tureserva-label">Imagen Destacada</label>
                            <div id="tureserva-featured-image-wrapper">
                                <?php if($thumb_url): ?>
                                    <img src="<?php echo $thumb_url; ?>" style="max-width:100%; height:auto; border-radius:4px; border:1px solid #ddd;">
                                    <br><a href="#" id="tureserva-remove-featured">Quitar imagen</a>
                                <?php else: ?>
                                    <button type="button" id="tureserva-set-featured" class="button">Establecer imagen</button>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="_thumbnail_id" id="_thumbnail_id" value="<?php echo $thumb_id; ?>">
                        </div>

                        <!-- Categorías -->
                        <div class="tureserva-field-group">
                            <label class="tureserva-label">Categoría del Alojamiento</label>
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                <?php 
                                $cats = get_terms(['taxonomy' => 'categoria_alojamiento', 'hide_empty' => false]);
                                $post_cats = wp_get_post_terms($post->ID, 'categoria_alojamiento', ['fields' => 'ids']);
                                if($cats && !is_wp_error($cats)):
                                    foreach($cats as $cat): ?>
                                        <label style="display:block; margin-bottom:5px;">
                                            <input type="checkbox" name="tax_input[categoria_alojamiento][]" value="<?php echo $cat->term_id; ?>" 
                                            <?php echo in_array($cat->term_id, $post_cats) ? 'checked' : ''; ?>>
                                            <?php echo esc_html($cat->name); ?>
                                        </label>
                                    <?php endforeach;
                                else:
                                    echo '<p class="description">No hay categorías.</p>';
                                endif;
                                ?>
                            </div>
                        </div>

                        <!-- Etiquetas -->
                        <div class="tureserva-field-group">
                            <label class="tureserva-label">Etiquetas</label>
                            <textarea name="tax_input[tureserva_etiqueta]" rows="2" class="tureserva-textarea" placeholder="Separadas por comas"><?php 
                                echo esc_textarea(implode(', ', wp_get_post_terms($post->ID, 'tureserva_etiqueta', ['fields' => 'names']))); 
                            ?></textarea>
                        </div>

                    </div>
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Video (URL Opcional)</label>
                    <input type="url" name="tureserva_video" class="tureserva-input-text" value="<?php echo esc_attr($val('video')); ?>" placeholder="https://youtube.com/...">
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Galería de Imágenes</label>
                    <input type="hidden" id="tureserva_galeria_ids" name="tureserva_galeria" value="<?php echo esc_attr($galeria_ids); ?>">
                    <button type="button" id="tureserva-add-gallery" class="button">Añadir imágenes</button>
                    
                    <div class="tureserva-gallery-preview">
                        <?php foreach($galeria_array as $img_id): 
                            $url = wp_get_attachment_thumb_url($img_id);
                            if($url): ?>
                            <div class="tureserva-gallery-item" data-id="<?php echo $img_id; ?>">
                                <img src="<?php echo $url; ?>">
                                <span class="tureserva-gallery-remove">&times;</span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CAPACIDAD Y CONFIGURACIÓN -->
            <div id="tab-capacidad" class="tureserva-tab-panel">
                <div class="tureserva-row">
                    <div class="tureserva-col">
                        <label class="tureserva-label">Adultos Máximos</label>
                        <input type="number" name="tureserva_adultos" class="tureserva-input-text" value="<?php echo esc_attr($val('adultos')); ?>">
                    </div>
                    <div class="tureserva-col">
                        <label class="tureserva-label">Niños Máximos</label>
                        <input type="number" name="tureserva_ninos" class="tureserva-input-text" value="<?php echo esc_attr($val('ninos')); ?>">
                    </div>
                    <div class="tureserva-col">
                        <label class="tureserva-label">Capacidad Total</label>
                        <input type="number" name="tureserva_capacidad" class="tureserva-input-text" value="<?php echo esc_attr($val('capacidad')); ?>">
                    </div>
                </div>

                <br>

                <div class="tureserva-row">
                    <div class="tureserva-col">
                        <label class="tureserva-label">Tamaño (m²)</label>
                        <input type="number" name="tureserva_tamano" class="tureserva-input-text" value="<?php echo esc_attr($val('tamano')); ?>">
                    </div>
                    <div class="tureserva-col">
                        <label class="tureserva-label">Tipo de Cama</label>
                        <input type="text" name="tureserva_tipo_cama" class="tureserva-input-text" value="<?php echo esc_attr($val('tipo_cama')); ?>" placeholder="Ej: King Size">
                    </div>
                </div>

                <br>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Vista</label>
                    <select name="tureserva_vista" class="tureserva-select">
                        <option value="">Seleccionar...</option>
                        <?php 
                        $vistas = ['Ciudad', 'Mar', 'Montaña', 'Jardín', 'Piscina'];
                        foreach($vistas as $v) {
                            echo '<option value="'.$v.'" '.selected($val('vista'), $v, false).'>'.$v.'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Accesibilidad</label>
                    <textarea name="tureserva_accesibilidad" class="tureserva-textarea" rows="2" placeholder="Detalles de accesibilidad..."><?php echo esc_textarea($val('accesibilidad')); ?></textarea>
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">
                        <input type="checkbox" name="tureserva_mascotas" value="1" <?php checked($val('mascotas'), 1); ?>>
                        Mascotas Permitidas
                    </label>
                </div>
            </div>

            <!-- TAB 3: SERVICIOS -->
            <div id="tab-servicios" class="tureserva-tab-panel">
                <p class="description">Selecciona los servicios incluidos en este alojamiento.</p>
                <div class="tureserva-services-grid">
                    <?php 
                    $servicios_cpt = get_posts(['post_type' => 'tureserva_servicio', 'numberposts' => -1]);
                    if($servicios_cpt):
                        foreach($servicios_cpt as $srv): ?>
                            <div class="tureserva-service-item">
                                <label>
                                    <input type="checkbox" name="tureserva_servicios[]" value="<?php echo $srv->ID; ?>" 
                                    <?php echo in_array($srv->ID, $servicios_guardados) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($srv->post_title); ?>
                                </label>
                            </div>
                        <?php endforeach;
                    else:
                        echo '<p>No hay servicios registrados. <a href="edit.php?post_type=tureserva_servicio">Crear servicios</a></p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- TAB 4: TARIFAS -->
            <div id="tab-tarifas" class="tureserva-tab-panel">
                <div class="tureserva-field-group">
                    <label class="tureserva-label">Tarifa Base por Noche <span class="tureserva-tooltip" data-tip="Precio estándar fuera de temporada">?</span></label>
                    <input type="number" name="tureserva_precio_base" class="tureserva-input-text" step="0.01" value="<?php echo esc_attr($val('precio_base')); ?>">
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Tarifas por Temporada</label>
                    <p class="description">Las tarifas estacionales se configuran en el apartado <a href="edit.php?post_type=tureserva_tarifa" target="_blank">Tarifas</a> y se asignan automáticamente según las fechas.</p>
                </div>
            </div>

            <!-- TAB 5: REGLAS -->
            <div id="tab-reglas" class="tureserva-tab-panel">
                <div class="tureserva-row">
                    <div class="tureserva-col">
                        <label class="tureserva-label">Hora Check-in</label>
                        <input type="time" name="tureserva_checkin_time" class="tureserva-input-text" value="<?php echo esc_attr($val('checkin_time')); ?>">
                    </div>
                    <div class="tureserva-col">
                        <label class="tureserva-label">Hora Check-out</label>
                        <input type="time" name="tureserva_checkout_time" class="tureserva-input-text" value="<?php echo esc_attr($val('checkout_time')); ?>">
                    </div>
                </div>
                <br>
                <div class="tureserva-row">
                    <div class="tureserva-col">
                        <label class="tureserva-label">Mínimo Noches</label>
                        <input type="number" name="tureserva_min_noches" class="tureserva-input-text" value="<?php echo esc_attr($val('min_noches')); ?>">
                    </div>
                    <div class="tureserva-col">
                        <label class="tureserva-label">Máximo Noches</label>
                        <input type="number" name="tureserva_max_noches" class="tureserva-input-text" value="<?php echo esc_attr($val('max_noches')); ?>">
                    </div>
                </div>
                <br>
                <div class="tureserva-field-group">
                    <label class="tureserva-label">Días No Disponibles</label>
                    <?php 
                    $dias_bloqueados = get_post_meta($post->ID, '_tureserva_dias_no_disponibles', true) ?: [];
                    $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach($dias_semana as $dia) {
                        echo '<label style="margin-right:15px;"><input type="checkbox" name="tureserva_dias_no_disponibles[]" value="'.$dia.'" '.(in_array($dia, $dias_bloqueados)?'checked':'').'> '.$dia.'</label>';
                    }
                    ?>
                </div>
            </div>

            <!-- TAB 6: SEO Y DATOS -->
            <div id="tab-seo" class="tureserva-tab-panel">
                <div class="tureserva-field-group">
                    <label class="tureserva-label">Estado del Alojamiento</label>
                    <select name="tureserva_estado" class="tureserva-select">
                        <option value="activo" <?php selected($val('estado'), 'activo'); ?>>Activo</option>
                        <option value="mantenimiento" <?php selected($val('estado'), 'mantenimiento'); ?>>En Mantenimiento</option>
                        <option value="oculto" <?php selected($val('estado'), 'oculto'); ?>>Oculto</option>
                    </select>
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Prioridad de Orden</label>
                    <input type="number" name="tureserva_orden" class="tureserva-input-text" value="<?php echo esc_attr($val('orden')); ?>">
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Meta Descripción SEO</label>
                    <textarea name="tureserva_meta_desc" class="tureserva-textarea" rows="2"><?php echo esc_textarea($val('meta_desc')); ?></textarea>
                </div>

                <div class="tureserva-field-group">
                    <label class="tureserva-label">Palabras Clave SEO</label>
                    <input type="text" name="tureserva_meta_keywords" class="tureserva-input-text" value="<?php echo esc_attr($val('meta_keywords')); ?>">
                </div>
            </div>

        </div>
    </div>
    
    <!-- CSS para ocultar el editor nativo si se duplica -->
    <style>
        #postdivrich { display: none; }
    </style>
    <?php
}


// ==========================================================
// 💾 GUARDAR DATOS
// ==========================================================
function tureserva_save_alojamiento_meta($post_id) {
    if (!isset($_POST['tureserva_alojamiento_nonce']) || !wp_verify_nonce($_POST['tureserva_alojamiento_nonce'], basename(__FILE__))) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Campos de texto simple
    $campos = [
        'descripcion_corta', 'video', 'galeria',
        'adultos', 'ninos', 'capacidad', 'tamano', 'tipo_cama', 'vista', 'accesibilidad',
        'precio_base',
        'checkin_time', 'checkout_time', 'min_noches', 'max_noches',
        'estado', 'orden', 'meta_desc', 'meta_keywords'
    ];

    foreach ($campos as $c) {
        if (isset($_POST["tureserva_$c"])) {
            update_post_meta($post_id, "_tureserva_$c", sanitize_text_field($_POST["tureserva_$c"]));
        }
    }

    // Checkboxes
    update_post_meta($post_id, '_tureserva_mascotas', isset($_POST['tureserva_mascotas']) ? 1 : 0);

    // Arrays (Servicios y Días)
    $servicios = isset($_POST['tureserva_servicios']) ? array_map('intval', $_POST['tureserva_servicios']) : [];
    update_post_meta($post_id, '_tureserva_servicios', $servicios);

    $dias = isset($_POST['tureserva_dias_no_disponibles']) ? array_map('sanitize_text_field', $_POST['tureserva_dias_no_disponibles']) : [];
    update_post_meta($post_id, '_tureserva_dias_no_disponibles', $dias);
}
add_action('save_post_trs_alojamiento', 'tureserva_save_alojamiento_meta');
