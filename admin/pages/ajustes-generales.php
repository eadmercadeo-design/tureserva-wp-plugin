<?php
/**
 * ==========================================================
 * ADMIN PAGE: Ajustes Generales — TuReserva
 * ==========================================================
 * Panel central con pestañas estilo MotoPress.
 * Guarda opciones globales: páginas, divisa, horarios, email, pagos, etc.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Registrar submenú bajo "Alojamiento"
// =======================================================
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=alojamiento',
        __('Ajustes generales', 'tureserva'),
        __('Ajustes', 'tureserva'),
        'manage_options',
        'tureserva-ajustes-generales',
        'tureserva_render_ajustes_generales_page'
    );
});

// =======================================================
// 🧾 Renderizado principal
// =======================================================
function tureserva_render_ajustes_generales_page() {

    // 💾 Guardar opciones globales
    if (isset($_POST['tureserva_ajustes_nonce']) && wp_verify_nonce($_POST['tureserva_ajustes_nonce'], 'tureserva_ajustes_action')) {
        update_option('tureserva_pagina_busqueda', intval($_POST['pagina_busqueda']));
        update_option('tureserva_pagina_pago', intval($_POST['pagina_pago']));
        update_option('tureserva_checkin', sanitize_text_field($_POST['checkin']));
        update_option('tureserva_checkout', sanitize_text_field($_POST['checkout']));
        update_option('tureserva_divisa', sanitize_text_field($_POST['divisa']));
        update_option('tureserva_formato_fecha', sanitize_text_field($_POST['formato_fecha']));
        update_option('tureserva_formato_hora', sanitize_text_field($_POST['formato_hora']));
        echo '<div class="updated notice"><p>' . __('✅ Ajustes guardados correctamente.', 'tureserva') . '</p></div>';
    }

    // 📄 Obtener valores actuales
    $paginas = get_pages();
    $pagina_busqueda = get_option('tureserva_pagina_busqueda', 0);
    $pagina_pago = get_option('tureserva_pagina_pago', 0);
    $checkin = get_option('tureserva_checkin', '14:00');
    $checkout = get_option('tureserva_checkout', '12:00');
    $divisa = get_option('tureserva_divisa', 'USD');
    $formato_fecha = get_option('tureserva_formato_fecha', 'd/m/Y');
    $formato_hora = get_option('tureserva_formato_hora', 'H:i');
    ?>

    <div class="wrap tureserva-ajustes">
        <h1>
            <span class="dashicons dashicons-admin-generic" style="color:#2271b1;margin-right:8px;"></span>
            <?php _e('Ajustes generales', 'tureserva'); ?>
        </h1>

        <style>
            .nav-tab-wrapper { margin-top: 20px; }
            .tureserva-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                margin-top: 10px;
                max-width: 950px;
            }
            .form-table th { width: 240px; vertical-align: top; }
        </style>

        <!-- 📑 PESTAÑAS PRINCIPALES -->
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'tureserva'); ?></a>
            <a href="#email" class="nav-tab"><?php _e('Emails', 'tureserva'); ?></a>
            <a href="#pagos" class="nav-tab"><?php _e('Pagos', 'tureserva'); ?></a>
            <a href="#sync" class="nav-tab"><?php _e('Sincronización', 'tureserva'); ?></a>
            <a href="#avanzado" class="nav-tab"><?php _e('Avanzado', 'tureserva'); ?></a>
        </h2>

        <form method="post">
            <?php wp_nonce_field('tureserva_ajustes_action', 'tureserva_ajustes_nonce'); ?>

            <!-- ======================================================= -->
            <!-- 🏠 TAB GENERAL -->
            <!-- ======================================================= -->
            <div id="general" class="tureserva-card tab-section" style="display:block;">
                <h2><?php _e('Páginas principales', 'tureserva'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="pagina_busqueda"><?php _e('Página de resultados de búsqueda', 'tureserva'); ?></label></th>
                        <td>
                            <select id="pagina_busqueda" name="pagina_busqueda">
                                <option value="0"><?php _e('— Seleccione una página —', 'tureserva'); ?></option>
                                <?php foreach ($paginas as $p) : ?>
                                    <option value="<?php echo $p->ID; ?>" <?php selected($pagina_busqueda, $p->ID); ?>>
                                        <?php echo esc_html($p->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="pagina_pago"><?php _e('Página de pago', 'tureserva'); ?></label></th>
                        <td>
                            <select id="pagina_pago" name="pagina_pago">
                                <option value="0"><?php _e('— Seleccione una página —', 'tureserva'); ?></option>
                                <?php foreach ($paginas as $p) : ?>
                                    <option value="<?php echo $p->ID; ?>" <?php selected($pagina_pago, $p->ID); ?>>
                                        <?php echo esc_html($p->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php _e('Horarios estándar', 'tureserva'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="checkin"><?php _e('Hora de Check-in', 'tureserva'); ?></label></th>
                        <td><input type="time" name="checkin" value="<?php echo esc_attr($checkin); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="checkout"><?php _e('Hora de Check-out', 'tureserva'); ?></label></th>
                        <td><input type="time" name="checkout" value="<?php echo esc_attr($checkout); ?>"></td>
                    </tr>
                </table>

                <h2><?php _e('Formato regional', 'tureserva'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="divisa"><?php _e('Divisa por defecto', 'tureserva'); ?></label></th>
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
                        <th><label for="formato_fecha"><?php _e('Formato de fecha', 'tureserva'); ?></label></th>
                        <td><input type="text" name="formato_fecha" value="<?php echo esc_attr($formato_fecha); ?>" placeholder="d/m/Y"></td>
                    </tr>
                    <tr>
                        <th><label for="formato_hora"><?php _e('Formato de hora', 'tureserva'); ?></label></th>
                        <td><input type="text" name="formato_hora" value="<?php echo esc_attr($formato_hora); ?>" placeholder="H:i"></td>
                    </tr>
                </table>
            </div>

            <!-- ======================================================= -->
            <!-- ✉️ TAB EMAIL (con subpestañas) -->
            <!-- ======================================================= -->
            <div id="email" class="tureserva-card tab-section" style="display:none;">
                <style>
                    .tureserva-subtabs { margin-bottom: 20px; border-bottom: 1px solid #ccc; }
                    .tureserva-subtabs a {
                        display:inline-block; padding:6px 14px; text-decoration:none;
                        border:1px solid #ccc; border-bottom:none; margin-right:4px;
                        background:#f1f1f1; color:#333; border-radius:4px 4px 0 0;
                        font-weight:600;
                    }
                    .tureserva-subtabs a.active { background:#fff; border-bottom:1px solid #fff; }
                    .email-subtab { display:none; }
                </style>

                <div class="tureserva-subtabs">
                    <a href="#emails-admin" class="subtab-link active"><?php _e('Emails del admin', 'tureserva'); ?></a>
                    <a href="#emails-cliente" class="subtab-link"><?php _e('Emails del cliente', 'tureserva'); ?></a>
                </div>

                <div id="emails-admin" class="email-subtab" style="display:block;">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/emails/admin.php'; ?>
                </div>

                <div id="emails-cliente" class="email-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/emails/cliente.php'; ?>
                </div>

                <script>
                    const emailTabs = document.querySelectorAll('#email .subtab-link');
                    const emailSections = document.querySelectorAll('#email .email-subtab');
                    emailTabs.forEach(link => {
                        link.addEventListener('click', e => {
                            e.preventDefault();
                            emailTabs.forEach(t => t.classList.remove('active'));
                            emailSections.forEach(s => s.style.display = 'none');
                            link.classList.add('active');
                            document.querySelector(link.getAttribute('href')).style.display = 'block';
                        });
                    });
                </script>
            </div>

            <!-- ======================================================= -->
            <!-- 💳 TAB PAGOS (con subpestañas) -->
            <!-- ======================================================= -->
            <div id="pagos" class="tureserva-card tab-section" style="display:none;">
                <style>
                    .tureserva-subtabs { margin-bottom: 20px; border-bottom: 1px solid #ccc; }
                    .tureserva-subtabs a {
                        display:inline-block; padding:6px 14px; text-decoration:none;
                        border:1px solid #ccc; border-bottom:none; margin-right:4px;
                        background:#f1f1f1; color:#333; border-radius:4px 4px 0 0;
                        font-weight:600;
                    }
                    .tureserva-subtabs a.active { background:#fff; border-bottom:1px solid #fff; }
                    .pago-subtab { display:none; }
                </style>

                <!-- 🧾 Subpestañas -->
                <div class="tureserva-subtabs">
                    <a href="#pago-configuracion" class="pago-link active"><?php _e('Configuración Global', 'tureserva'); ?></a>
                    <a href="#pago-probar" class="pago-link"><?php _e('Probar pago', 'tureserva'); ?></a>
                    <a href="#pago-stripe" class="pago-link"><?php _e('Stripe', 'tureserva'); ?></a>
                    <a href="#pago-paypal" class="pago-link"><?php _e('PayPal', 'tureserva'); ?></a>
                    <a href="#pago-transferencia" class="pago-link"><?php _e('Transferencia', 'tureserva'); ?></a>
                    <a href="#pago-manual" class="pago-link"><?php _e('Manual / Efectivo', 'tureserva'); ?></a>
                    <?php if (is_plugin_active('woocommerce/woocommerce.php')) : ?>
                        <a href="#pago-woocommerce" class="pago-link"><?php _e('WooCommerce', 'tureserva'); ?></a>
                    <?php endif; ?>
                </div>

                <!-- 🧩 Subpestañas de contenido -->
                <div id="pago-configuracion" class="pago-subtab" style="display:block;">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/configuracion-global.php'; ?>
                </div>

                <div id="pago-probar" class="pago-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/probar-pago.php'; ?>
                </div>

                <div id="pago-stripe" class="pago-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/stripe.php'; ?>
                </div>

                <div id="pago-paypal" class="pago-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/paypal.php'; ?>
                </div>

                <div id="pago-transferencia" class="pago-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/transferencia.php'; ?>
                </div>

                <div id="pago-manual" class="pago-subtab">
                    <?php include TURESERVA_PATH . 'admin/pages/partials/pagos/manual.php'; ?>
                </div>

                <?php if (is_plugin_active('woocommerce/woocommerce.php')) : ?>
                    <div id="pago-woocommerce" class="pago-subtab">
                        <h3>🛒 <?php _e('Integración con WooCommerce', 'tureserva'); ?></h3>
                        <p><?php _e('WooCommerce está activo. Puede usar sus pasarelas de pago para procesar reservas.', 'tureserva'); ?></p>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Activar integración', 'tureserva'); ?></th>
                                <td>
                                    <input type="checkbox" name="tureserva_pago_woo_enable" value="1" <?php checked(get_option('tureserva_pago_woo_enable'), 1); ?>>
                                    <?php _e('Permitir pagos con WooCommerce.', 'tureserva'); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Script de subpestañas -->
                <script>
                    const payTabs = document.querySelectorAll('#pagos .pago-link');
                    const paySections = document.querySelectorAll('#pagos .pago-subtab');
                    payTabs.forEach(tab => {
                        tab.addEventListener('click', e => {
                            e.preventDefault();
                            payTabs.forEach(t => t.classList.remove('active'));
                            paySections.forEach(s => s.style.display = 'none');
                            tab.classList.add('active');
                            document.querySelector(tab.getAttribute('href')).style.display = 'block';
                        });
                    });
                </script>
            </div>

            <!-- ======================================================= -->
            <!-- 🌐 TAB SYNC -->
            <!-- ======================================================= -->
            <div id="sync" class="tureserva-card tab-section" style="display:none;">
                <h2><?php _e('Sincronización de calendarios y Cloud', 'tureserva'); ?></h2>
                <p><?php _e('Opciones para integrar Supabase y calendarios externos (Google, Airbnb, etc.).', 'tureserva'); ?></p>
            </div>

            <!-- ======================================================= -->
            <!-- ⚙️ TAB AVANZADO -->
            <!-- ======================================================= -->
            <div id="avanzado" class="tureserva-card tab-section" style="display:none;">
                <h2><?php _e('Opciones avanzadas', 'tureserva'); ?></h2>
                <p><?php _e('Herramientas de depuración, licencias y configuración del sistema.', 'tureserva'); ?></p>
            </div>

        </form>
    </div>

    <script>
        const mainTabs = document.querySelectorAll('.nav-tab');
        const sections = document.querySelectorAll('.tab-section');
        mainTabs.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                mainTabs.forEach(t => t.classList.remove('nav-tab-active'));
                sections.forEach(s => s.style.display = 'none');
                tab.classList.add('nav-tab-active');
                document.querySelector(tab.getAttribute('href')).style.display = 'block';
            });
        });
    </script>
    <?php
}
