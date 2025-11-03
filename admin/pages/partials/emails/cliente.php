<?php
/**
 * ==========================================================
 * EMAILS DEL CLIENTE — TuReserva
 * ==========================================================
 * Configura los correos automáticos que recibe el cliente:
 * - Confirmación de reserva
 * - Aprobación
 * - Cancelación
 * - Registro de cuenta
 * - Recordatorio antes del check-in (pre-llegada)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 💾 Guardar ajustes
// =======================================================
if (isset($_POST['tureserva_save_cliente_emails']) && check_admin_referer('tureserva_cliente_emails_nonce')) {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tureserva_email_cliente_') === 0) {
            update_option($key, wp_kses_post($value));
        }
    }
    echo '<div class="updated"><p>✅ ' . __('Configuración de emails del cliente guardada correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🧩 Helper de campos
// =======================================================
function tureserva_email_cliente_field($id, $label, $type = 'text', $rows = 4) {
    $value = get_option($id, '');
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    if ($type === 'textarea') {
        wp_editor($value, $id, array('textarea_name' => $id, 'textarea_rows' => $rows));
    } else {
        echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($id) . '" value="' . esc_attr($value) . '" class="regular-text">';
    }
    echo '</td>';
    echo '</tr>';
}

?>

<div class="tureserva-email-config">
    <h2><?php _e('Emails del cliente', 'tureserva'); ?></h2>
    <p><?php _e('Configure los correos automáticos que se envían a los clientes en diferentes etapas del proceso de reserva.', 'tureserva'); ?></p>

    <form method="post">
        <?php wp_nonce_field('tureserva_cliente_emails_nonce'); ?>

        <!-- =====================================================
        EMAIL 1: NUEVA RESERVA (confirmación por administrador)
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>📩 <?php _e('Email de nueva reserva (confirmación por administrador)', 'tureserva'); ?></h3>
            <p><?php _e('Este correo se envía cuando el modo de confirmación está configurado como “Confirmación por administrador”.', 'tureserva'); ?></p>
            <table class="form-table">
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_pending_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_pending_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_pending_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                ?>
            </table>
        </div>

        <!-- =====================================================
        EMAIL 2: NUEVA RESERVA (confirmación por usuario)
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>📧 <?php _e('Email de nueva reserva (confirmación por usuario)', 'tureserva'); ?></h3>
            <p><?php _e('Se envía cuando el modo de confirmación está configurado como “Confirmación por cliente mediante enlace de correo electrónico”.', 'tureserva'); ?></p>
            <table class="form-table">
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_confirm_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_confirm_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_confirm_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                ?>
            </table>
        </div>

        <!-- =====================================================
        EMAIL 3: RESERVA APROBADA
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>✅ <?php _e('Email de reserva aprobada', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_approved_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_approved_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_approved_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                ?>
            </table>
        </div>

        <!-- =====================================================
        EMAIL 4: RESERVA CANCELADA
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>❌ <?php _e('Email de reserva cancelada', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_cancel_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_cancel_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_cancel_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                ?>
            </table>
        </div>

        <!-- =====================================================
        EMAIL 5: REGISTRO DE CLIENTE
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>👤 <?php _e('Correo electrónico de registro del cliente', 'tureserva'); ?></h3>
            <p><?php _e('Este correo se envía automáticamente cuando un cliente crea una cuenta durante el proceso de reserva.', 'tureserva'); ?></p>
            <table class="form-table">
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_register_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_register_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_register_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                ?>
            </table>
        </div>

        <!-- =====================================================
        EMAIL 6: PRE-LLEGADA DEL CLIENTE (nuevo)
        ====================================================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>🕓 <?php _e('Email de pre-llegada del cliente', 'tureserva'); ?></h3>
            <p><?php _e('Este correo se enviará automáticamente antes de la hora de check-in para informar al huésped cómo acceder al alojamiento (códigos, Wi-Fi, mapa, etc.).', 'tureserva'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_email_cliente_precheck_hours"><?php _e('Horas antes del check-in', 'tureserva'); ?></label></th>
                    <td>
                        <input type="number" name="tureserva_email_cliente_precheck_hours" value="<?php echo esc_attr(get_option('tureserva_email_cliente_precheck_hours', 24)); ?>" min="1" max="72" step="1"> 
                        <span><?php _e('horas antes de la llegada', 'tureserva'); ?></span>
                    </td>
                </tr>
                <?php
                tureserva_email_cliente_field('tureserva_email_cliente_precheck_subject', __('Asunto', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_precheck_header', __('Cabecera', 'tureserva'));
                tureserva_email_cliente_field('tureserva_email_cliente_precheck_body', __('Plantilla de email', 'tureserva'), 'textarea', 12);
                ?>
            </table>
            <p class="description"><?php _e('⚙️ Este correo se programará automáticamente según la hora de check-in definida en los ajustes generales.', 'tureserva'); ?></p>
        </div>

        <?php submit_button(__('Guardar cambios', 'tureserva'), 'primary', 'tureserva_save_cliente_emails'); ?>
    </form>
</div>
