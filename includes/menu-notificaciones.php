<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔔 MENU: NOTIFICACIONES
// =======================================================
if ( ! function_exists( 'tureserva_menu_notificaciones' ) ) {
    add_action( 'admin_menu', 'tureserva_menu_notificaciones' );
    function tureserva_menu_notificaciones() {
        add_submenu_page(
            'edit.php?post_type=tureserva_reserva',
            'Notificaciones',
            'Notificaciones',
            'manage_options',
            'tureserva_notificaciones',
            'tureserva_panel_notificaciones'
        );
    }
}

// =======================================================
// 🎨 CSS & ASSETS (Local para esta pantalla)
// =======================================================
if ( ! function_exists( 'tureserva_notificaciones_assets' ) ) {
    add_action('admin_head', 'tureserva_notificaciones_assets');
    function tureserva_notificaciones_assets() {
        $screen = get_current_screen();
        // Safety check: ensure screen object exists before accessing ID
        if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'tureserva_notificaciones' ) === false ) {
            return;
        }
        ?>
        <style>
            .tureserva-wrap {
                max-width: 800px;
                margin: 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .tureserva-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 6px;
                padding: 25px;
                margin-bottom: 20px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            }
            .tureserva-card h2 {
                margin-top: 0;
                padding-bottom: 15px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 18px;
                color: #1d2327;
            }
            .ts-form-group {
                margin-bottom: 20px;
            }
            .ts-form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: #3c434a;
            }
            .ts-form-group input[type="text"],
            .ts-form-group input[type="email"],
            .ts-form-group textarea {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                font-size: 14px;
            }
            .ts-form-group textarea {
                min-height: 100px;
            }
            .ts-helper {
                display: block;
                margin-top: 6px;
                font-size: 12px;
                color: #646970;
                font-style: italic;
            }
            .ts-btn-primary {
                background: #2271b1;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
            }
            .ts-btn-primary:hover {
                background: #135e96;
            }
        </style>
        <?php
    }
}

// =======================================================
// ⚙️ RENDERIZADO DEL PANEL
// =======================================================
if ( ! function_exists( 'tureserva_panel_notificaciones' ) ) {
    function tureserva_panel_notificaciones() {
        // 💾 Guardar configuración
        if ( isset( $_POST['tureserva_save_notifications'] ) && check_admin_referer( 'tureserva_notifications_nonce' ) ) {
            if ( isset( $_POST['tureserva_admin_email'] ) ) {
                update_option( 'tureserva_admin_email', sanitize_textarea_field( $_POST['tureserva_admin_email'] ) );
            }
            if ( isset( $_POST['tureserva_from_name'] ) ) {
                update_option( 'tureserva_from_name', sanitize_text_field( $_POST['tureserva_from_name'] ) );
            }
            if ( isset( $_POST['tureserva_from_email'] ) ) {
                update_option( 'tureserva_from_email', sanitize_email( $_POST['tureserva_from_email'] ) );
            }
            
            echo '<div class="notice notice-success is-dismissible"><p>Configuración de notificaciones guardada.</p></div>';
        }

        // Obtener valores actuales
        $admin_email = get_option( 'tureserva_admin_email', get_option( 'admin_email' ) );
        $from_name   = get_option( 'tureserva_from_name', 'TuReserva' );
        $from_email  = get_option( 'tureserva_from_email', get_option( 'admin_email' ) );
        ?>
        <div class="wrap tureserva-wrap">
            <h1>🔔 Configuración de Notificaciones</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'tureserva_notifications_nonce' ); ?>
                
                <div class="tureserva-card">
                    <h2>Ajustes de Correo Electrónico</h2>
                    
                    <!-- Email Administrador -->
                    <div class="ts-form-group">
                        <label for="tureserva_admin_email">Destinatarios de Alertas (Administrador)</label>
                        <textarea id="tureserva_admin_email" name="tureserva_admin_email"><?php echo esc_textarea( $admin_email ); ?></textarea>
                        <span class="ts-helper">Ingresa los correos que recibirán notificaciones de nuevas reservas. Sepáralos por comas (ej: admin@hotel.com, recepcion@hotel.com).</span>
                    </div>

                    <!-- Nombre Remitente -->
                    <div class="ts-form-group">
                        <label for="tureserva_from_name">Nombre del Remitente</label>
                        <input type="text" id="tureserva_from_name" name="tureserva_from_name" value="<?php echo esc_attr( $from_name ); ?>">
                        <span class="ts-helper">El nombre que verán los clientes en sus correos (ej: Hotel Paraíso).</span>
                    </div>

                    <!-- Email Remitente -->
                    <div class="ts-form-group">
                        <label for="tureserva_from_email">Correo del Remitente</label>
                        <input type="email" id="tureserva_from_email" name="tureserva_from_email" value="<?php echo esc_attr( $from_email ); ?>">
                        <span class="ts-helper">La dirección de correo desde la cual se enviarán las notificaciones. Debe pertenecer al dominio para evitar SPAM.</span>
                    </div>
                </div>

                <button type="submit" name="tureserva_save_notifications" class="ts-btn-primary">Guardar Cambios</button>
            </form>
        </div>
        <?php
    }
}
