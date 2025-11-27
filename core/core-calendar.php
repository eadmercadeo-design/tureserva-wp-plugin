<?php
/**
 * ==========================================================
 * CORE: Calendario de Reservas — TuReserva
 * ==========================================================
 * Devuelve los datos de reservas y bloqueos en formato JSON.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📅 ENDPOINT AJAX
// =======================================================
add_action('wp_ajax_tureserva_get_calendar', 'tureserva_get_calendar');
add_action('wp_ajax_tureserva_get_resources', 'tureserva_get_calendar'); // Reutilizamos la misma función
function tureserva_get_calendar() {

    // Permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Acceso denegado');
    }

    // 🔒 Validar nonce (puedes comentar estas 3 líneas en local)
    if (!isset($_GET['security']) || !wp_verify_nonce($_GET['security'], 'tureserva_calendar_nonce')) {
        wp_send_json_error('Nonce inválido');
    }

    // =======================================================
    // 🏨 ENDPOINT: OBTENER RECURSOS (ALOJAMIENTOS)
    // =======================================================
    if (isset($_GET['action']) && $_GET['action'] === 'tureserva_get_resources') {
        $alojamientos = get_posts([
            'post_type'      => 'trs_alojamiento',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC'
        ]);

        $resources = [];
        foreach ($alojamientos as $aloj) {
            $resources[] = [
                'id'    => (string) $aloj->ID,
                'title' => $aloj->post_title
            ];
        }
        wp_send_json_success($resources);
    }

    $year        = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $alojamiento = isset($_GET['alojamiento']) ? intval($_GET['alojamiento']) : 0;
    $estado      = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';

    $eventos = [];

    // =======================================================
    // 🏨 1. Reservas
    // =======================================================
    $args = [
        'post_type'      => 'tureserva_reserva',
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
        $check_in   = get_post_meta($reserva->ID, '_tureserva_checkin', true);
        $check_out  = get_post_meta($reserva->ID, '_tureserva_checkout', true);
        $estado_res = get_post_meta($reserva->ID, '_tureserva_estado', true);
        $cliente    = get_post_meta($reserva->ID, '_tureserva_cliente_nombre', true);
        $aloj_id    = get_post_meta($reserva->ID, '_tureserva_alojamiento_id', true);
        $aloj_tit   = $aloj_id ? get_the_title($aloj_id) : 'Alojamiento no especificado';

        if (empty($check_in) || empty($check_out)) continue;

        $color = match ($estado_res) {
            'confirmada' => '#2ecc71',
            'pendiente'  => '#f1c40f',
            'cancelada'  => '#e74c3c',
            default      => '#3498db',
        };

        $eventos[] = [
            'id'         => $reserva->ID,
            'resourceId' => (string) $aloj_id, // 🔑 CLAVE para vista Timeline
            'title'      => "{$cliente}",
            'start'      => $check_in,
            'end'        => $check_out,
            'color'      => $color,
            'textColor'  => '#fff',
            'borderColor'=> $color,
            'extendedProps' => [
                'estado'         => ucfirst($estado_res),
                'cliente'        => $cliente,
                'alojamiento'    => $aloj_tit,
                'alojamiento_id' => $aloj_id,
                'tipo'           => 'reserva',
                'link'           => get_edit_post_link($reserva->ID)
            ],
        ];
    }

    // =======================================================
    // 🚫 2. Bloqueos manuales
    // =======================================================
    $alojamientos = $alojamiento > 0
        ? [get_post($alojamiento)]
        : get_posts([
            'post_type'      => 'trs_alojamiento',
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
                'id'         => uniqid('bloqueo_'),
                'resourceId' => (string) $aloj->ID, // 🔑 CLAVE para vista Timeline
                'title'      => "⛔ {$motivo}",
                'start'      => $inicio,
                'end'        => $fin,
                'color'      => '#95a5a6',
                'textColor'  => '#fff',
                'borderColor'=> '#95a5a6',
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
        $anio = intval(substr($e['start'], 0, 4));
        return $anio === $year;
    });

    wp_send_json_success(array_values($eventos));
}
