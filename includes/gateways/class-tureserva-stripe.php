<?php
/**
 * Stripe Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TuReserva_Gateway_Stripe extends TuReserva_Payment_Gateway {

    public function __construct() {
        $this->id = 'stripe';
        $this->title = 'Stripe';
        $this->description = 'Acepta pagos con tarjeta de crédito/débito vía Stripe.';
        
        parent::__construct();

        // Hooks specific to Stripe
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function init_form_fields() {
        return [
            'enabled' => [
                'title'   => 'Habilitar/Deshabilitar',
                'type'    => 'checkbox',
                'label'   => 'Habilitar Stripe',
                'default' => 'no'
            ],
            'mode' => [
                'title'       => 'Modo de Operación',
                'type'        => 'select',
                'description' => 'Elige entre Modo Prueba (Test) o Producción (Live).',
                'default'     => 'test',
                'options'     => [
                    'test' => 'Test (Pruebas)',
                    'live' => 'Live (Producción)'
                ]
            ],
            'public_key' => [
                'title'       => 'Clave Pública',
                'type'        => 'text',
                'description' => 'Tu Publishable Key de Stripe.',
                'default'     => ''
            ],
            'secret_key' => [
                'title'       => 'Clave Secreta',
                'type'        => 'password',
                'description' => 'Tu Secret Key de Stripe.',
                'default'     => ''
            ],
            'webhook_secret' => [
                'title'       => 'Webhook Secret',
                'type'        => 'password',
                'description' => 'El secreto del webhook para validar eventos.',
                'default'     => ''
            ]
        ];
    }

    public function process_payment( $reserva_id ) {
        // Logic to create Stripe Session
        // This is called when user clicks "Pay"
        
        $secret_key = $this->get_option( 'secret_key' );
        if ( ! $secret_key ) {
            return false;
        }

        // Check if Stripe lib exists
        if ( file_exists( TURESERVA_PATH . 'vendor/autoload.php' ) ) {
            require_once TURESERVA_PATH . 'vendor/autoload.php';
        } else {
             error_log( 'Stripe library missing.' );
             return false;
        }

        try {
            \Stripe\Stripe::setApiKey( $secret_key );

            $total = get_post_meta( $reserva_id, '_tureserva_total', true );
            // Fallback for demo/testing if total is 0 or missing
            if ( empty($total) ) {
                 $total = 100; // 100 base units
            }
            
            $titulo = get_the_title( $reserva_id );
            $currency = get_option( 'tureserva_moneda', 'usd' ); // Global setting

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower( $currency ),
                        'product_data' => ['name' => $titulo . ' (Reserva #' . $reserva_id . ')'],
                        'unit_amount' => intval( $total * 100 ), // cents
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => home_url( '/reserva-confirmada/?reserva=' . $reserva_id . '&gateway=stripe&session_id={CHECKOUT_SESSION_ID}' ),
                'cancel_url'  => home_url( '/reserva-cancelada/?reserva=' . $reserva_id ),
                'metadata' => [
                    'reserva_id' => $reserva_id,
                ],
            ]);

            return [
                'result'   => 'success',
                'redirect' => $session->url
            ];

        } catch ( \Exception $e ) {
            error_log( 'Stripe Error: ' . $e->getMessage() );
            return [
                'result'  => 'fail',
                'message' => $e->getMessage()
            ];
        }
    }

    public function register_routes() {
        register_rest_route( 'tureserva/v1', '/stripe/webhook', [
            'methods' => 'POST',
            'callback' => [ $this, 'webhook_handler' ],
            'permission_callback' => '__return_true',
        ]);
    }

    public function webhook_handler( WP_REST_Request $request ) {
        $payload = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = $this->get_option( 'webhook_secret' );

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch ( \Exception $e ) {
            return new WP_REST_Response( ['error' => $e->getMessage()], 400 );
        }

        if ( $event->type === 'checkout.session.completed' ) {
            $session = $event->data->object;
            $reserva_id = intval( $session->metadata->reserva_id ?? 0 );

            if ( $reserva_id ) {
                $this->complete_payment( $reserva_id, $session->id );
            }
        }

        return new WP_REST_Response( ['status' => 'ok'], 200 );
    }

    private function complete_payment( $reserva_id, $transaction_id ) {
        update_post_meta( $reserva_id, '_tureserva_estado_pago', 'pagado' );
        update_post_meta( $reserva_id, '_tureserva_pago_metodo', 'stripe' );
        update_post_meta( $reserva_id, '_tureserva_pago_id', $transaction_id );
        
        // Update status if needed
        wp_update_post([
            'ID' => $reserva_id,
            'post_status' => 'publish' // Assuming publish is confirmed
        ]);
        
        do_action( 'tureserva_payment_complete', $reserva_id, 'stripe', $transaction_id );
    }
}
