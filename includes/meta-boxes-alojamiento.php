<?php
/**
 * Meta Boxes: Alojamientos
 * Sistema nativo de campos personalizados (sin ACF)
 */

if (!defined('ABSPATH')) exit;

// === Registrar el meta box principal === //
function tureserva_register_alojamiento_meta_boxes() {
    add_meta_box(
        'tureserva_alojamiento_detalles',
        __('Detalles del Alojamiento', 'tureserva'),
        'tureserva_render_alojamiento_meta_box',
        'trs_alojamiento',      // ← CPT CORREGIDO
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
    
    <!-- (SE MANTIENE TODO EL HTML EXACTAMENTE IGUAL) -->
    
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

    // Guardar servicios
    $servicios = isset($_POST['tureserva_servicios']) ? array_map('intval', $_POST['tureserva_servicios']) : [];
    update_post_meta($post_id, '_tureserva_servicios', $servicios);

    // Comentarios
    $permitir = isset($_POST['tureserva_permitir_comentarios']) ? 1 : 0;
    wp_update_post(['ID' => $post_id, 'comment_status' => $permitir ? 'open' : 'closed']);
}
add_action('save_post_trs_alojamiento', 'tureserva_save_alojamiento_meta');   // ← CORREGIDO


// === Columnas personalizadas === //
function tureserva_alojamiento_columns($columns) {
    unset($columns['date']);
    $columns['tipo'] = __('Tipo', 'tureserva');
    $columns['capacidad'] = __('Capacidad', 'tureserva');
    $columns['cama'] = __('Cama', 'tureserva');
    $columns['servicios'] = __('Servicios', 'tureserva');
    $columns['date'] = __('Fecha', 'tureserva');
    return $columns;
}
add_filter('manage_trs_alojamiento_posts_columns', 'tureserva_alojamiento_columns');  // ← CORREGIDO


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
add_action('manage_trs_alojamiento_posts_custom_column', 'tureserva_alojamiento_column_content', 10, 2); // ← CORREGIDO
