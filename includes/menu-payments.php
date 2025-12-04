<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Configuración de Pagos (Stripe)
 * ==========================================================
 * Permite ingresar las API Keys de Stripe y definir
 * el modo de operación (Prueba o Producción).
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📁 REGISTRO DEL SUBMENÚ
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_payments' );
function tureserva_menu_payments() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva', // dentro del menú "Reservas"
        'Configuración de Pagos',
        'Configuración de Pagos',
        'manage_options',
        'tureserva_payments',
        'tureserva_vista_payments'
    );
}

// =======================================================
// 💳 INTERFAZ DE CONFIGURACIÓN
// =======================================================
function tureserva_vista_payments() {
    $public = get_option( 'tureserva_stripe_public_key', '' );
    $secret = get_option( 'tureserva_stripe_secret_key', '' );
    $mode   = get_option( 'tureserva_stripe_mode', 'test' );
    ?>
    <div class="wrap">
        <h1>💳 Configuración de Pagos — Stripe</h1>
        <p>Conecta TuReserva con <strong>Stripe</strong> para aceptar pagos con tarjeta de crédito y débito.</p>

        <form method="post" action="options.php">
            <?php settings_fields( 'tureserva_payments_group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Modo de operación</th>
                    <td>
                        <select name="tureserva_stripe_mode">
                            <option value="test" <?php selected( $mode, 'test' ); ?>>🧪 Modo Prueba</option>
                            <option value="live" <?php selected( $mode, 'live' ); ?>>🚀 Modo Producción</option>
                        </select>
                        <p class="description">Usa modo prueba mientras desarrollas o realizas pruebas internas.</p>
                    </td>
                </tr>
                <tr>
                    <th>Clave pública (Publishable Key)</th>
                    <td>
                        <input type="text" name="tureserva_stripe_public_key" value="<?php echo esc_attr( $public ); ?>" class="regular-text">
                        <p class="description">Ejemplo: <code>pk_test_51MX...</code></p>
                    </td>
                </tr>
                <tr>
                    <th>Clave secreta (Secret Key)</th>
                    <td>
                        <input type="password" name="tureserva_stripe_secret_key" value="<?php echo esc_attr( $secret ); ?>" class="regular-text">
                        <p class="description">Ejemplo: <code>sk_test_51MX...</code></p>
                    </td>
                </tr>
            </table>
            <?php submit_button( '💾 Guardar configuración' ); ?>
        </form>

        <?php if ( $mode === 'test' ) : ?>
            <div style="background:#fff3cd;border-left:5px solid #ffecb5;padding:12px;margin-top:20px;">
                ⚠️ Estás en <strong>modo prueba</strong>. Usa las tarjetas de desarrollo de Stripe, por ejemplo:
                <code>4242 4242 4242 4242</code>, fecha futura y CVC <code>123</code>.
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// =======================================================
// ⚙️ REGISTRO DE OPCIONES
// =======================================================
add_action( 'admin_init', 'tureserva_register_payment_settings' );
function tureserva_register_payment_settings() {
    register_setting( 'tureserva_payments_group', 'tureserva_stripe_public_key' );
    register_setting( 'tureserva_payments_group', 'tureserva_stripe_secret_key' );
    register_setting( 'tureserva_payments_group', 'tureserva_stripe_mode' );
}
