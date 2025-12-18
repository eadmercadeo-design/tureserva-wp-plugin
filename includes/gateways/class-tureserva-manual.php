<?php
/**
 * Manual / Offline Payment Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TuReserva_Gateway_Manual extends TuReserva_Payment_Gateway {

    public function __construct() {
        $this->id = 'manual';
        $this->title = 'Pago Manual / Transferencia';
        $this->description = 'Permite a los clientes pagar a la llegada o mediante transferencia bancaria.';
        
        parent::__construct();
    }

    public function init_form_fields() {
        return [
            'enabled' => [
                'title'   => 'Habilitar/Deshabilitar',
                'type'    => 'checkbox',
                'label'   => 'Habilitar pago manual',
                'default' => 'yes'
            ],
            'title' => [
                'title'       => 'Título',
                'type'        => 'text',
                'description' => 'Esto es lo que verá el usuario durante el checkout.',
                'default'     => 'Pago a la llegada / Transferencia'
            ],
            'instructions' => [
                'title'       => 'Instrucciones',
                'type'        => 'textarea',
                'description' => 'Instrucciones que se mostrarán en la página de agradecimiento y en los correos.',
                'default'     => 'Por favor realiza el pago mediante transferencia bancaria a la cuenta X, o paga en efectivo al llegar.'
            ]
        ];
    }

    public function process_payment( $reserva_id ) {
        // Mark as on-hold or pending payment
        update_post_meta( $reserva_id, '_tureserva_estado_pago', 'pendiente' );
        update_post_meta( $reserva_id, '_tureserva_pago_metodo', 'manual' );
        
        // Return success and redirect to thank you page
        return [
            'result'   => 'success',
            'redirect' => home_url( '/reserva-completada/?reserva=' . $reserva_id )
        ];
    }
}
