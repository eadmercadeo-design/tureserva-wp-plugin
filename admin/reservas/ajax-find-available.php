<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tureserva_find_available_rooms', 'tureserva_find_available_rooms');
function tureserva_find_available_rooms() {
    // 1. Verificar nonce
    check_ajax_referer('tureserva_add_reserva_nonce', 'security');

    // 2. Sanitizar entradas
    $check_in  = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $adults    = intval($_POST['adults']);
    $children  = intval($_POST['children']);
    $type      = intval($_POST['alojamiento_type']);

    // Logging para depuración
    error_log("🔍 [TuReserva] Buscando disponibilidad: In=$check_in, Out=$check_out, Type=$type, Adults=$adults, Children=$children");

    if (empty($check_in) || empty($check_out)) {
        wp_send_json_error(__('Debe seleccionar las fechas de llegada y salida.', 'tureserva'));
    }

    // Validar que las fechas sean válidas
    $check_in_timestamp = strtotime($check_in);
    $check_out_timestamp = strtotime($check_out);
    
    if (!$check_in_timestamp || !$check_out_timestamp) {
        wp_send_json_error(__('Las fechas seleccionadas no son válidas.', 'tureserva'));
    }
    
    if ($check_in_timestamp >= $check_out_timestamp) {
        wp_send_json_error(__('La fecha de salida debe ser posterior a la fecha de llegada.', 'tureserva'));
    }

    // 3. Preparar argumentos para la búsqueda
    $args_extra = [];
    if ($type > 0) {
        $args_extra['tax_query'] = [
            [
                'taxonomy' => 'categoria_alojamiento',
                'field'    => 'term_id',
                'terms'    => $type
            ]
        ];
    }

    // 4. Usar la función CORE de disponibilidad
    // Esto asegura que se respeten las reservas existentes y los bloqueos.
    if (!function_exists('tureserva_buscar_alojamientos_disponibles')) {
        error_log("❌ [TuReserva] Error: La función tureserva_buscar_alojamientos_disponibles no existe.");
        wp_send_json_error(__('Error interno: Función de disponibilidad no encontrada.', 'tureserva'));
    }

    // Verificar que existen alojamientos antes de buscar disponibilidad
    $args_test = array_merge([
        'post_type'      => 'trs_alojamiento',
        'posts_per_page' => 1,
        'post_status'    => 'publish'
    ], $args_extra);
    
    $test_alojamientos = get_posts($args_test);
    error_log("🔍 [TuReserva] Total de alojamientos en BD (test): " . count($test_alojamientos));
    
    if (empty($test_alojamientos)) {
        error_log("⚠️ [TuReserva] No se encontraron alojamientos en la base de datos con los filtros aplicados.");
        wp_send_json_error(__('No se encontraron alojamientos en el sistema. Por favor, verifica que hay alojamientos creados y publicados.', 'tureserva'));
    }

    $alojamientos_disponibles = tureserva_buscar_alojamientos_disponibles($check_in, $check_out, $args_extra);

    error_log("✅ [TuReserva] Encontrados " . count($alojamientos_disponibles) . " alojamientos disponibles de " . count($test_alojamientos) . " totales.");

    // 5. Formatear resultados para el frontend
    $resultados = [];
    foreach ($alojamientos_disponibles as $post) {
        // Filtrar por capacidad si es necesario (opcional, pero recomendado)
        $capacidad = (int) get_post_meta($post->ID, '_tureserva_capacidad', true);
        $total_personas = $adults + $children;

        if ($total_personas > 0 && $capacidad > 0 && $capacidad < $total_personas) {
            error_log("⚠️ [TuReserva] Alojamiento #{$post->ID} ({$post->post_title}) no tiene capacidad suficiente (necesita $total_personas, tiene $capacidad)");
            continue; // Saltar si no cabe la gente
        }

        $precio_noche = get_post_meta($post->ID, '_tureserva_precio_noche', true);
        if (empty($precio_noche)) {
            $precio_noche = get_post_meta($post->ID, '_tureserva_precio_base', true);
        }

        $resultados[] = [
            'id' => $post->ID,
            'nombre' => $post->post_title,
            'capacidad' => $capacidad ?: '-',
            'precio' => $precio_noche ? number_format((float)$precio_noche, 2) : '0.00',
        ];
    }

    error_log("📊 [TuReserva] Retornando " . count($resultados) . " resultados al frontend.");
    wp_send_json_success($resultados);
}
