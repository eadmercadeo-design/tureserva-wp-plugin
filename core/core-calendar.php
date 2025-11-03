<?php
/**
 * ==========================================================
 * CORE: Calendario de Reservas — TuReserva
 * ==========================================================
 * Genera los datos en formato JSON para el calendario
 * administrativo:
 *  - Reservas confirmadas, pendientes o canceladas
 *  - Bloqueos manuales de alojamientos
 *  - Filtros dinámicos por año, estado o alojamiento
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📅 ENDPOINT AJAX: Obtener eventos del calendario
// =======================================================
add_action('wp_ajax_tureserva_get_calendar', 'tureserva_get_calendar');
add_action('wp_ajax_nopriv_tureserva_get_calendar', 'tureserva_get_calendar');

function tureserva_get_calendar() {

    // 🚫 Validación de permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Acceso denegado');
    }

    // 🔒 Validación del nonce
    if (!isset($_GET['security']) || !wp_verify_nonce($_GET['security'], 'tureserva_calendar_nonce')) {
        wp_send_json_error('Nonce inválido');
    }

    // ===============================
    // 🔸 Parámetros recibidos
    // ===============================
    $year        = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $alojamiento = isset($_GET['alojamiento']) ? intval($_GET['alojamiento']) : 0;
    $estado      = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';

    $eventos = [];

    // =======================================================
    // 🏨 1. Cargar Reservas
    // =======================================================
    $args = [
        'post_type'      => 'tureserva_reservas',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => []
    ];

    if ($alojamiento > 0) {
        $args['meta_query'][] = [
            'key'     => '_tureserva_alojamiento_id',
            'value'   => $alojamiento,
            'compare' => '='
        ];
    }

    if (!empty($estado)) {
        $args['meta_query'][] = [
            'key'     => '_tureserva_estado',
            'value'   => $estado,
            'compare' => '='
        ];
    }

    $reservas = get_posts($args);

    foreach ($reservas as $reserva) {

        $check_in   = get_post_meta($reserva->ID, '_tureserva_check_in', true);
        $check_out  = get_post_meta($reserva->ID, '_tureserva_check_out', true);
        $estado_res = get_post_meta($reserva->ID, '_tureserva_estado', true);
        $cliente    = get_post_meta($reserva->ID, '_tureserva_cliente_nombre', true);
        $aloj_id    = get_post_meta($reserva->ID, '_tureserva_alojamiento_id', true);
        $aloj_tit   = $aloj_id ? get_the_title($aloj_id) : 'Alojamiento no especificado';

        if (empty($check_in) || empty($check_out)) continue;

        // 🎨 Color según estado
        $color = match ($estado_res) {
            'confirmada' => '#2ecc71', // verde
            'pendiente'  => '#f1c40f', // amarillo
            'cancelada'  => '#e74c3c', // rojo
            default      => '#3498db', // azul (otros estados)
        };

        // 🗓️ Construir evento
        $eventos[] = [
            'id'    => $reserva->ID,
            'title' => "🛏️ {$aloj_tit} — {$cliente}",
            'start' => $check_in,
            'end'   => $check_out,
            'color' => $color,
            'textColor' => '#fff',
            'borderColor' => $color,
            'extendedProps' => [
                'estado'      => ucfirst($estado_res),
                'cliente'     => $cliente,
                'alojamiento' => $aloj_tit,
                'tipo'        => 'reserva',
                'link'        => get_edit_post_link($reserva->ID)
            ],
        ];
    }

    // =======================================================
    // 🚫 2. Cargar Bloqueos Manuales
    // =======================================================
    $alojamientos = $alojamiento > 0
        ? [get_post($alojamiento)]
        : get_posts([
            'post_type'      => 'tureserva_alojamiento',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);

    foreach ($alojamientos as $aloj) {
        $bloqueos = get_post_meta($aloj->ID, '_tureserva_bloqueos', true);

        if (empty($bloqueos) || !is_array($bloqueos)) continue;

        foreach ($bloqueos as $bloqueo) {
            $inicio = $bloqueo['inicio'] ?? '';
            $fin    = $bloqueo['fin'] ?? '';
            $motivo = $bloqueo['motivo'] ?? 'Bloqueo manual';

            if (empty($inicio) || empty($fin)) continue;

            $eventos[] = [
                'id'    => uniqid('bloqueo_'),
                'title' => "⛔ {$aloj->post_title} — {$motivo}",
                'start' => $inicio,
                'end'   => $fin,
                'color' => '#95a5a6',
                'textColor' => '#fff',
                'borderColor' => '#95a5a6',
                'extendedProps' => [
                    'motivo'      => $motivo,
                    'tipo'        => 'bloqueo',
                    'alojamiento' => $aloj->post_title,
                ],
            ];
        }
    }

    // =======================================================
    // 🔎 3. Filtrar por año
    // =======================================================
    $eventos = array_filter($eventos, function ($e) use ($year) {
        if (empty($e['start'])) return false;
        $anio_evento = intval(substr($e['start'], 0, 4));
        return $anio_evento === $year;
    });

    // =======================================================
    // 📤 4. Enviar respuesta
    // =======================================================
    wp_send_json_success(array_values($eventos));
}
