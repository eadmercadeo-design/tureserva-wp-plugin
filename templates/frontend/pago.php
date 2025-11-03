<?php
/**
 * ==========================================================
 * TEMPLATE: Página de Pago — TuReserva
 * ==========================================================
 * Muestra los métodos de pago activos según configuración.
 * Ejecuta la acción correspondiente (Stripe, PayPal, etc.)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

$reserva_id = intval($_GET['reserva'] ?? 0);
if (!$reserva_id) {
    echo '<p style="color:red;">⚠️ No se encontró la reserva.</p>';
    return;
}

$total = get_post_meta($reserva_id, '_tureserva_total', true);
$moneda = get_option('tureserva_moneda', 'USD');

echo '<div class="tureserva-pago-wrapper" style="max-width:700px;margin:40px auto;padding:30px;background:#fff;border-radius:10px;box-shadow:0 1px 5px rgba(0,0,0,0.1);">';
echo '<h2 style="margin-bottom:10px;">💳 ' . __('Pagar tu reserva', 'tureserva') . '</h2>';
echo '<p>' . __('Total a pagar:', 'tureserva') . ' <strong>' . esc_html($moneda . ' ' . number_format($total, 2)) . '</strong></p>';

// Botones activos
$metodos = [];

// STRIPE
if (get_option('tureserva_stripe_activo')) {
    $metodos[] = [
        'id' => 'stripe',
        'label' => __('Pagar con tarjeta (Stripe)', 'tureserva'),
        'desc' => __('Pago seguro con tarjeta de crédito o débito.', 'tureserva'),
        'icon' => 'https://upload.wikimedia.org/wikipedia/commons/b/b7/Stripe_Logo%2C_revised_2016.svg',
    ];
}

// PAYPAL
if (get_option('tureserva_paypal_activo')) {
    $metodos[] = [
        'id' => 'paypal',
        'label' => __('PayPal', 'tureserva'),
        'desc' => __('Usa tu cuenta PayPal o tarjeta asociada.', 'tureserva'),
        'icon' => 'https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg',
    ];
}

// TRANSFERENCIA
if (get_option('tureserva_transferencia_activo')) {
    $metodos[] = [
        'id' => 'transferencia',
        'label' => __('Transferencia bancaria', 'tureserva'),
        'desc' => __('Realiza una transferencia a la cuenta indicada.', 'tureserva'),
        'icon' => 'https://cdn-icons-png.flaticon.com/512/833/833262.png',
    ];
}

// MANUAL / EFECTIVO
if (get_option('tureserva_manual_activo')) {
    $metodos[] = [
        'id' => 'manual',
        'label' => __('Pago en efectivo o manual', 'tureserva'),
        'desc' => __('Confirma tu reserva y paga directamente al llegar.', 'tureserva'),
        'icon' => 'https://cdn-icons-png.flaticon.com/512/3176/3176366.png',
    ];
}
?>

<div class="tureserva-metodos">
    <?php foreach ($metodos as $metodo): ?>
        <div class="tureserva-metodo" style="border:1px solid #ddd;padding:15px;border-radius:8px;margin:15px 0;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:#fafafa;" data-metodo="<?php echo esc_attr($metodo['id']); ?>">
            <div style="display:flex;align-items:center;gap:15px;">
                <img src="<?php echo esc_url($metodo['icon']); ?>" style="width:50px;height:auto;">
                <div>
                    <strong><?php echo esc_html($metodo['label']); ?></strong><br>
                    <small><?php echo esc_html($metodo['desc']); ?></small>
                </div>
            </div>
            <button class="button button-primary" style="padding:8px 16px;"><?php _e('Seleccionar', 'tureserva'); ?></button>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.tureserva-metodo').forEach(btn => {
    btn.addEventListener('click', () => {
        const metodo = btn.getAttribute('data-metodo');
        const reserva = '<?php echo esc_js($reserva_id); ?>';
        btn.style.opacity = '0.6';
        btn.querySelector('button').textContent = 'Procesando...';

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'tureserva_procesar_pago',
                metodo,
                reserva
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect) window.location.href = data.redirect;
            else alert(data.message || 'Error al procesar el pago.');
        })
        .catch(() => alert('Error de conexión.'));
    });
});
</script>

<?php
echo '</div>';
