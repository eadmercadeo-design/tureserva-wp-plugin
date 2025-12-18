<?php
/**
 * PASARELAS DE PAGO — TuReserva
 * Configuración de métodos de pago y pasarelas.
 * Refactorizado para usar el Sistema Modular de Pagos.
 */

if (!defined('ABSPATH')) exit;

// 1. Obtener Gestor de Pagos
if ( ! function_exists('TR_Payments') ) {
    echo '<div class="notice notice-error"><p>Error: El gestor de pagos no está cargado.</p></div>';
    return;
}

$gateways = TR_Payments()->get_gateways();

// 2. Definir Pestaña Actual
// Si no hay subtab, por defecto mostrar la primera pasarela
$first_gateway_id = !empty($gateways) ? array_key_first($gateways) : 'general';
$current_subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : $first_gateway_id;

// URL base para los enlaces de las sub-pestañas
$base_url = admin_url('admin.php?page=tureserva-ajustes-generales&tab=pagos');

?>

<div class="tureserva-subtabs">
    <!-- NAVEGACIÓN LATERAL -->
    <div class="ts-subnav">
        <?php foreach ($gateways as $id => $gateway) : ?>
            <?php 
                $is_active = $current_subtab === $id;
                $enabled   = $gateway->is_available();
                $icon      = $enabled ? 'dashicons-yes' : 'dashicons-marker'; 
                $color     = $enabled ? '#46b450' : '#ccc';
            ?>
            <a href="<?php echo esc_url($base_url . '&subtab=' . $id); ?>" class="ts-subnav-link <?php echo $is_active ? 'active' : ''; ?>">
                <span style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <?php echo esc_html($gateway->title); ?>
                    <span class="dashicons <?php echo $icon; ?>" style="font-size:14px; color:<?php echo $color; ?>;"></span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- CONTENIDO -->
    <div class="ts-subtab-content">
        <?php 
        $active_gateway = TR_Payments()->get_gateway($current_subtab);
        
        if ( $active_gateway ) : 
        ?>
            <div class="tureserva-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                    <div>
                        <h3 style="margin:0;"><?php echo esc_html($active_gateway->title); ?></h3>
                        <p style="margin:5px 0 0 0; color:#666; font-size:13px;"><?php echo esc_html($active_gateway->description); ?></p>
                    </div>
                </div>

                <!-- 
                    NOTA: No necesitamos <form> aquí porque este partial está DENTRO 
                    del <form> principal en ajustes-generales.php 
                -->
                
                <table class="form-table">
                    <?php $active_gateway->generate_settings_html(); ?>
                </table>
            </div>

        <?php else : ?>
            <div class="tureserva-card">
                <p>Selecciona una pasarela de pago.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Estilos para sub-pestañas (mantenidos) */
.tureserva-subtabs { display: flex; gap: 20px; }
.ts-subnav { width: 220px; flex-shrink: 0; display: flex; flex-direction: column; gap: 5px; }
.ts-subnav-link { 
    padding: 12px 15px; 
    border-radius: 5px; 
    color: #444; 
    text-decoration: none; 
    font-weight: 500; 
    transition: all 0.2s; 
    border-left: 3px solid transparent;
    background: #fff;
    border: 1px solid #e5e5e5;
}
.ts-subnav-link:hover { background: #fafafa; color: #2271b1; }
.ts-subnav-link.active { 
    background: #fff; 
    color: #2271b1; 
    border-left-color: #2271b1; 
    border-color: #2271b1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
}
.ts-subtab-content { flex: 1; }
</style>
