<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tureserva_create_reservation', 'tureserva_create_reservation_handler');

function tureserva_create_reservation_handler() {
    check_ajax_referer('tureserva_add_reserva_nonce', 'security');

    // Recoger datos del POST
    $alojamiento_id = intval($_POST['alojamiento_id']);
    $check_in       = sanitize_text_field($_POST['check_in']);
    $check_out      = sanitize_text_field($_POST['check_out']);
    $adults         = intval($_POST['adults']);
    $children       = intval($_POST['children']);
    
    $cliente_nombre = sanitize_text_field($_POST['cliente_nombre']);
    $cliente_email  = sanitize_email($_POST['cliente_email']);
    $cliente_tel    = sanitize_text_field($_POST['cliente_telefono']);

    if (empty($alojamiento_id) || empty($check_in) || empty($check_out)) {
        wp_send_json_error(__('Datos incompletos.', 'tureserva'));
    }

    if (empty($cliente_nombre) || empty($cliente_email)) {
        wp_send_json_error(__('Debe ingresar nombre y email del cliente.', 'tureserva'));
    }

    // Preparar argumentos para tureserva_crear_reserva
    $args = [
        'alojamiento_id' => $alojamiento_id,
        'check_in'       => $check_in,
        'check_out'      => $check_out,
        'huespedes'      => ['adultos' => $adults, 'ninos' => $children],
        'cliente'        => [
            'nombre'   => $cliente_nombre,
            'email'    => $cliente_email,
            'telefono' => $cliente_tel,
            'notas'    => 'Reserva creada manualmente desde el admin.'
        ],
        'estado'         => 'confirmada', // Por defecto confirmada si es manual
        'origen'         => 'manual'
    ];

    // Intentar crear la reserva
    $resultado = tureserva_crear_reserva($args);

    if (is_wp_error($resultado)) {
        wp_send_json_error($resultado->get_error_message());
    } else {
        wp_send_json_success([
            'message' => __('Reserva creada con éxito.', 'tureserva'),
            'id'      => $resultado,
            'redirect'=> admin_url('post.php?post=' . $resultado . '&action=edit')
        ]);
    }
}
