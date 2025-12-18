<?php
/**
 * Admin View: Payments Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
$gateways   = TR_Payments()->get_gateways();

?>
<div class="wrap tureserva-settings">
    <h1 class="wp-heading-inline">💳 Configuración de Pagos</h1>
    <p class="description">Administra los métodos de pago, monedas y reglas financieras.</p>

    <nav class="nav-tab-wrapper">
        <a href="?page=tureserva_payments&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
        <a href="?page=tureserva_payments&tab=gateways" class="nav-tab <?php echo $active_tab == 'gateways' ? 'nav-tab-active' : ''; ?>">Pasarelas de Pago</a>
    </nav>

    <form method="post" action="options.php" class="tureserva-settings-form">
        
        <?php if ( $active_tab === 'general' ) : ?>
            <?php settings_fields( 'tureserva_payments_general_group' ); ?>
            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
                <h2>Configuración General</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Moneda</th>
                        <td>
                            <select name="tureserva_moneda">
                                <option value="USD" <?php selected( get_option('tureserva_moneda'), 'USD' ); ?>>USD ($)</option>
                                <option value="EUR" <?php selected( get_option('tureserva_moneda'), 'EUR' ); ?>>EUR (€)</option>
                                <option value="GBP" <?php selected( get_option('tureserva_moneda'), 'GBP' ); ?>>GBP (£)</option>
                                <option value="MXN" <?php selected( get_option('tureserva_moneda'), 'MXN' ); ?>>MXN ($)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Símbolo de Moneda</th>
                        <td>
                            <input type="text" name="tureserva_simbolo_moneda" value="<?php echo esc_attr( get_option('tureserva_simbolo_moneda', '$') ); ?>" class="small-text">
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ( $active_tab === 'gateways' ) : ?>
            
            <?php
            // If checking a specific gateway logic could go here
            // But we will list all gateways
            ?>
            <div style="margin-top: 20px;">
                <?php foreach ( $gateways as $gateway ) : ?>
                    <div class="card" style="margin-bottom: 20px; padding: 0;">
                        <?php 
                            $is_enabled = $gateway->enabled === 'yes';
                            $status_color = $is_enabled ? '#d4edda' : '#f8d7da';
                            $status_text_color = $is_enabled ? '#155724' : '#721c24';
                        ?>
                        <div style="padding: 15px; border-bottom: 1px solid #ccd0d4; display: flex; justify-content: space-between; align-items: center; background-color: <?php echo $is_enabled ? '#f0f0f1' : '#fff'; ?>">
                            <h3 style="margin: 0;"><?php echo esc_html( $gateway->title ); ?></h3>
                            <span class="badge" style="background: <?php echo $status_color; ?>; color: <?php echo $status_text_color; ?>; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                <?php echo $is_enabled ? 'ACTIVO' : 'INACTIVO'; ?>
                            </span>
                        </div>
                        <div style="padding: 20px;">
                            <?php 
                                // We need to register a settings group for EACH gateway dynamically or use a single group array?
                                // Standard WP Settings API is tricky with dynamic fields.
                                // We will manually handle saving in options.php via a single group 'tureserva_payments_gateways_group' 
                                // but we need to ensure the names match what register_setting expects.
                                // Actually, easiest way for granular control:
                                // Register one setting "tureserva_payment_settings_{id}" which is an array.
                                settings_fields( 'tureserva_payment_gateways_group' ); 
                            ?>
                            <p><?php echo esc_html( $gateway->description ); ?></p>
                            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                            <table class="form-table">
                                <?php $gateway->generate_settings_html(); ?>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <?php submit_button( '💾 Guardar Cambios' ); ?>
    </form>
</div>
