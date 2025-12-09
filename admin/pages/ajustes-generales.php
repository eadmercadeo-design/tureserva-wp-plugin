<?php
/**
 * ADMIN PAGE: Ajustes Generales — TuReserva (Rediseño Moderno)
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Registrar submenú
// =======================================================
// (Manejado centralizadamente en includes/menu-alojamiento.php)

// =======================================================
// 🔧 Función auxiliar para renderizar Cards (Compartida)
// =======================================================
if (!function_exists('tureserva_render_email_card')) {
    function tureserva_render_email_card($id_base, $title, $icon, $desc, $default_subject = '', $default_body = '', $extra_fields = '') {
        $disable = get_option($id_base . '_disable', 0);
        $subject = get_option($id_base . '_subject', $default_subject);
        $header  = get_option($id_base . '_header', get_bloginfo('name'));
        $body    = get_option($id_base . '_body', $default_body);
        $recipients = get_option($id_base . '_recipients', ''); // Para cliente suele ser dinámico, pero dejamos el campo por si acaso se quiere copia oculta

        ?>
        <div class="tureserva-card">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f0f0f1; padding-bottom:15px; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="dashicons <?php echo esc_attr($icon); ?>" style="font-size:24px; color:#2271b1; height:24px; width:24px;"></span>
                    <div>
                        <h3 style="margin:0; font-size:16px;"><?php echo esc_html($title); ?></h3>
                        <p style="margin:0; font-size:12px; color:#666;"><?php echo esc_html($desc); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:12px; font-weight:600; color:#50575e;">Desactivar notificación</span>
                    <label class="ts-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($id_base); ?>_disable" value="1" <?php checked($disable, 1); ?>>
                        <span class="ts-slider"></span>
                    </label>
                </div>
            </div>

            <?php if ($extra_fields) : ?>
                <div style="background:#f9f9f9; padding:15px; border-radius:5px; margin-bottom:15px; border:1px solid #eee;">
                    <?php echo $extra_fields; ?>
                </div>
            <?php endif; ?>

            <div class="tureserva-grid-2">
                <div class="ts-form-group">
                    <label>Asunto del correo</label>
                    <input type="text" name="<?php echo esc_attr($id_base); ?>_subject" value="<?php echo esc_attr($subject); ?>" placeholder="Ej: Información importante">
                </div>
                <div class="ts-form-group">
                    <label>Encabezado del correo</label>
                    <input type="text" name="<?php echo esc_attr($id_base); ?>_header" value="<?php echo esc_attr($header); ?>" placeholder="Ej: Detalles de tu reserva">
                </div>
            </div>

            <div class="ts-form-group">
                <label>Plantilla del correo</label>
                <div style="border:1px solid #ccc; border-radius:4px; overflow:hidden;">
                    <?php wp_editor($body, $id_base . '_body', ['textarea_name' => $id_base . '_body', 'textarea_rows' => 10, 'media_buttons' => true, 'teeny' => true]); ?>
                </div>
            </div>
        </div>
        <?php
    }
}

// =======================================================
// 🧾 Renderizado principal
// =======================================================
function tureserva_render_ajustes_generales_page() {
    
    // Obtener páginas para selectores
    $paginas = get_pages();
    
    // Guardar Ajustes
    if (isset($_POST['tureserva_ajustes_nonce']) && wp_verify_nonce($_POST['tureserva_ajustes_nonce'], 'tureserva_ajustes_action')) {
        
        // 0. Validación específica para Correos Admin
        if (isset($_POST['tureserva_admin_emails'])) {
            $raw_emails = $_POST['tureserva_admin_emails'];
            $emails_array = explode(',', $raw_emails);
            $valid_emails = array();
            $email_errors = array();
            
            foreach ($emails_array as $email) {
                $email = trim($email);
                if (is_email($email)) {
                    $valid_emails[] = $email;
                } elseif (!empty($email)) {
                    $email_errors[] = $email;
                }
            }
            
            // Sobrescribir el POST con el string limpio para que el bucle genérico lo guarde
            $_POST['tureserva_admin_emails'] = implode(', ', $valid_emails);

            if (!empty($email_errors)) {
                echo '<div class="notice notice-warning is-dismissible"><p>⚠️ Algunos correos no eran válidos y se omitieron: ' . esc_html(implode(', ', $email_errors)) . '</p></div>';
            }
        }

        // 1. Guardar opciones genéricas (tureserva_*)
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'tureserva_') === 0) {
                // Si es array, guardar tal cual, si no, sanitizar
                $val = is_array($value) ? $value : wp_kses_post($value);
                update_option($key, $val);
            }
        }

        // 2. Manejo de Checkboxes (que no envían valor si están desmarcados)
        $checkboxes = [
            'tureserva_mostrar_precios_bajos',
            'tureserva_email_admin_pending_disable',
            'tureserva_email_admin_approved_disable',
            'tureserva_email_admin_payment_disable',
            'tureserva_email_admin_cancel_disable',
            'tureserva_email_client_confirmation_disable',
            'tureserva_email_client_cancel_disable',
            'tureserva_email_client_pre_arrival_disable',
            'tureserva_email_client_location_disable',
            'tureserva_payment_test_enable',
            'tureserva_payment_arrival_enable',
            'tureserva_payment_bank_enable',
            'tureserva_payment_paypal_enable',
            'tureserva_payment_paypal_sandbox',
            'tureserva_payment_stripe_enable',
            'tureserva_payment_stripe_testmode',
            'tureserva_payment_wc_enable'
        ];
        foreach ($checkboxes as $cb) {
            if (!isset($_POST[$cb])) update_option($cb, 0);
        }

        echo '<div class="notice notice-success is-dismissible"><p>✅ Ajustes guardados correctamente.</p></div>';
    }

    ?>
    <div class="wrap tureserva-settings-wrap">
        
        <div class="tureserva-header">
            <h1>Ajustes de TuReserva</h1>
            <button class="ts-btn-primary" onclick="document.getElementById('main-settings-form').submit();">Guardar Cambios</button>
        </div>

        <!-- TABS NAV -->
        <div class="tureserva-tabs-nav">
            <a href="#tab-general" class="tureserva-tab-link active">General</a>
            <a href="#tab-email-admin" class="tureserva-tab-link">Email Administrador</a>
            <a href="#tab-email-cliente" class="tureserva-tab-link">Email Cliente</a>
            <a href="#tab-pagos" class="tureserva-tab-link">Pasarela de Pago</a>
            <a href="#tab-licencia" class="tureserva-tab-link">Licencia</a>
        </div>

        <form method="post" id="main-settings-form">
            <?php wp_nonce_field('tureserva_ajustes_action', 'tureserva_ajustes_nonce'); ?>

            <!-- 🔵 TAB GENERAL -->
            <div id="tab-general" class="tureserva-tab-content active">
                
                <!-- Bloque: Formularios y Páginas -->
                <div class="tureserva-card">
                    <h3>Formularios y páginas</h3>
                    <div class="tureserva-grid-2">
                        <div class="ts-form-group">
                            <label>Página de resultados de búsqueda</label>
                            <select name="tureserva_pagina_busqueda">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($paginas as $p) echo "<option value='{$p->ID}' " . selected(get_option('tureserva_pagina_busqueda'), $p->ID, false) . ">{$p->post_title}</option>"; ?>
                            </select>
                        </div>
                        <div class="ts-form-group">
                            <label>Página de pago (Checkout)</label>
                            <select name="tureserva_pagina_pago">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($paginas as $p) echo "<option value='{$p->ID}' " . selected(get_option('tureserva_pagina_pago'), $p->ID, false) . ">{$p->post_title}</option>"; ?>
                            </select>
                        </div>
                        <div class="ts-form-group">
                            <label>Términos y condiciones</label>
                            <select name="tureserva_pagina_terminos">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($paginas as $p) echo "<option value='{$p->ID}' " . selected(get_option('tureserva_pagina_terminos'), $p->ID, false) . ">{$p->post_title}</option>"; ?>
                            </select>
                        </div>
                        <div class="ts-form-group">
                            <label>Página de confirmación de reserva</label>
                            <select name="tureserva_pagina_confirmacion">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($paginas as $p) echo "<option value='{$p->ID}' " . selected(get_option('tureserva_pagina_confirmacion'), $p->ID, false) . ">{$p->post_title}</option>"; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bloque: Comportamiento del Sistema -->
                <div class="tureserva-card">
                    <h3>Comportamiento del sistema</h3>
                    <div class="tureserva-grid-2">
                        <div class="ts-form-group">
                            <label>Hora de Entrada (Check-in)</label>
                            <input type="time" name="tureserva_checkin" value="<?php echo esc_attr(get_option('tureserva_checkin', '14:00')); ?>">
                        </div>
                        <div class="ts-form-group">
                            <label>Hora de Salida (Check-out)</label>
                            <input type="time" name="tureserva_checkout" value="<?php echo esc_attr(get_option('tureserva_checkout', '11:00')); ?>">
                        </div>
                        <div class="ts-form-group">
                            <label>Tipo de camas</label>
                            <input type="text" name="tureserva_tipos_camas" value="<?php echo esc_attr(get_option('tureserva_tipos_camas', '')); ?>" placeholder="Ej: King, Queen, Doble, Sencilla">
                            <span class="ts-helper">Separado por comas</span>
                        </div>
                        <div class="ts-form-group">
                            <label>Mostrar precios más bajos por tipo</label>
                            <label class="ts-toggle">
                                <input type="checkbox" name="tureserva_mostrar_precios_bajos" value="1" <?php checked(get_option('tureserva_mostrar_precios_bajos'), 1); ?>>
                                <span class="ts-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bloque: Moneda y Formato -->
                <div class="tureserva-card">
                    <h3>Moneda y Formato</h3>
                    <div class="tureserva-grid-2">
                        <div class="ts-form-group">
                            <label>Moneda</label>
                            <select name="tureserva_moneda">
                                <option value="USD" <?php selected(get_option('tureserva_moneda'), 'USD'); ?>>USD ($)</option>
                                <option value="EUR" <?php selected(get_option('tureserva_moneda'), 'EUR'); ?>>EUR (€)</option>
                                <option value="COP" <?php selected(get_option('tureserva_moneda'), 'COP'); ?>>COP ($)</option>
                            </select>
                        </div>
                        <div class="ts-form-group">
                            <label>Separador decimal</label>
                            <select name="tureserva_separador_decimal">
                                <option value="." <?php selected(get_option('tureserva_separador_decimal'), '.'); ?>>Punto (.)</option>
                                <option value="," <?php selected(get_option('tureserva_separador_decimal'), ','); ?>>Coma (,)</option>
                            </select>
                        </div>
                        <div class="ts-form-group">
                            <label>Formato de fecha</label>
                            <input type="text" name="tureserva_formato_fecha" value="<?php echo esc_attr(get_option('tureserva_formato_fecha', 'd/m/Y')); ?>">
                        </div>
                        <div class="ts-form-group">
                            <label>Formato de hora</label>
                            <input type="text" name="tureserva_formato_hora" value="<?php echo esc_attr(get_option('tureserva_formato_hora', 'H:i')); ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- 🔶 TAB EMAIL ADMIN -->
            <div id="tab-email-admin" class="tureserva-tab-content">
                <?php include TURESERVA_PATH . 'admin/pages/partials/emails/admin.php'; ?>
            </div>

            <!-- 🔶 TAB EMAIL CLIENTE -->
            <div id="tab-email-cliente" class="tureserva-tab-content">
                <?php include TURESERVA_PATH . 'admin/pages/partials/emails/client.php'; ?>
            </div>

            <!-- 🔶 TAB PASARELA DE PAGO -->
            <div id="tab-pagos" class="tureserva-tab-content">
                <?php include TURESERVA_PATH . 'admin/pages/partials/payments.php'; ?>
            </div>

            <!-- 🔑 TAB LICENCIA -->
            <div id="tab-licencia" class="tureserva-tab-content">
                <div class="tureserva-card">
                    <h3>Estado de la Licencia</h3>
                    <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                        <span class="license-status license-valid">
                            <span class="dashicons dashicons-yes"></span> Licencia Activa
                        </span>
                        <span style="color:#666; font-size:13px;">Vence el: <strong>31/12/2025</strong></span>
                    </div>
                    
                    <div class="ts-form-group">
                        <label>Clave de Licencia</label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" name="tureserva_license_key" value="<?php echo esc_attr(get_option('tureserva_license_key', '')); ?>" style="flex:1;">
                            <button type="button" class="ts-btn-primary">Verificar Licencia</button>
                        </div>
                        <span class="ts-helper">Introduce tu clave de compra para recibir actualizaciones automáticas.</span>
                    </div>
                </div>
            </div>

        </form>
    </div>
    <?php
}
