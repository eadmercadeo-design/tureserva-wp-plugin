<?php
/**
 * ==========================================================
 * EMAILS DEL ADMINISTRADOR — TuReserva
 * ==========================================================
 * Configura los correos automáticos enviados al administrador
 * según el estado de las reservas (pendiente, confirmada, cancelada).
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧾 Guardar ajustes
// =======================================================
if (isset($_POST['tureserva_save_admin_emails']) && check_admin_referer('tureserva_admin_emails_nonce')) {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tureserva_email_admin_') === 0) {
            update_option($key, wp_kses_post($value));
        }
    }
    echo '<div class="updated"><p>✅ ' . __('Configuración de emails del administrador guardada correctamente.', 'tureserva') . '</p></div>';
}

// =======================================================
// 🔧 Función auxiliar
// =======================================================
function tureserva_email_admin_field($id, $label, $type = 'text', $rows = 4) {
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
    <h2><?php _e('Emails del administrador', 'tureserva'); ?></h2>
    <p><?php _e('Configure los correos que recibirá el administrador cuando se generen o modifiquen reservas.', 'tureserva'); ?></p>

    <form method="post">
        <?php wp_nonce_field('tureserva_admin_emails_nonce'); ?>

        <!-- =======================
        EMAIL: RESERVA PENDIENTE
        ======================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>📩 <?php _e('Email de reserva pendiente', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_admin_field('tureserva_email_admin_pending_subject', __('Asunto', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_pending_header', __('Cabecera', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_pending_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                tureserva_email_admin_field('tureserva_email_admin_pending_recipients', __('Destinatarios', 'tureserva'));
                ?>
            </table>
        </div>

        <!-- =======================
        EMAIL: RESERVA APROBADA
        ======================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>✅ <?php _e('Email de reserva aprobada', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_admin_field('tureserva_email_admin_approved_subject', __('Asunto', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_approved_header', __('Cabecera', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_approved_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                tureserva_email_admin_field('tureserva_email_admin_approved_recipients', __('Destinatarios', 'tureserva'));
                ?>
            </table>
        </div>

        <!-- =======================
        EMAIL: RESERVA APROBADA (POR PAGO)
        ======================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>💳 <?php _e('Email de reserva aprobada (por pago)', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_admin_field('tureserva_email_admin_payment_subject', __('Asunto', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_payment_header', __('Cabecera', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_payment_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                tureserva_email_admin_field('tureserva_email_admin_payment_recipients', __('Destinatarios', 'tureserva'));
                ?>
            </table>
        </div>

        <!-- =======================
        EMAIL: RESERVA CANCELADA
        ======================== -->
        <div class="tureserva-card" style="margin-top:25px;">
            <h3>❌ <?php _e('Email de reserva cancelada', 'tureserva'); ?></h3>
            <table class="form-table">
                <?php
                tureserva_email_admin_field('tureserva_email_admin_cancel_subject', __('Asunto', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_cancel_header', __('Cabecera', 'tureserva'));
                tureserva_email_admin_field('tureserva_email_admin_cancel_body', __('Plantilla de email', 'tureserva'), 'textarea', 10);
                tureserva_email_admin_field('tureserva_email_admin_cancel_recipients', __('Destinatarios', 'tureserva'));
                ?>
            </table>
        </div>

        <?php submit_button(__('Guardar cambios', 'tureserva'), 'primary', 'tureserva_save_admin_emails'); ?>
    </form>
</div>
