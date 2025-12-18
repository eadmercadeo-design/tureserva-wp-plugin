<?php
/**
 * PayPal Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TuReserva_Gateway_PayPal extends TuReserva_Payment_Gateway {

    public function __construct() {
        $this->id = 'paypal';
        $this->title = 'PayPal';
        $this->description = 'Acepta pagos vía PayPal Standard.';
        
        parent::__construct();
        
        add_action('rest_api_init', [$this, 'register_webhook']);
    }

    public function init_form_fields() {
        return [
            'enabled' => [
                'title'   => 'Habilitar/Deshabilitar',
                'type'    => 'checkbox',
                'label'   => 'Habilitar PayPal',
                'default' => 'no'
            ],
            'sandbox' => [
                'title'       => 'Modo Sandbox',
                'type'        => 'checkbox',
                'label'       => 'Habilitar modo pruebas (Sandbox)',
                'default'     => 'yes'
            ],
            'client_id' => [
                'title'       => 'Client ID',
                'type'        => 'text',
                'description' => 'PayPal Client ID.',
                'default'     => ''
            ],
            'secret' => [
                'title'       => 'Secret Key',
                'type'        => 'password',
                'description' => 'PayPal Secret Key.',
                'default'     => ''
            ]
        ];
    }

    public function get_api_url() {
        $sandbox = $this->get_option('sandbox', 'yes') === 'yes';
        return $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function process_payment( $reserva_id ) {
        // Create PayPal Order
        $client_id = $this->get_option('client_id');
        $secret = $this->get_option('secret');

        if ( ! $client_id || ! $secret ) {
             return ['result' => 'fail', 'message' => 'PayPal credentials missing.'];
        }

        $token = $this->get_access_token();
        if ( ! $token ) {
            return ['result' => 'fail', 'message' => 'Could not get PayPal Access Token.'];
        }

        $total = get_post_meta($reserva_id, '_tureserva_total', true);
        if ( empty($total) ) $total = 0;
        
        $currency = get_option('tureserva_moneda', 'USD');

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
                'return_url' => home_url('/reserva-confirmada/?reserva=' . $reserva_id . '&gateway=paypal'),
                'cancel_url' => home_url('/reserva-cancelada/?reserva=' . $reserva_id),
            ]
        ]);

        $response = wp_remote_post($this->get_api_url() . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $body
        ]);

        if ( is_wp_error( $response ) ) {
             return ['result' => 'fail', 'message' => $response->get_error_message()];
        }

        $data = json_decode(wp_remote_retrieve_body($response));

        if (!empty($data->links)) {
            foreach ($data->links as $link) {
                if ($link->rel === 'approve') {
                    // Update meta to track order ID
                    update_post_meta($reserva_id, '_tureserva_paypal_order_id', $data->id);
                    return [
                        'result' => 'success',
                        'redirect' => $link->href
                    ];
                }
            }
        }

        return ['result' => 'fail', 'message' => 'Could not retrieve approval link from PayPal.'];
    }

    private function get_access_token() {
        $client_id = $this->get_option('client_id');
        $secret = $this->get_option('secret');
        
        $response = wp_remote_post($this->get_api_url() . '/v1/oauth2/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret),
            ],
            'body' => 'grant_type=client_credentials'
        ]);

        if ( is_wp_error( $response ) ) return false;

        $data = json_decode(wp_remote_retrieve_body($response));
        return $data->access_token ?? null;
    }

    public function register_webhook() {
        register_rest_route('tureserva/v1', '/paypal/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function handle_webhook(WP_REST_Request $request) {
        // Basic webhook handling
        // Ideally verify signature
        $body = json_decode($request->get_body());
        
        if ( isset($body->event_type) && $body->event_type === 'CHECKOUT.ORDER.APPROVED' ) {
             // Find order
             $resource = $body->resource;
             $order_id = $resource->id;
             
             // Find reservation by order ID logic here
             // For now just logging
             error_log('PayPal Webhook: Order Approved ' . $order_id);
        }
        
        return new WP_REST_Response(['status' => 'ok']);
    }
}
