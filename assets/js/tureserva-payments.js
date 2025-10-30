/**
 * ==========================================================
 * TuReserva — Pagos con Tarjeta (Stripe Frontend)
 * ==========================================================
 */

jQuery(document).ready(function ($) {
    if (typeof Stripe === 'undefined' || !tureservaPagoData) return;

    const stripe = Stripe(tureservaPagoData.stripe_key);
    const elements = stripe.elements();

    const style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: { color: '#fa755a', iconColor: '#fa755a' }
    };

    const card = elements.create('card', { style });
    card.mount('#tureserva-card-element');

    const form = $('#tureserva-form-pago');
    const btn = $('#tureserva-btn-pagar');
    const mensaje = $('#tureserva-pago-mensaje');

    form.on('submit', function (e) {
        e.preventDefault();

        btn.prop('disabled', true).text('Procesando...');
        mensaje.removeClass('error success').text('');

        stripe.createToken(card).then(function (result) {
            if (result.error) {
                mensaje.addClass('error').text(result.error.message);
                btn.prop('disabled', false).text('Pagar ahora');
            } else {
                procesarPago(result.token.id);
            }
        });
    });

    function procesarPago(token) {
        $.post(tureservaPagoData.ajax_url, {
            action: 'tureserva_pago_stripe',
            reserva_id: tureservaPagoData.reserva_id,
            monto: tureservaPagoData.monto,
            token: token
        }).done(function (resp) {
            if (resp.success) {
                mensaje.addClass('success').text(resp.data.mensaje);
                btn.text('✅ Pago completado');
                card.clear();
            } else {
                mensaje.addClass('error').text(resp.data?.mensaje || 'Error al procesar el pago.');
                btn.prop('disabled', false).text('Pagar ahora');
            }
        }).fail(function () {
            mensaje.addClass('error').text('Error de conexión con el servidor.');
            btn.prop('disabled', false).text('Pagar ahora');
        });
    }
});
