<?php
/**
 * ==========================================================
 * SHORTCODE: [tureserva_pago]
 * ==========================================================
 * Muestra un formulario de pago con tarjeta de crédito
 * conectado a Stripe (modo test o producción).
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'tureserva_pago', 'tureserva_shortcode_pago' );
function tureserva_shortcode_pago( $atts ) {
    $atts = shortcode_atts( array(
        'reserva_id' => 0,
        'monto'      => 0,
        'moneda'     => 'usd',
    ), $atts, 'tureserva_pago' );

    $reserva_id = intval( $atts['reserva_id'] );
    $monto      = floatval( $atts['monto'] );
    $moneda     = esc_attr( strtoupper( $atts['moneda'] ) );

    $public_key = get_option( 'tureserva_stripe_public_key' );
    $modo       = get_option( 'tureserva_stripe_mode', 'test' );

    if ( empty( $public_key ) ) {
        return '<p style="color:red;">❌ Stripe no está configurado correctamente.</p>';
    }

    ob_start();
    ?>
    <div id="tureserva-pago-container" class="tureserva-pago-wrapper">
        <h2>💳 Pago de Reserva #<?php echo esc_html( $reserva_id ); ?></h2>
        <p>Monto a pagar: <strong><?php echo esc_html( $moneda . ' ' . number_format( $monto, 2 ) ); ?></strong></p>

        <form id="tureserva-form-pago">
            <div id="tureserva-card-element"><!-- Stripe card field --></div>
            <button type="submit" id="tureserva-btn-pagar" class="button button-primary">
                Pagar ahora
            </button>
            <div id="tureserva-pago-mensaje"></div>
        </form>

        <small style="display:block;margin-top:10px;">
            🔒 Pago seguro con Stripe (modo: <strong><?php echo esc_html( strtoupper($modo) ); ?></strong>)
        </small>
    </div>

    <script>
        const tureservaPagoData = {
            ajax_url: '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>',
            stripe_key: '<?php echo esc_js( $public_key ); ?>',
            reserva_id: '<?php echo esc_js( $reserva_id ); ?>',
            monto: '<?php echo esc_js( $monto ); ?>'
        };
    </script>
    <?php
    return ob_get_clean();
}

// =======================================================
// 📦 ENCOLAR SCRIPTS STRIPE Y JS PERSONALIZADO
// =======================================================
add_action( 'wp_enqueue_scripts', 'tureserva_enqueue_pago_scripts' );
function tureserva_enqueue_pago_scripts() {
    if ( ! is_singular() && ! has_shortcode( get_post()->post_content ?? '', 'tureserva_pago' ) ) return;

    wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, true );
    wp_enqueue_script(
        'tureserva-payments',
        TURESERVA_URL . 'assets/js/tureserva-payments.js',
        array( 'jquery', 'stripe-js' ),
        TURESERVA_VERSION,
        true
    );

    wp_enqueue_style(
        'tureserva-payments-css',
        TURESERVA_URL . 'assets/css/tureserva-payments.css',
        array(),
        TURESERVA_VERSION
    );
}
