<?php
/**
 * ==========================================================
 * CORE: Pagos con Tarjeta de Crédito — TuReserva
 * ==========================================================
 * Módulo central para procesar pagos con Stripe.
 * Permite crear cargos, registrar pagos en reservas
 * y mantener un historial en el CPT "pagos".
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ CONFIGURACIÓN POR DEFECTO
// =======================================================
if ( ! function_exists( 'tureserva_payments_default_options' ) ) {
    function tureserva_payments_default_options() {
        if ( ! get_option( 'tureserva_stripe_secret_key' ) ) {
            update_option( 'tureserva_stripe_secret_key', '' );
        }
        if ( ! get_option( 'tureserva_stripe_public_key' ) ) {
            update_option( 'tureserva_stripe_public_key', '' );
        }
        if ( ! get_option( 'tureserva_stripe_mode' ) ) {
            update_option( 'tureserva_stripe_mode', 'test' ); // test | live
        }
    }
}
add_action( 'tureserva_activated', 'tureserva_payments_default_options' );

// =======================================================
// 💳 CREAR UN PAGO EN STRIPE
// =======================================================
if ( ! function_exists( 'tureserva_create_stripe_payment' ) ) {
    function tureserva_create_stripe_payment( $reserva_id, $amount, $currency = 'usd', $token = '' ) {

        $secret = get_option( 'tureserva_stripe_secret_key' );

        if ( empty( $secret ) ) {
            error_log('[TuReserva Payments] ❌ Stripe no configurado.');
            return false;
        }

        // Petición directa a la API de Stripe
        $response = wp_remote_post( 'https://api.stripe.com/v1/charges', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret,
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'amount'      => intval( $amount * 100 ), // Stripe usa centavos
                'currency'    => strtolower( $currency ),
                'source'      => $token,
                'description' => 'Pago de reserva #' . $reserva_id,
            ),
            'timeout' => 20,
        ));

        // Validación de errores
        if ( is_wp_error( $response ) ) {
            error_log('[TuReserva Payments] ❌ Error de conexión: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Éxito del pago
        if ( $code === 200 && ! empty( $body['status'] ) && $body['status'] === 'succeeded' ) {

            // Guardar datos en la reserva
            update_post_meta( $reserva_id, '_tureserva_pago_estado', 'pagado' );
            update_post_meta( $reserva_id, '_tureserva_pago_id', $body['id'] );
            update_post_meta( $reserva_id, '_tureserva_pago_monto', $amount );
            update_post_meta( $reserva_id, '_tureserva_pago_moneda', $currency );

            // Acción hook personalizable
            do_action( 'tureserva_pago_confirmado', $reserva_id, $body );

            // Registro en historial de pagos
            $cliente_nombre = get_post_meta( $reserva_id, '_tureserva_cliente_nombre', true ) ?: 'Cliente';

            $post_id = wp_insert_post( array(
                'post_type'   => 'pagos',
                'post_status' => 'publish',
                'post_title'  => 'Pago de reserva #' . $reserva_id,
                'meta_input'  => array(
                    '_tureserva_reserva_id'     => $reserva_id,
                    '_tureserva_cliente_nombre' => $cliente_nombre,
                    '_tureserva_pago_estado'    => 'pagado',

