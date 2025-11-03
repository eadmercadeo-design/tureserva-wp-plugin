<?php
/**
 * ==========================================================
 * CLASE: TuReserva – Pasarela Stripe
 * ==========================================================
 * Integra pagos reales con Stripe Checkout.
 * - Crea sesiones de pago
 * - Escucha webhooks
 * - Actualiza reservas al confirmar pago
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// Requiere la librería oficial de Stripe (instalada vía Composer)
require_once TURESERVA_PATH . 'vendor/autoload.php';

class TuReserva_Stripe_Gateway {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('tureserva_procesar_pago_stripe', [$this, 'crear_sesion_pago'], 10, 1);
    }

    /**
     * 🧾 Crear sesión de pago en Stripe
     */
    public function crear_sesion_pago($reserva_id) {
        $public_key = get_option('tureserva_stripe_public_key');
        $secret_key = get_option('tureserva_stripe_secret_key');

        if (!$secret_key) return;

        \Stripe\Stripe::setApiKey($secret_key);

        $total = get_post_meta($reserva_id, '_tureserva_total', true);
        $titulo = get_the_title($reserva_id);

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower(get_option('tureserva_moneda', 'usd')),
                        'product_data' => ['name' => $titulo],
                        'unit_amount' => intval($total * 100), // en centavos
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => home_url('/reserva-confirmada/?reserva=' . $reserva_id),
                'cancel_url'  => home_url('/reserva-cancelada/?reserva=' . $reserva_id),
                'metadata' => [
                    'reserva_id' => $reserva_id,
                ],
            ]);

            update_post_meta($reserva_id, '_tureserva_stripe_session', $session->id);
            wp_redirect($session->url);
            exit;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe error: ' . $e->getMessage());
        }
    }

    /**
     * 📡 Registrar webhook listener (REST API)
     */
    public function register_routes() {
        register_rest_route('tureserva/v1', '/stripe/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'webhook_handler'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * 📬 Manejar webhook de Stripe
     */
    public function webhook_handler(WP_REST_Request $request) {
        $payload = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = get_option('tureserva_stripe_webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (Exception $e) {
            return new WP_REST_Response(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $reserva_id = intval($session->metadata->reserva_id ?? 0);

            if ($reserva_id) {
                update_post_meta($reserva_id, '_tureserva_estado_pago', 'pagado');
                wp_update_post([
                    'ID' => $reserva_id,
                    'post_status' => 'publish'
                ]);
                error_log("✅ Stripe: Pago confirmado para la reserva #$reserva_id");
            }
        }

        return new WP_REST_Response(['status' => 'ok'], 200);
    }
}

add_action('plugins_loaded', function() {
    new TuReserva_Stripe_Gateway();
});
