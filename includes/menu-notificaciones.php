<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Notificaciones — TuReserva
 * ==========================================================
 * Permite configurar:
 *  - Correo remitente y administrador
 *  - Activación de WhatsApp
 *  - Credenciales de API
 *  - Textos de correos automáticos
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Notificaciones"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_notificaciones' );
function tureserva_menu_notificaciones() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas', // Menú principal → Reservas
        'Notificaciones',
        'Notificaciones',
        'manage_options',
        'tureserva_notificaciones',
        'tureserva_panel_notificaciones'
    );
}

// =======================================================
// 🎛️ INTERFAZ DEL PANEL DE CONFIGURACIÓN
// =======================================================
function tureserva_panel_notificaciones() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Guardar configuración
    if ( isset( $_POST['tureserva_guardar_notificaciones'] ) && check_admin_referer( 'tureserva_save_notif', 'tureserva_nonce' ) ) {

        // 📨 Correo
        update_option( 'tureserva_admin_email', sanitize_email( $_POST['tureserva_admin_email'] ?? '' ) );
        update_option( 'tureserva_from_name', sanitize_text_field( $_POST['tureserva_from_name'] ?? '' ) );
        update_option( 'tureserva_from_email', sanitize_email( $_POST['tureserva_from_email'] ?? '' ) );

        // 💬 WhatsApp
        update_option( 'tureserva_whatsapp_enable', isset( $_POST['tureserva_whatsapp_enable'] ) ? 1 : 0 );
        update_option( 'tureserva_whatsapp_api_url', esc_url_raw( $_POST['tureserva_whatsapp_api_url'] ?? '' ) );
        update_option( 'tureserva_whatsapp_token', sanitize_text_field( $_POST['tureserva_whatsapp_token'] ?? '' ) );

        // ✉️ Textos de correos
        update_option( 'tureserva_email_nueva_reserva', wp_kses_post( $_POST['tureserva_email_nueva_reserva'] ?? '' ) );
        update_option( 'tureserva_email_confirmada', wp_kses_post( $_POST['tureserva_email_confirmada'] ?? '' ) );
        update_option( 'tureserva_email_cancelada', wp_kses_post( $_POST['tureserva_email_cancelada'] ?? '' ) );

        echo '<div class="updated"><p>✅ Configuración de notificaciones guardada correctamente.</p></div>';
    }

    // Obtener valores actuales
    $admin_email     = get_option( 'tureserva_admin_email', get_option( 'admin_email' ) );
    $from_name       = get_option( 'tureserva_from_name', 'TuReserva Hotel' );
    $from_email      = get_option( 'tureserva_from_email', get_option( 'admin_email' ) );
    $whatsapp_enable = get_option( 'tureserva_whatsapp_enable', 0 );
    $whatsapp_url    = get_option( 'tureserva_whatsapp_api_url', '' );
    $whatsapp_token  = get_option( 'tureserva_whatsapp_token', '' );

    $email_nueva     = get_option( 'tureserva_email_nueva_reserva', 'Gracias por tu reserva, {nombre_cliente}. Tu solicitud ha sido recibida.' );
    $email_conf      = get_option( 'tureserva_email_confirmada', 'Tu reserva para {alojamiento} ha sido confirmada. Te esperamos desde {check_in}.' );
    $email_cancel    = get_option( 'tureserva_email_cancelada', 'Lamentamos informarte que tu reserva ha sido cancelada.' );
    ?>

    <div class="wrap">
        <h1>📩 Configuración de Notificaciones — TuReserva</h1>
        <form method="post">
            <?php wp_nonce_field( 'tureserva_save_notif', 'tureserva_nonce' ); ?>

            <h2 class="title">📧 Configuración de Correos</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_admin_email">Correo del administrador</label></th>
                    <td><input type="email" name="tureserva_admin_email" id="tureserva_admin_email" value="<?php echo esc_attr( $admin_email ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_from_name">Nombre del remitente</label></th>
                    <td><input type="text" name="tureserva_from_name" id="tureserva_from_name" value="<?php echo esc_attr( $from_name ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_from_email">Correo del remitente</label></th>
                    <td><input type="email" name="tureserva_from_email" id="tureserva_from_email" value="<?php echo esc_attr( $from_email ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <hr>

            <h2 class="title">💬 Configuración de WhatsApp</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_whatsapp_enable">Activar notificaciones por WhatsApp</label></th>
                    <td>
                        <label><input type="checkbox" name="tureserva_whatsapp_enable" id="tureserva_whatsapp_enable" value="1" <?php checked( $whatsapp_enable, 1 ); ?>> Habilitar envío automático</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_whatsapp_api_url">API URL</label></th>
                    <td><input type="url" name="tureserva_whatsapp_api_url" id="tureserva_whatsapp_api_url" value="<?php echo esc_attr( $whatsapp_url ); ?>" class="large-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_whatsapp_token">Token de acceso</label></th>
                    <td><input type="text" name="tureserva_whatsapp_token" id="tureserva_whatsapp_token" value="<?php echo esc_attr( $whatsapp_token ); ?>" class="large-text"></td>
                </tr>
            </table>

            <hr>

            <h2 class="title">📝 Textos de correos automáticos</h2>
            <p>Usa variables como <code>{nombre_cliente}</code>, <code>{alojamiento}</code>, <code>{check_in}</code> y <code>{check_out}</code>.</p>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_email_nueva_reserva">Correo — Nueva reserva</label></th>
                    <td><textarea name="tureserva_email_nueva_reserva" id="tureserva_email_nueva_reserva" rows="5" class="large-text"><?php echo esc_textarea( $email_nueva ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_email_confirmada">Correo — Reserva confirmada</label></th>
                    <td><textarea name="tureserva_email_confirmada" id="tureserva_email_confirmada" rows="5" class="large-text"><?php echo esc_textarea( $email_conf ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_email_cancelada">Correo — Reserva cancelada</label></th>
                    <td><textarea name="tureserva_email_cancelada" id="tureserva_email_cancelada" rows="5" class="large-text"><?php echo esc_textarea( $email_cancel ); ?></textarea></td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="tureserva_guardar_notificaciones" class="button-primary button-large">💾 Guardar configuración</button>
            </p>
        </form>
    </div>
    <?php
}
