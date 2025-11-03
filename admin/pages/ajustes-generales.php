<?php
/**
 * ==========================================================
 * ADMIN PAGE: Ajustes Generales — TuReserva
 * ==========================================================
 * Panel central con pestañas estilo MotoPress.
 * Guarda opciones globales: páginas, divisa, horarios, etc.
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

    // Guardar opciones si se envió el formulario
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

    // Obtener valores actuales
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
        <h1><span class="dashicons dashicons-admin-generic" style="color:#2271b1;margin-right:8px;"></span><?php _e('Ajustes generales', 'tureserva'); ?></h1>

        <style>
            .nav-tab-wrapper {
                margin-top: 20px;
            }
            .tureserva-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                margin-top: 10px;
                max-width: 900px;
            }
            .form-table th { width: 240px; vertical-align: top; }
        </style>

        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'tureserva'); ?></a>
            <a href="#email" class="nav-tab"><?php _e('Email', 'tureserva'); ?></a>
            <a href="#pagos" class="nav-tab"><?php _e('Pagos', 'tureserva'); ?></a>
            <a href="#sync" class="nav-tab"><?php _e('Sincronización', 'tureserva'); ?></a>
            <a href="#avanzado" class="nav-tab"><?php _e('Avanzado', 'tureserva'); ?></a>
        </h2>

        <form method="post">
            <?php wp_nonce_field('tureserva_ajustes_action', 'tureserva_ajustes_nonce'); ?>

            <!-- ======= TAB GENERAL ======= -->
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

            <!-- FUTUROS BLOQUES -->
            <div id="email" class="tureserva-card tab-section" style="display:none;"><p><?php _e('Configuración de correo electrónico (próximamente).', 'tureserva'); ?></p></div>
            <div id="pagos" class="tureserva-card tab-section" style="display:none;"><p><?php _e('Opciones de pago y métodos (en desarrollo).', 'tureserva'); ?></p></div>
            <div id="sync" class="tureserva-card tab-section" style="display:none;"><p><?php _e('Sincronización de calendarios y cloud (Supabase).', 'tureserva'); ?></p></div>
            <div id="avanzado" class="tureserva-card tab-section" style="display:none;"><p><?php _e('Configuraciones avanzadas del sistema.', 'tureserva'); ?></p></div>

            <?php submit_button(__('Guardar cambios', 'tureserva')); ?>
        </form>
    </div>

    <script>
        // Cambiar pestañas sin recargar
        const tabs = document.querySelectorAll('.nav-tab');
        const sections = document.querySelectorAll('.tab-section');
        tabs.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('nav-tab-active'));
                sections.forEach(s => s.style.display = 'none');
                tab.classList.add('nav-tab-active');
                document.querySelector(tab.getAttribute('href')).style.display = 'block';
            });
        });
    </script>

    <?php
}
