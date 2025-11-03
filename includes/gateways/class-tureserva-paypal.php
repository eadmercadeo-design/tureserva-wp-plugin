<?php
/**
 * ==========================================================
 * CLASE: TuReserva – Pasarela PayPal
 * ==========================================================
 * Integra pagos reales con PayPal Checkout v2
 * - Crea órdenes
 * - Captura pago
 * - Escucha webhooks
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

class TuReserva_PayPal_Gateway {

    private $api_url;
    private $client_id;
    private $secret;

    public function __construct() {
        $sandbox = get_option('tureserva_paypal_sandbox', 1);
        $this->api_url = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->client_id = get_option('tureserva_paypal_client_id');
        $this->secret = get_option('tureserva_paypal_secret');

        add_action('tureserva_procesar_pago_paypal', [$this, 'crear_orden'], 10, 1);
        add_action('rest_api_init', [$this, 'register_webhook']);
    }

    /**
     * 🧾 Crear orden PayPal
     */
    public function crear_orden($reserva_id) {
        $total = get_post_meta($reserva_id, '_tureserva_total', true);
        $currency = get_option('tureserva_moneda', 'USD');

        $token = $this->get_access_token();
        if (!$token) return;

        $body = json_encode([
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($total, 2, '.', '')
                ],
                'description' => get_the_title($reserva_id)
            ]],
            'application_context' => [
                'return_url' => home_url('/reserva-confirmada/?reserva=' . $reserva_id),
                'cancel_url' => home_url('/reserva-cancelada/?reserva=' . $reserva_id),
            ]
        ]);

        $response = wp_remote_post($this->api_url . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $body
        ]);

        $data = json_decode(wp_remote_retrieve_body($response));

        if (!empty($data->links)) {
            foreach ($data->links as $link) {
                if ($link->rel === 'approve') {
                    update_post_meta($reserva_id, '_tureserva_paypal_order', $data->id);
                    wp_redirect($link->href);
                    exit;
                }
            }
        }
    }

    /**
     * 🔑 Obtener access token
     */
    private function get_access_token() {
        $response = wp_remote_post($this->api_url . '/v1/oauth2/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->secret),
            ],
            'body' => 'grant_type=client_credentials'
        ]);

        $data = json_decode(wp_remote_retrieve_body($response));
        return $data->access_token ?? null;
    }

    /**
     * 📡 Registrar webhook REST
     */
    public function register_webhook() {
        register_rest_route('tureserva/v1', '/paypal/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * 📬 Capturar webhook PayPal
     */
    public function handle_webhook(WP_REST_Request $request) {
        $body = json_decode($request->get_body());
        if (empty($body->resource->id)) return new WP_REST_Response(['error' => 'No order ID'], 400);

        $order_id = $body->resource->id;
        $reserva_id = $this->find_reserva_by_order($order_id);

        if ($reserva_id && $body->event_type === 'CHECKOUT.ORDER.APPROVED') {
            update_post_meta($reserva_id, '_tureserva_estado_pago', 'pagado');
            wp_update_post(['ID' => $reserva_id, 'post_status' => 'publish']);
            error_log("✅ PayPal: pago confirmado para reserva #$reserva_id");
        }

        return new WP_REST_Response(['ok' => true], 200);
    }

    private function find_reserva_by_order($paypal_order_id) {
        $query = new WP_Query([
            'post_type' => 'reserva',
            'meta_key' => '_tureserva_paypal_order',
            'meta_value' => $paypal_order_id,
            'posts_per_page' => 1
        ]);
        return $query->posts ? $query->posts[0]->ID : 0;
    }
}

add_action('plugins_loaded', function() {
    new TuReserva_PayPal_Gateway();
});
