<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tureserva_procesar_pago', 'tureserva_ajax_procesar_pago');
add_action('wp_ajax_nopriv_tureserva_procesar_pago', 'tureserva_ajax_procesar_pago');

function tureserva_ajax_procesar_pago() {
    $metodo = sanitize_text_field($_POST['metodo'] ?? '');
    $reserva_id = intval($_POST['reserva'] ?? 0);

    if (!$reserva_id) {
        wp_send_json(['message' => 'Reserva no encontrada.']);
    }

    switch ($metodo) {
        case 'stripe':
            do_action('tureserva_procesar_pago_stripe', $reserva_id);
            break;

        case 'paypal':
            do_action('tureserva_procesar_pago_paypal', $reserva_id);
            break;

        case 'transferencia':
            $info = get_option('tureserva_transferencia_instrucciones', 'Cuenta bancaria: XXXX');
            wp_send_json(['message' => $info]);
            break;

        case 'manual':
            update_post_meta($reserva_id, '_tureserva_estado_pago', 'pendiente');
            wp_send_json(['message' => 'Tu reserva ha sido registrada. Pago pendiente en efectivo.']);
            break;

        default:
            wp_send_json(['message' => 'Método de pago no válido.']);
    }
}
