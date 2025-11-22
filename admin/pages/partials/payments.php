<?php
/**
 * PASARELAS DE PAGO — TuReserva
 * Configuración de métodos de pago y pasarelas.
 */

if (!defined('ABSPATH')) exit;

// Obtener la sub-pestaña actual
$current_subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'general';

// URL base para los enlaces de las sub-pestañas
$base_url = admin_url('admin.php?page=tureserva-ajustes-generales&tab=pagos');

// Array de sub-pestañas
$subtabs = [
    'general' => 'Ajustes Generales',
    'test' => 'Probar Pago',
    'arrival' => 'Pagar a la llegada',
    'bank' => 'Transferencia Bancaria',
    'paypal' => 'PayPal',
    'stripe' => 'Stripe (Tarjetas)',
    'woocommerce' => 'WooCommerce',
    // '2checkout' => '2Checkout', // Se pueden agregar más según necesidad
];

?>

<div class="tureserva-subtabs">
    <div class="ts-subnav">
        <?php foreach ($subtabs as $key => $label) : ?>
            <a href="<?php echo esc_url($base_url . '&subtab=' . $key); ?>" class="ts-subnav-link <?php echo $current_subtab === $key ? 'active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="ts-subtab-content">
        
        <!-- 1. AJUSTES GENERALES -->
        <?php if ($current_subtab === 'general') : ?>
            <div class="tureserva-card">
                <h3>Configuración General de Pagos</h3>
                <div class="tureserva-grid-2">
                    <div class="ts-form-group">
                        <label>Moneda</label>
                        <select name="tureserva_currency">
                            <option value="USD" <?php selected(get_option('tureserva_currency'), 'USD'); ?>>Dólar Estadounidense (USD)</option>
                            <option value="EUR" <?php selected(get_option('tureserva_currency'), 'EUR'); ?>>Euro (EUR)</option>
                            <option value="COP" <?php selected(get_option('tureserva_currency'), 'COP'); ?>>Peso Colombiano (COP)</option>
                            <option value="MXN" <?php selected(get_option('tureserva_currency'), 'MXN'); ?>>Peso Mexicano (MXN)</option>
                        </select>
                    </div>
                    <div class="ts-form-group">
                        <label>Posición de la moneda</label>
                        <select name="tureserva_currency_pos">
                            <option value="left" <?php selected(get_option('tureserva_currency_pos'), 'left'); ?>>Izquierda ($100)</option>
                            <option value="right" <?php selected(get_option('tureserva_currency_pos'), 'right'); ?>>Derecha (100$)</option>
                        </select>
                    </div>
                </div>
                
                <div class="ts-form-group">
                    <label>Modo de Depósito</label>
                    <select name="tureserva_deposit_type">
                        <option value="full" <?php selected(get_option('tureserva_deposit_type'), 'full'); ?>>Pago Total (100%)</option>
                        <option value="percent" <?php selected(get_option('tureserva_deposit_type'), 'percent'); ?>>Porcentaje (%)</option>
                        <option value="fixed" <?php selected(get_option('tureserva_deposit_type'), 'fixed'); ?>>Monto Fijo</option>
                    </select>
                    <p class="ts-helper">Define si el cliente debe pagar el total o un adelanto para confirmar.</p>
                </div>

                <div class="ts-form-group">
                    <label>Tiempo de espera para pago pendiente (minutos)</label>
                    <input type="number" name="tureserva_pending_payment_time" value="<?php echo esc_attr(get_option('tureserva_pending_payment_time', 60)); ?>">
                    <p class="ts-helper">Tiempo que la reserva permanece bloqueada esperando el pago antes de liberarse.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. PROBAR PAGO (TEST) -->
        <?php if ($current_subtab === 'test') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-hammer" style="font-size:24px; color:#2271b1;"></span>
                        <h3>Probar Pago (Modo Simulación)</h3>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_test_enable" value="1" <?php checked(get_option('tureserva_payment_test_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>
                <div class="ts-form-group">
                    <label>Título</label>
                    <input type="text" name="tureserva_payment_test_title" value="<?php echo esc_attr(get_option('tureserva_payment_test_title', 'Probar Pago')); ?>">
                </div>
                <div class="ts-form-group">
                    <label>Descripción</label>
                    <textarea name="tureserva_payment_test_desc" rows="3"><?php echo esc_textarea(get_option('tureserva_payment_test_desc', 'Simula un pago exitoso para pruebas.')); ?></textarea>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. PAGAR A LA LLEGADA -->
        <?php if ($current_subtab === 'arrival') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-store" style="font-size:24px; color:#2271b1;"></span>
                        <h3>Pagar a la llegada</h3>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_arrival_enable" value="1" <?php checked(get_option('tureserva_payment_arrival_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>
                <div class="ts-form-group">
                    <label>Título</label>
                    <input type="text" name="tureserva_payment_arrival_title" value="<?php echo esc_attr(get_option('tureserva_payment_arrival_title', 'Pagar a la llegada')); ?>">
                </div>
                <div class="ts-form-group">
                    <label>Instrucciones</label>
                    <textarea name="tureserva_payment_arrival_desc" rows="3"><?php echo esc_textarea(get_option('tureserva_payment_arrival_desc', 'Paga en efectivo o tarjeta al momento de hacer check-in.')); ?></textarea>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. TRANSFERENCIA BANCARIA -->
        <?php if ($current_subtab === 'bank') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-bank" style="font-size:24px; color:#2271b1;"></span>
                        <h3>Transferencia Bancaria Directa</h3>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_bank_enable" value="1" <?php checked(get_option('tureserva_payment_bank_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>
                <div class="ts-form-group">
                    <label>Título</label>
                    <input type="text" name="tureserva_payment_bank_title" value="<?php echo esc_attr(get_option('tureserva_payment_bank_title', 'Transferencia Bancaria')); ?>">
                </div>
                <div class="ts-form-group">
                    <label>Descripción</label>
                    <textarea name="tureserva_payment_bank_desc" rows="3"><?php echo esc_textarea(get_option('tureserva_payment_bank_desc', 'Realiza el pago directamente en nuestra cuenta bancaria.')); ?></textarea>
                </div>
                <div class="ts-form-group">
                    <label>Instrucciones / Datos Bancarios</label>
                    <?php wp_editor(get_option('tureserva_payment_bank_instructions', ''), 'tureserva_payment_bank_instructions', ['textarea_rows' => 5, 'media_buttons' => false, 'teeny' => true]); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 5. PAYPAL -->
        <?php if ($current_subtab === 'paypal') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-cart" style="font-size:24px; color:#003087;"></span>
                        <h3>PayPal</h3>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_paypal_enable" value="1" <?php checked(get_option('tureserva_payment_paypal_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>
                
                <div class="ts-form-group">
                    <label>Modo de prueba (Sandbox)</label>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_paypal_sandbox" value="1" <?php checked(get_option('tureserva_payment_paypal_sandbox'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>

                <div class="ts-form-group">
                    <label>Email de PayPal</label>
                    <input type="email" name="tureserva_payment_paypal_email" value="<?php echo esc_attr(get_option('tureserva_payment_paypal_email', '')); ?>" placeholder="tu-email@paypal.com">
                </div>
                
                <div class="ts-form-group">
                    <label>Título</label>
                    <input type="text" name="tureserva_payment_paypal_title" value="<?php echo esc_attr(get_option('tureserva_payment_paypal_title', 'PayPal')); ?>">
                </div>
            </div>
        <?php endif; ?>

        <!-- 6. STRIPE (TARJETAS) -->
        <?php if ($current_subtab === 'stripe') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-credit-card" style="font-size:24px; color:#6772e5;"></span>
                        <div>
                            <h3>Stripe (Tarjetas de Crédito/Débito)</h3>
                            <p style="margin:0; font-size:12px; color:#666;">Acepta pagos con tarjeta desde cualquier país.</p>
                        </div>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_stripe_enable" value="1" <?php checked(get_option('tureserva_payment_stripe_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>

                <div class="ts-form-group">
                    <label>Modo de prueba</label>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_stripe_testmode" value="1" <?php checked(get_option('tureserva_payment_stripe_testmode'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>

                <div class="tureserva-grid-2">
                    <div class="ts-form-group">
                        <label>Clave Publicable (Publishable Key)</label>
                        <input type="text" name="tureserva_stripe_pk" value="<?php echo esc_attr(get_option('tureserva_stripe_pk', '')); ?>" placeholder="pk_test_...">
                    </div>
                    <div class="ts-form-group">
                        <label>Clave Secreta (Secret Key)</label>
                        <input type="password" name="tureserva_stripe_sk" value="<?php echo esc_attr(get_option('tureserva_stripe_sk', '')); ?>" placeholder="sk_test_...">
                    </div>
                </div>

                <div class="ts-form-group">
                    <label>Título</label>
                    <input type="text" name="tureserva_payment_stripe_title" value="<?php echo esc_attr(get_option('tureserva_payment_stripe_title', 'Tarjeta de Crédito/Débito')); ?>">
                </div>
                
                <div class="ts-form-group">
                    <label>Descripción</label>
                    <textarea name="tureserva_payment_stripe_desc" rows="2"><?php echo esc_textarea(get_option('tureserva_payment_stripe_desc', 'Paga de forma segura con tu tarjeta de crédito o débito.')); ?></textarea>
                </div>
            </div>
        <?php endif; ?>

        <!-- 7. WOOCOMMERCE -->
        <?php if ($current_subtab === 'woocommerce') : ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-cart" style="font-size:24px; color:#96588a;"></span>
                        <div>
                            <h3>Integración con WooCommerce</h3>
                            <p style="margin:0; font-size:12px; color:#666;">Utiliza las pasarelas de pago configuradas en WooCommerce para procesar reservas.</p>
                        </div>
                    </div>
                    <label class="ts-toggle">
                        <input type="checkbox" name="tureserva_payment_wc_enable" value="1" <?php checked(get_option('tureserva_payment_wc_enable'), 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>

                <?php if (class_exists('WooCommerce')) : ?>
                    <div class="notice notice-info inline">
                        <p>✅ WooCommerce está activo. Las reservas se añadirán al carrito de WooCommerce como productos.</p>
                    </div>
                    <div class="ts-form-group" style="margin-top:15px;">
                        <label>Producto de Reserva (ID)</label>
                        <input type="number" name="tureserva_wc_product_id" value="<?php echo esc_attr(get_option('tureserva_wc_product_id', '')); ?>" placeholder="ID del producto">
                        <p class="ts-helper">ID del producto de WooCommerce que se usará para procesar el pago.</p>
                    </div>
                <?php else : ?>
                    <div class="notice notice-warning inline">
                        <p>⚠️ WooCommerce no está instalado o activo. Esta función no estará disponible hasta que actives WooCommerce.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
/* Estilos para sub-pestañas */
.tureserva-subtabs { display: flex; gap: 20px; }
.ts-subnav { width: 200px; flex-shrink: 0; display: flex; flex-direction: column; gap: 5px; }
.ts-subnav-link { 
    padding: 10px 15px; 
    border-radius: 5px; 
    color: #444; 
    text-decoration: none; 
    font-weight: 500; 
    transition: all 0.2s; 
    border-left: 3px solid transparent;
}
.ts-subnav-link:hover { background: #f0f0f1; color: #2271b1; }
.ts-subnav-link.active { background: #fff; color: #2271b1; border-left-color: #2271b1; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.ts-subtab-content { flex: 1; }
</style>
