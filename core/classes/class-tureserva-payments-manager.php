<?php
/**
 * Payments Manager Class
 * Handles registration and retrieval of payment gateways.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TuReserva_Payments_Manager {

    /**
     * The single instance of the class.
     * @var TuReserva_Payments_Manager
     */
    protected static $_instance = null;

    /**
     * Registered Gateways
     * @var array
     */
    public $gateways = [];

    /**
     * Main Instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize
     */
    /**
     * Initialize
     */
    public function init() {
        // Load gateways
        // If we are already past plugins_loaded (which we are, since tureserva_init runs on plugins_loaded), 
        // we should load immediately.
        if ( did_action( 'plugins_loaded' ) ) {
            $this->load_gateways();
        } else {
            add_action( 'plugins_loaded', [ $this, 'load_gateways' ], 20 );
        }
    }

    /**
     * Load Gateways
     */
    public function load_gateways() {
        // Include Abstract
        require_once plugin_dir_path( __DIR__ ) . 'abstracts/class-tureserva-payment-gateway.php';

        // Include Gateways
        // Note: These paths might need adjustment based on final file structure
        $gateway_files = [
            'includes/gateways/class-tureserva-stripe.php',
            'includes/gateways/class-tureserva-paypal.php',
            'includes/gateways/class-tureserva-manual.php'
        ];

        foreach ( $gateway_files as $file ) {
            // Use TURESERVA_PATH constant defined in main plugin file
            $path = TURESERVA_PATH . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            }
        }

        // Register default gateways
        // We will make this filterable so add-ons can register their own
        $load_gateways = apply_filters( 'tureserva_payment_gateways', [] );
        
        // Ensure core gateways are always attempted to load if classes exist
        if ( class_exists( 'TuReserva_Gateway_Stripe' ) ) {
            $load_gateways[] = 'TuReserva_Gateway_Stripe';
        }
        if ( class_exists( 'TuReserva_Gateway_PayPal' ) ) {
            $load_gateways[] = 'TuReserva_Gateway_PayPal';
        }
        if ( class_exists( 'TuReserva_Gateway_Manual' ) ) {
            $load_gateways[] = 'TuReserva_Gateway_Manual';
        }

        $load_gateways = array_unique( $load_gateways );

        foreach ( $load_gateways as $gateway_class ) {
            if ( class_exists( $gateway_class ) ) {
                $gateway = new $gateway_class();
                $this->gateways[ $gateway->id ] = $gateway;
            }
        }
    }

    /**
     * Get all registered gateways
     * @return array
     */
    public function get_gateways() {
        return $this->gateways;
    }

    /**
     * Get available gateways (enabled)
     * @return array
     */
    public function get_available_gateways() {
        $available = [];
        foreach ( $this->gateways as $id => $gateway ) {
            if ( $gateway->is_available() ) {
                $available[ $id ] = $gateway;
            }
        }
        return $available;
    }

    /**
     * Get Gateway by ID
     * @param string $id
     * @return TuReserva_Payment_Gateway|false
     */
    public function get_gateway( $id ) {
        return isset( $this->gateways[ $id ] ) ? $this->gateways[ $id ] : false;
    }
}

/**
 * Global function to access the manager
 */
function TR_Payments() {
    return TuReserva_Payments_Manager::instance();
}
