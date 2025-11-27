<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * CPT: Tarifas — TuReserva (versión corregida y estandarizada)
 * ==========================================================
 * Cambios importantes:
 * ----------------------------------------------------------
 * ✔ CPT renombrado a: tureserva_tarifa
 * ✔ Estándar de etiquetas unificado
 * ✔ CPT incrustado dentro del menú Alojamientos
 * ✔ Metabox corregido y vinculado al CPT correcto
 * ✔ Sanitización mejorada
 * ✔ Comentarios en cada bloque
 * ==========================================================
 */

// ==========================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE DE TARIFAS
// ==========================================================
function tureserva_register_tarifas_cpt()
{
    // 🏷️ Etiquetas completas
    $labels = array(
        'name'               => __('Tarifas', 'tureserva'),
        'singular_name'      => __('Tarifa', 'tureserva'),
        'menu_name'          => __('Tarifas', 'tureserva'),
        'add_new'            => __('Añadir nueva', 'tureserva'),
        'add_new_item'       => __('Añadir nueva tarifa', 'tureserva'),
        'edit_item'          => __('Editar tarifa', 'tureserva'),
        'new_item'           => __('Nueva tarifa', 'tureserva'),
        'view_item'          => __('Ver tarifa', 'tureserva'),
        'search_items'       => __('Buscar tarifas', 'tureserva'),
        'not_found'          => __('No se encontraron tarifas', 'tureserva'),
        'all_items'          => __('Todas las tarifas', 'tureserva'),
    );

    // ⚙️ Configuración del CPT
    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=trs_alojamiento', // ✔ Dentro del módulo Alojamiento
        'supports'           => array('title'),
        'menu_position'      => 8,
        'show_in_rest'       => false, // Gutenberg desactivado (más estable)
    );

    // ✔ Nombre final del CPT corregido
    register_post_type('tureserva_tarifa', $args);
}
add_action('init', 'tureserva_register_tarifas_cpt');


// ==========================================================
// 🧰 REGISTRO DEL METABOX
// ==========================================================
function tureserva_add_tarifas_metabox()
{
    add_meta_box(
        'tureserva_tarifas_metabox',
        __('Configuración de tarifas y precios variables', 'tureserva'),
        'tureserva_render_tarifas_metabox',
        'tureserva_tarifa',  // ✔ CPT correcto
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_tarifas_metabox');


// ==========================================================
// 🧱 RENDER DEL FORMULARIO DEL METABOX
// ==========================================================
// ==========================================================
// 🧱 RENDER DEL FORMULARIO DEL METABOX (REDISEÑADO)
// ==========================================================
function tureserva_render_tarifas_metabox($post)
{
    // Datos Globales
    $alojamiento_id = get_post_meta($post->ID, '_tureserva_alojamiento_id', true);
    $precios = get_post_meta($post->ID, '_tureserva_precios', true); // Nueva estructura de datos
    if (!is_array($precios)) $precios = [];

    // Recursos
    $alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => -1]);
    $temporadas = get_posts(['post_type' => 'temporada', 'posts_per_page' => -1]);

    wp_nonce_field('tureserva_save_tarifas', 'tureserva_tarifas_nonce');
    ?>

    <div class="tureserva-metabox-wrapper">
        
        <!-- 1. Configuración Global -->
        <div class="tureserva-global-settings">
            <label>🏠 Alojamiento Asociado:</label>
            <select name="tureserva_alojamiento_id">
                <option value="">-- Seleccionar Alojamiento --</option>
                <?php foreach ($alojamientos as $a) : ?>
                    <option value="<?php echo $a->ID; ?>" <?php selected($alojamiento_id, $a->ID); ?>>
                        <?php echo esc_html($a->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 2. Bloques de Temporada -->
        <div id="tureserva-season-blocks-container">
            <?php 
            if (empty($precios)) {
                // Bloque vacío por defecto
                tureserva_render_season_block(0, [], $temporadas);
            } else {
                foreach ($precios as $index => $data) {
                    tureserva_render_season_block($index, $data, $temporadas);
                }
            }
            ?>
        </div>

        <!-- 3. Botón Añadir Temporada -->
        <div id="add-season-block-btn" class="tureserva-add-season-btn">
            <span class="dashicons dashicons-plus"></span> Añadir nuevo precio de temporada
        </div>

    </div>
    <?php
}

// ==========================================================
// 🔁 RENDER DE UN BLOQUE DE TEMPORADA
// ==========================================================
function tureserva_render_season_block($index, $data, $temporadas) {
    $temporada_id = $data['temporada_id'] ?? '';
    $precio_base = $data['precio_base'] ?? '';
    $adultos = $data['adultos'] ?? 1;
    $ninos = $data['ninos'] ?? 0;
    $variables = $data['variables'] ?? [];
    ?>
    <div class="tureserva-season-block" data-index="<?php echo $index; ?>">
        <div class="tureserva-season-header">
            <div class="tureserva-season-title">
                <span class="dashicons dashicons-calendar-alt"></span>
                <select name="tureserva_precios[<?php echo $index; ?>][temporada_id]" style="border:none; background:transparent; font-weight:600;">
                    <option value="">-- Seleccionar Temporada --</option>
                    <?php foreach ($temporadas as $t) : ?>
                        <option value="<?php echo $t->ID; ?>" <?php selected($temporada_id, $t->ID); ?>>
                            <?php echo esc_html($t->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="tureserva-season-actions">
                <button type="button" class="btn-icon remove-season-block" title="Eliminar bloque">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>

        <div class="tureserva-season-content">
            <!-- Grid Principal -->
            <div class="tureserva-main-grid">
                <div class="ts-form-group">
                    <label>Precio Base (por noche)</label>
                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][precio_base]" value="<?php echo esc_attr($precio_base); ?>" placeholder="0.00">
                </div>
                <div class="ts-form-group">
                    <label>Adultos incluidos</label>
                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][adultos]" value="<?php echo esc_attr($adultos); ?>">
                </div>
                <div class="ts-form-group">
                    <label>Niños incluidos</label>
                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][ninos]" value="<?php echo esc_attr($ninos); ?>">
                </div>
            </div>

            <!-- Precios Variables -->
            <div class="tureserva-variable-prices">
                <div class="tureserva-variable-header">
                    <h4>Precios Variables (por duración)</h4>
                    <button type="button" class="btn-add-variable" data-index="<?php echo $index; ?>">
                        + Agregar precio variable
                    </button>
                </div>
                
                <div class="tureserva-variable-rows">
                    <?php if (!empty($variables)) : ?>
                        <?php foreach ($variables as $vIndex => $var) : ?>
                            <div class="tureserva-variable-row">
                                <div class="ts-form-group">
                                    <label>Desde (Noches)</label>
                                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][variables][<?php echo $vIndex; ?>][min]" value="<?php echo esc_attr($var['min']); ?>">
                                </div>
                                <div class="ts-form-group">
                                    <label>Hasta (Noches)</label>
                                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][variables][<?php echo $vIndex; ?>][max]" value="<?php echo esc_attr($var['max']); ?>">
                                </div>
                                <div class="ts-form-group">
                                    <label>Precio por noche</label>
                                    <input type="number" name="tureserva_precios[<?php echo $index; ?>][variables][<?php echo $vIndex; ?>][price]" value="<?php echo esc_attr($var['price']); ?>">
                                </div>
                                <button type="button" class="btn-icon remove-variable" title="Eliminar">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// ==========================================================
// 💾 GUARDAR DATOS (NUEVA ESTRUCTURA)
// ==========================================================
function tureserva_save_tarifas_metabox($post_id)
{
    if (!isset($_POST['tureserva_tarifas_nonce']) || !wp_verify_nonce($_POST['tureserva_tarifas_nonce'], 'tureserva_save_tarifas')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // 1. Guardar Alojamiento ID
    if (isset($_POST['tureserva_alojamiento_id'])) {
        update_post_meta($post_id, '_tureserva_alojamiento_id', sanitize_text_field($_POST['tureserva_alojamiento_id']));
    }

    // 2. Guardar Precios (Estructura compleja)
    $precios = $_POST['tureserva_precios'] ?? [];
    $sanitized_precios = [];

    // Variables para determinar las fechas globales de la tarifa (basadas en la primera temporada válida)
    $global_start = '';
    $global_end = '';

    foreach ($precios as $index => $p) {
        $temporada_id = sanitize_text_field($p['temporada_id'] ?? '');
        
        $bloque = [
            'temporada_id' => $temporada_id,
            'precio_base'  => floatval($p['precio_base'] ?? 0),
            'adultos'      => intval($p['adultos'] ?? 1),
            'ninos'        => intval($p['ninos'] ?? 0),
            'variables'    => []
        ];

        if (isset($p['variables']) && is_array($p['variables'])) {
            foreach ($p['variables'] as $v) {
                $bloque['variables'][] = [
                    'min'   => intval($v['min'] ?? 0),
                    'max'   => intval($v['max'] ?? 0),
                    'price' => floatval($v['price'] ?? 0),
                ];
            }
        }
        $sanitized_precios[] = $bloque;

        // Si es el primer bloque y tiene temporada, obtenemos sus fechas para la tarifa global
        if ($index === 0 && !empty($temporada_id)) {
            $global_start = get_post_meta($temporada_id, '_tureserva_fecha_inicio', true);
            $global_end   = get_post_meta($temporada_id, '_tureserva_fecha_fin', true);
        }
    }

    update_post_meta($post_id, '_tureserva_precios', $sanitized_precios);
    
    // 3. Guardar fechas globales para que core-pricing.php pueda encontrar esta tarifa
    if ($global_start && $global_end) {
        update_post_meta($post_id, '_tureserva_fecha_inicio', $global_start);
        update_post_meta($post_id, '_tureserva_fecha_fin', $global_end);
    }

    // Compatibilidad con la vista de lista
    if (!empty($sanitized_precios)) {
        update_post_meta($post_id, '_tureserva_precio_base', $sanitized_precios[0]['precio_base']);
        update_post_meta($post_id, '_tureserva_temporada_id', $sanitized_precios[0]['temporada_id']);
    }
}
add_action('save_post_tureserva_tarifa', 'tureserva_save_tarifas_metabox');
