<?php
/**
 * ==========================================================
 * ADMIN PAGE — Sincronización de Calendarios (iCal)
 * ==========================================================
 * Permite importar y exportar calendarios desde plataformas
 * como Airbnb, Booking.com y Google Calendar.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🖥️ PÁGINA PRINCIPAL DE SINCRONIZACIÓN DE CALENDARIOS
// =======================================================
function tureserva_calendar_sync_page() {
    global $wpdb;

    $table = $wpdb->prefix . 'tureserva_sync_log';
    $registros = $wpdb->get_results("SELECT * FROM $table ORDER BY fecha DESC LIMIT 50");
    ?>
    <div class="wrap">
        <h1><?php _e('Sincronización de Calendarios (iCal)', 'tureserva'); ?></h1>
        <p><?php _e('Administre la conexión con calendarios externos como Airbnb, Booking.com o Google Calendar. Puede ejecutar sincronizaciones manuales o revisar el historial de ejecución.', 'tureserva'); ?></p>

        <div style="margin-bottom:20px;">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="tureserva_sync_all_calendars">
                <button type="submit" class="button button-primary">
                    <?php _e('🔄 Sincronizar todos los calendarios externos', 'tureserva'); ?>
                </button>
            </form>
            <a href="<?php echo admin_url('admin-post.php?action=tureserva_clear_sync_logs'); ?>" class="button button-secondary" style="margin-top:5px;">
                <?php _e('🗑️ Limpiar registros', 'tureserva'); ?>
            </a>
        </div>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Alojamiento', 'tureserva'); ?></th>
                    <th><?php _e('Estado', 'tureserva'); ?></th>
                    <th><?php _e('Total', 'tureserva'); ?></th>
                    <th><?php _e('Exitoso', 'tureserva'); ?></th>
                    <th><?php _e('Erróneo', 'tureserva'); ?></th>
                    <th><?php _e('Fecha', 'tureserva'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registros)) : ?>
                    <?php foreach ($registros as $r) : ?>
                        <tr>
                            <td><?php echo esc_html($r->alojamiento); ?></td>
                            <td><?php echo esc_html($r->estado); ?></td>
                            <td><?php echo intval($r->total); ?></td>
                            <td><?php echo intval($r->exitoso); ?></td>
                            <td><?php echo intval($r->erroneo); ?></td>
                            <td><?php echo esc_html($r->fecha); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="6"><?php _e('No se han encontrado registros de sincronización.', 'tureserva'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
