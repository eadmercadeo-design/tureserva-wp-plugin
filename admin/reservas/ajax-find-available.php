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
    error_log("🔍 [TuReserva] Buscando disponibilidad: In=$check_in, Out=$check_out, Type=$type, Adults=$adults");

    if (empty($check_in) || empty($check_out)) {
        wp_send_json_error(__('Debe seleccionar las fechas de llegada y salida.', 'tureserva'));
    }

    // 3. Preparar argumentos para la búsqueda
    $args_extra = [
        'tax_query' => $type ? [['taxonomy' => 'categoria_alojamiento', 'terms' => $type]] : [],
    ];

    // 4. Usar la función CORE de disponibilidad
    // Esto asegura que se respeten las reservas existentes y los bloqueos.
    if (!function_exists('tureserva_buscar_alojamientos_disponibles')) {
        error_log("❌ [TuReserva] Error: La función tureserva_buscar_alojamientos_disponibles no existe.");
        wp_send_json_error(__('Error interno: Función de disponibilidad no encontrada.', 'tureserva'));
    }

    $alojamientos_disponibles = tureserva_buscar_alojamientos_disponibles($check_in, $check_out, $args_extra);

    error_log("✅ [TuReserva] Encontrados " . count($alojamientos_disponibles) . " alojamientos disponibles.");

    // 5. Formatear resultados para el frontend
    $resultados = [];
    foreach ($alojamientos_disponibles as $post) {
        // Filtrar por capacidad si es necesario (opcional, pero recomendado)
        $capacidad = (int) get_post_meta($post->ID, '_tureserva_capacidad', true);
        $total_personas = $adults + $children;

        if ($total_personas > 0 && $capacidad < $total_personas) {
            continue; // Saltar si no cabe la gente
        }

        $resultados[] = [
            'id' => $post->ID,
            'nombre' => $post->post_title,
            'capacidad' => $capacidad,
            'precio' => get_post_meta($post->ID, '_tureserva_precio_noche', true),
        ];
    }

    wp_send_json_success($resultados);
}
