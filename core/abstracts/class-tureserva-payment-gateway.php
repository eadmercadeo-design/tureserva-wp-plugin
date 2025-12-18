<?php
/**
 * Abstract Payment Gateway Class
 * Base class for all payment gateways in TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class TuReserva_Payment_Gateway {

    /**
     * Gateway ID (e.g., 'stripe', 'paypal')
     * @var string
     */
    public $id;

    /**
     * Gateway Title (e.g., 'Stripe', 'PayPal')
     * @var string
     */
    public $title;

    /**
     * Gateway Description
     * @var string
     */
    public $description;

    /**
     * Enabled status ('yes' or 'no')
     * @var string
     */
    public $enabled;

    /**
     * Gateway Settings
     * @var array
     */
    public $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_settings();
    }

    /**
     * Initialize settings
     */
    public function init_settings() {
        // Load settings from DB
        $all_settings = get_option( 'tureserva_payment_settings_' . $this->id, [] );
        $this->settings = $all_settings;
        $this->enabled  = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
    }

    /**
     * Get setting value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_option( $key, $default = '' ) {
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
    }

    /**
     * Check if gateway is available
     * @return bool
     */
    public function is_available() {
        return $this->enabled === 'yes';
    }

    /**
     * Get admin settings fields
     * @return array
     */
    abstract public function init_form_fields();

    /**
     * Process Payment
     * @param int $order_id
     * @return array|bool
     */
    abstract public function process_payment( $order_id );

    /**
     * Render Admin Options
     * Typically using the Settings API or custom HTML
     */
    public function admin_options() {
        echo '<h3>' . esc_html( $this->title ) . '</h3>';
        echo '<p>' . esc_html( $this->description ) . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
    }

    /**
     * Generate Settings HTML
     */
    public function generate_settings_html() {
        $fields = $this->init_form_fields();

        foreach ( $fields as $key => $field ) {
            $value = $this->get_option( $key, isset( $field['default'] ) ? $field['default'] : '' );
            // Use 'tureserva_payment_settings_' prefix to match what setup-pages.php or ajustes-generales.php might expect/save
            $field_name = "tureserva_payment_settings_{$this->id}[{$key}]";
            
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="' . esc_attr( $field_name ) . '">' . esc_html( $field['title'] ) . '</label>';
            echo '</th>';
            echo '<td class="forminp">';
            
            switch ( $field['type'] ) {
                case 'text':
                case 'password':
                    echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr( isset($field['placeholder']) ? $field['placeholder'] : '' ) . '">';
                    break;
                case 'textarea':
                    echo '<textarea name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '" rows="5" cols="50" class="large-text">' . esc_textarea( $value ) . '</textarea>';
                    break;
                case 'select':
                    echo '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '">';
                    foreach ( $field['options'] as $option_key => $option_value ) {
                        echo '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_value ) . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'checkbox':
                    echo '<label><input type="checkbox" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '" value="yes" ' . checked( $value, 'yes', false ) . '> ' . esc_html( isset($field['label']) ? $field['label'] : '' ) . '</label>';
                    break;
            }

            if ( ! empty( $field['description'] ) ) {
                echo '<p class="description">' . wp_kses_post( $field['description'] ) . '</p>';
            }

            echo '</td>';
            echo '</tr>';
        }
    }
}
