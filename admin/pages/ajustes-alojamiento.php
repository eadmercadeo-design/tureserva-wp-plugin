<?php
/**
 * ==========================================================
 * ADMIN PAGE: Ajustes de Alojamiento — TuReserva
 * ==========================================================
 * Configuración global con pestañas internas:
 * - General
 * - Emails (Administrador)
 * - Emails (Cliente)
 * - Pagos
 * - Sincronización
 * - Avanzado
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧭 Registrar submenú bajo "Alojamiento"
// =======================================================
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=alojamiento',
        __('Ajustes de Alojamiento', 'tureserva'),
        __('Ajustes', 'tureserva'),
        'manage_options',
        'tureserva-ajustes-alojamiento',
        'tureserva_render_ajustes_alojamiento_page'
    );
});

// =======================================================
// 🧾 Renderizar página de ajustes con pestañas
// =======================================================
function tureserva_render_ajustes_alojamiento_page() {

    // =======================================================
    // 💾 GUARDAR AJUSTES
    // =======================================================
    if (isset($_POST['tureserva_ajustes_nonce']) && wp_verify_nonce($_POST['tureserva_ajustes_nonce'], 'tureserva_ajustes_action')) {

        // --- Sección General ---
        update_option('tureserva_checkin', sanitize_text_field($_POST['checkin']));
        update_option('tureserva_checkout', sanitize_text_field($_POST['checkout']));
        update_option('tureserva_capacidad_maxima', intval($_POST['capacidad_maxima']));
        update_option('tureserva_divisa', sanitize_text_field($_POST['divisa']));
        update_option('tureserva_formato_precio', sanitize_text_field($_POST['formato_precio']));

        // --- Sección Email (Administrador) ---
        $tipos_admin = ['pendiente', 'aprobada', 'pagada', 'cancelada'];
        foreach ($tipos_admin as $slug) {
            update_option("tureserva_email_{$slug}_activo", isset($_POST["email_{$slug}_activo"]));
            update_option("tureserva_email_{$slug}_tema", sanitize_text_field($_POST["email_{$slug}_tema"] ?? ''));
            update_option("tureserva_email_{$slug}_cabecera", sanitize_text_field($_POST["email_{$slug}_cabecera"] ?? ''));
            update_option("tureserva_email_{$slug}_cuerpo", wp_kses_post($_POST["email_{$slug}_cuerpo"] ?? ''));
            update_option("tureserva_email_{$slug}_destinos", sanitize_text_field($_POST["email_{$slug}_destinos"] ?? ''));
        }

        // --- Sección Email (Cliente) ---
        $tipos_cliente = ['nueva_admin', 'nueva_usuario', 'aprobada', 'cancelada', 'registro', 'bloque_cancelacion'];
        foreach ($tipos_cliente as $slug) {
            update_option("tureserva_cliente_email_{$slug}_activo", isset($_POST["cliente_email_{$slug}_activo"]));
            update_option("tureserva_cliente_email_{$slug}_tema", sanitize_text_field($_POST["cliente_email_{$slug}_tema"] ?? ''));
            update_option("tureserva_cliente_email_{$slug}_cabecera", sanitize_text_field($_POST["cliente_email_{$slug}_cabecera"] ?? ''));
            update_option("tureserva_cliente_email_{$slug}_cuerpo", wp_kses_post($_POST["cliente_email_{$slug}_cuerpo"] ?? ''));
        }

        // --- Email Pre-checkin (1h antes) ---
        update_option('tureserva_cliente_email_precheckin_activo', isset($_POST['cliente_email_precheckin_activo']));
        update_option('tureserva_cliente_email_precheckin_tema', sanitize_text_field($_POST['cliente_email_precheckin_tema'] ?? ''));
        update_option('tureserva_cliente_email_precheckin_cuerpo', wp_kses_post($_POST['cliente_email_precheckin_cuerpo'] ?? ''));

        // --- Campañas especiales ---
        update_option('tureserva_cliente_email_campania_tema', sanitize_text_field($_POST['cliente_email_campania_tema'] ?? ''));
        update_option('tureserva_cliente_email_campania_cuerpo', wp_kses_post($_POST['cliente_email_campania_cuerpo'] ?? ''));

        echo '<div class="updated notice"><p>' . __('✅ Ajustes guardados correctamente.', 'tureserva') . '</p></div>';
    }

    // =======================================================
    // 🔄 CARGAR VALORES
    // =======================================================
    $checkin  = get_option('tureserva_checkin', '14:00');
    $checkout = get_option('tureserva_checkout', '12:00');
    $capacidad = get_option('tureserva_capacidad_maxima', 6);
    $divisa = get_option('tureserva_divisa', 'USD');
    $formato_precio = get_option('tureserva_formato_precio', '$0,000.00');

    ?>

    <div class="wrap">
        <h1><span class="dashicons dashicons-admin-generic" style="color:#2271b1;margin-right:8px;"></span><?php _e('Ajustes de Alojamiento', 'tureserva'); ?></h1>
        <p class="description"><?php _e('Configura los parámetros globales y opciones avanzadas aplicables a todos los alojamientos.', 'tureserva'); ?></p>

        <style>
            .nav-tab-wrapper { margin-top: 20px; }
            .tureserva-card {
                background: #fff;
                padding: 20px;
                margin-top: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                max-width: 900px;
            }
            .tureserva-card h2 { font-size: 1.2em; margin-bottom: 15px; }
            .tab-section { display: none; }
            .tab-section.active { display: block; }
            hr { border: none; border-top: 1px solid #ddd; margin: 30px 0; }
        </style>

        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'tureserva'); ?></a>
            <a href="#email_admin" class="nav-tab"><?php _e('Email (Administrador)', 'tureserva'); ?></a>
            <a href="#email_cliente" class="nav-tab"><?php _e('Email (Cliente)', 'tureserva'); ?></a>
            <a href="#pagos" class="nav-tab"><?php _e('Pagos', 'tureserva'); ?></a>
            <a href="#sync" class="nav-tab"><?php _e('Sincronización', 'tureserva'); ?></a>
            <a href="#avanzado" class="nav-tab"><?php _e('Avanzado', 'tureserva'); ?></a>
        </h2>

        <form method="post">
            <?php wp_nonce_field('tureserva_ajustes_action', 'tureserva_ajustes_nonce'); ?>

            <!-- =======================================================
                 PESTAÑA GENERAL
            ======================================================= -->
            <div id="general" class="tureserva-card tab-section active">
                <h2><?php _e('Parámetros generales', 'tureserva'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="checkin"><?php _e('Hora de Check-in', 'tureserva'); ?></label></th>
                        <td><input type="time" id="checkin" name="checkin" value="<?php echo esc_attr($checkin); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="checkout"><?php _e('Hora de Check-out', 'tureserva'); ?></label></th>
                        <td><input type="time" id="checkout" name="checkout" value="<?php echo esc_attr($checkout); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="capacidad_maxima"><?php _e('Capacidad máxima por alojamiento', 'tureserva'); ?></label></th>
                        <td><input type="number" id="capacidad_maxima" name="capacidad_maxima" value="<?php echo esc_attr($capacidad); ?>" min="1" max="20"></td>
                    </tr>
                    <tr>
                        <th><label for="divisa"><?php _e('Divisa', 'tureserva'); ?></label></th>
                        <td>
                            <select id="divisa" name="divisa">
                                <option value="USD" <?php selected($divisa, 'USD'); ?>>USD – Dólar estadounidense</option>
                                <option value="EUR" <?php selected($divisa, 'EUR'); ?>>EUR – Euro</option>
                                <option value="COP" <?php selected($divisa, 'COP'); ?>>COP – Peso colombiano</option>
                                <option value="PAB" <?php selected($divisa, 'PAB'); ?>>PAB – Balboa panameño</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="formato_precio"><?php _e('Formato de precio', 'tureserva'); ?></label></th>
                        <td>
                            <input type="text" id="formato_precio" name="formato_precio" value="<?php echo esc_attr($formato_precio); ?>">
                            <p class="description"><?php _e('Ejemplo: $0,000.00 — puede usar separadores de miles y decimales.', 'tureserva'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- =======================================================
                 EMAIL ADMINISTRADOR
            ======================================================= -->
            <div id="email_admin" class="tureserva-card tab-section">
                <h2><?php _e('Emails del administrador', 'tureserva'); ?></h2>
                <p><?php _e('Define los correos automáticos que el sistema enviará al administrador en diferentes etapas de la reserva.', 'tureserva'); ?></p>
                <?php
                $tipos = [
                    'pendiente' => __('Email de reserva pendiente', 'tureserva'),
                    'aprobada'  => __('Email de reserva aprobada', 'tureserva'),
                    'pagada'    => __('Email de reserva pagada', 'tureserva'),
                    'cancelada' => __('Email de reserva cancelada', 'tureserva'),
                ];
                foreach ($tipos as $slug => $titulo) :
                    $activar   = get_option("tureserva_email_{$slug}_activo", true);
                    $tema      = get_option("tureserva_email_{$slug}_tema", "%site_title% – Reserva #%booking_id%");
                    $cabecera  = get_option("tureserva_email_{$slug}_cabecera", ucfirst($slug));
                    $cuerpo    = get_option("tureserva_email_{$slug}_cuerpo", "La reserva #%booking_id% requiere atención.");
                    $destinos  = get_option("tureserva_email_{$slug}_destinos", get_option('admin_email'));
                ?>
                    <hr>
                    <h3><?php echo esc_html($titulo); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Activar notificación', 'tureserva'); ?></th>
                            <td><input type="checkbox" name="email_<?php echo esc_attr($slug); ?>_activo" value="1" <?php checked($activar, true); ?>> <?php _e('Enviar automáticamente', 'tureserva'); ?></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Tema', 'tureserva'); ?></label></th>
                            <td><input type="text" name="email_<?php echo esc_attr($slug); ?>_tema" value="<?php echo esc_attr($tema); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Cabecera', 'tureserva'); ?></label></th>
                            <td><input type="text" name="email_<?php echo esc_attr($slug); ?>_cabecera" value="<?php echo esc_attr($cabecera); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Cuerpo', 'tureserva'); ?></label></th>
                            <td><?php wp_editor($cuerpo, "email_{$slug}_cuerpo", ['textarea_name'=>"email_{$slug}_cuerpo",'textarea_rows'=>8,'teeny'=>true,'media_buttons'=>false]); ?></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Destinatarios', 'tureserva'); ?></label></th>
                            <td><input type="text" name="email_<?php echo esc_attr($slug); ?>_destinos" value="<?php echo esc_attr($destinos); ?>" class="regular-text"></td>
                        </tr>
                    </table>
                <?php endforeach; ?>
            </div>

            <!-- =======================================================
                 EMAIL CLIENTE (incluye pre-checkin y campañas)
            ======================================================= -->
            <div id="email_cliente" class="tureserva-card tab-section">
                <h2><?php _e('Emails del cliente', 'tureserva'); ?></h2>
                <p><?php _e('Correos automáticos y programados que el sistema enviará al huésped.', 'tureserva'); ?></p>

                <?php
                $tipos_cliente = [
                    'nueva_admin' => __('Email de nueva reserva (confirmación por administrador)', 'tureserva'),
                    'nueva_usuario' => __('Email de nueva reserva (confirmación por usuario)', 'tureserva'),
                    'aprobada' => __('Email de reserva aprobada', 'tureserva'),
                    'cancelada' => __('Email de reserva cancelada', 'tureserva'),
                    'registro' => __('Email de registro de cuenta', 'tureserva'),
                    'bloque_cancelacion' => __('Plantilla de detalles de cancelación', 'tureserva'),
                ];
                foreach ($tipos_cliente as $slug => $titulo) :
                    $tema = get_option("tureserva_cliente_email_{$slug}_tema", "%site_title% – Reserva #%booking_id%");
                    $cabecera = get_option("tureserva_cliente_email_{$slug}_cabecera", ucfirst($slug));
                    $cuerpo = get_option("tureserva_cliente_email_{$slug}_cuerpo", "Estimado cliente, su reserva está en proceso.");
                ?>
                    <hr>
                    <h3><?php echo esc_html($titulo); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label><?php _e('Tema', 'tureserva'); ?></label></th>
                            <td><input type="text" name="cliente_email_<?php echo esc_attr($slug); ?>_tema" value="<?php echo esc_attr($tema); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Cabecera', 'tureserva'); ?></label></th>
                            <td><input type="text" name="cliente_email_<?php echo esc_attr($slug); ?>_cabecera" value="<?php echo esc_attr($cabecera); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Cuerpo del correo', 'tureserva'); ?></label></th>
                            <td><?php wp_editor($cuerpo, "cliente_email_{$slug}_cuerpo", ['textarea_name'=>"cliente_email_{$slug}_cuerpo",'textarea_rows'=>8,'teeny'=>true,'media_buttons'=>false]); ?></td>
                        </tr>
                    </table>
                <?php endforeach; ?>

                <!-- BLOQUE EXTRA: pre-checkin y campañas -->
                <?php include TURESERVA_PATH . 'admin/pages/partials/email-precheckin-campania.php'; ?>
            </div>

            <!-- =======================================================
                 RESTO DE PESTAÑAS
            ======================================================= -->
            <div id="pagos" class="tureserva-card tab-section"><h2><?php _e('Opciones de pago', 'tureserva'); ?></h2><p><?php _e('Próximamente: integración con Stripe y PayPal.', 'tureserva'); ?></p></div>
            <div id="sync" class="tureserva-card tab-section"><h2><?php _e('Sincronización Cloud y Calendarios', 'tureserva'); ?></h2><p><?php _e('Próximamente: conexión con Supabase e iCal.', 'tureserva'); ?></p></div>
            <div id="avanzado" class="tureserva-card tab-section"><h2><?php _e('Opciones avanzadas', 'tureserva'); ?></h2><p><?php _e('Configuraciones adicionales del sistema TuReserva.', 'tureserva'); ?></p></div>

            <?php submit_button(__('Guardar ajustes', 'tureserva')); ?>
        </form>
    </div>

    <script>
    const tabs=document.querySelectorAll('.nav-tab');
    const sections=document.querySelectorAll('.tab-section');
    tabs.forEach(tab=>{
        tab.addEventListener('click',e=>{
            e.preventDefault();
            tabs.forEach(t=>t.classList.remove('nav-tab-active'));
            sections.forEach(s=>s.classList.remove('active'));
            tab.classList.add('nav-tab-active');
            const target=document.querySelector(tab.getAttribute('href'));
            target.classList.add('active');
        });
    });
    </script>

    <?php
}
