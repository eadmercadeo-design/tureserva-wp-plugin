<?php
/**
 * ==========================================================
 * ADMIN PAGE — Sincronización de Calendarios
 * ==========================================================
 * Permite importar y exportar calendarios iCal (Airbnb, Booking, Google, etc.)
 * y mostrar un resumen visual de la última sincronización.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🖥️ Render de la página principal
// =======================================================
function tureserva_calendar_sync_page() {
    ?>
    <div class="wrap">
        <h1><span style="color:#2271b1;">📅</span> <?php _e('Sincronización de Calendarios', 'tureserva'); ?></h1>
        <p><?php _e('Administra la sincronización de calendarios con plataformas externas como Airbnb, Booking o Google Calendar.', 'tureserva'); ?></p>

        <!-- ===================================================== -->
        <!-- 🧩 SECCIÓN: Sincronización manual -->
        <!-- ===================================================== -->
        <div style="margin-top:30px;background:#fff;padding:25px 30px;border:1px solid #dcdcdc;border-radius:10px;max-width:900px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="margin-top:0;">🕓 <?php _e('Sincronización manual', 'tureserva'); ?></h2>
            <p><?php _e('Haz clic para actualizar manualmente los calendarios de todos los alojamientos conectados.', 'tureserva'); ?></p>

            <button id="tureserva-sync-calendar" class="button button-primary" style="margin-top:10px;">
                🔄 <?php _e('Sincronizar ahora', 'tureserva'); ?>
            </button>

            <div id="tureserva-calendar-progress" style="margin-top:20px;width:100%;max-width:400px;background:#eee;border-radius:6px;height:10px;overflow:hidden;">
                <div style="width:0%;height:10px;background:#2271b1;transition:width .3s;" id="tureserva-calendar-progress-bar"></div>
            </div>
            <p id="tureserva-calendar-status" style="margin-top:10px;font-weight:500;color:#444;"></p>
        </div>

        <!-- ===================================================== -->
        <!-- 🧩 SECCIÓN: Últimas sincronizaciones -->
        <!-- ===================================================== -->
        <div style="margin-top:40px;background:#fff;padding:25px 30px;border:1px solid #dcdcdc;border-radius:10px;max-width:900px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="margin-top:0;">📊 <?php _e('Historial de sincronización', 'tureserva'); ?></h2>
            <p><?php _e('Revisa las últimas sincronizaciones registradas para cada alojamiento.', 'tureserva'); ?></p>

            <table class="widefat fixed striped" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th><?php _e('Alojamiento', 'tureserva'); ?></th>
                        <th><?php _e('Fecha', 'tureserva'); ?></th>
                        <th><?php _e('Fuente', 'tureserva'); ?></th>
                        <th><?php _e('Estado', 'tureserva'); ?></th>
                    </tr>
                </thead>
                <tbody id="tureserva-calendar-log">
                    <tr>
                        <td colspan="4" style="text-align:center;color:#777;">
                            <?php _e('No hay registros disponibles aún.', 'tureserva'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Encolar script JS (simulación visual)
    wp_enqueue_script('tureserva-calendar-sync', TURESERVA_URL . 'assets/js/calendar-sync.js', ['jquery'], TURESERVA_VERSION, true);

    wp_localize_script('tureserva-calendar-sync', 'tureserva_calendar_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_calendar_sync_nonce'),
    ]);
}
