<?php
/**
 * ==========================================================
 * ADMIN PAGE — Sincronización de Calendarios
 * ==========================================================
 * Muestra el estado general de las sincronizaciones iCal
 * para cada alojamiento y permite ejecutar acciones globales.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 Registrar submenú dentro de "Reservas"
// =======================================================
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Sincronización de Calendarios', 'tureserva'),
        __('Sincronización de Calendarios', 'tureserva'),
        'manage_options',
        'tureserva-calendar-sync',
        'tureserva_calendar_sync_page'
    );
});

// =======================================================
// 🖥️ Render de la página principal
// =======================================================
function tureserva_calendar_sync_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';
    $registros = $wpdb->get_results("SELECT * FROM $table ORDER BY fecha DESC LIMIT 50");
    ?>
    <div class="wrap">
        <h1><?php _e('Estado de la sincronización de calendarios', 'tureserva'); ?></h1>
        <p><?php _e('Aquí puede ver el estado de la sincronización de sus calendarios externos.', 'tureserva'); ?></p>

        <div style="margin-bottom:15px;">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                <input type="hidden" name="action" value="tureserva_sync_all_calendars">
                <button type="submit" class="button button-primary">
                    <?php _e('Sincronizar todos los calendarios externos', 'tureserva'); ?>
                </button>
            </form>

            <button class="button" disabled><?php _e('Abortar Proceso', 'tureserva'); ?></button>

            <a href="<?php echo admin_url('admin-post.php?action=tureserva_clear_sync_logs'); ?>" class="button button-secondary">
                <?php _e('Eliminar todos los registros', 'tureserva'); ?>
            </a>
        </div>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Alojamiento', 'tureserva'); ?></th>
                    <th><?php _e('Estado', 'tureserva'); ?></th>
                    <th><?php _e('Total', 'tureserva'); ?></th>
                    <th><?php _e('Exitoso', 'tureserva'); ?></th>
                    <th><?php _e('Omitidos', 'tureserva'); ?></th>
                    <th><?php _e('Erróneo', 'tureserva'); ?></th>
                    <th><?php _e('Eliminado', 'tureserva'); ?></th>
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
                            <td><?php echo intval($r->omitidos); ?></td>
                            <td><?php echo intval($r->erroneo); ?></td>
                            <td><?php echo intval($r->eliminado); ?></td>
                            <td><?php echo esc_html($r->fecha); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8"><?php _e('No se han encontrado elementos.', 'tureserva'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php 
        // ✅ Mostrar panel de sincronización automática al final
        tureserva_calendar_sync_settings(); 
        ?>
    </div>
    <?php
}

// =======================================================
// ⚙️ Panel de configuración automática (CRON)
// =======================================================
function tureserva_calendar_sync_settings() {
    $interval = get_option('tureserva_cron_interval', 'none');
    $last_sync = get_option('tureserva_last_sync', '—');
    $next = wp_next_scheduled('tureserva_cron_sync_calendars');
    $next_sync = $next ? date_i18n('Y-m-d H:i:s', $next) : '—';
    ?>
    <hr style="margin:30px 0;">
    <h2><?php _e('Sincronización Automática', 'tureserva'); ?></h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('tureserva_save_cron_settings'); ?>
        <input type="hidden" name="action" value="tureserva_save_cron_settings">

        <p><?php _e('Defina la frecuencia de sincronización automática de sus calendarios externos.', 'tureserva'); ?></p>

        <select name="tureserva_cron_interval">
            <option value="none" <?php selected($interval, 'none'); ?>><?php _e('Desactivado', 'tureserva'); ?></option>
            <option value="tureserva_15min" <?php selected($interval, 'tureserva_15min'); ?>><?php _e('Cada 15 minutos', 'tureserva'); ?></option>
            <option value="tureserva_30min" <?php selected($interval, 'tureserva_30min'); ?>><?php _e('Cada 30 minutos', 'tureserva'); ?></option>
            <option value="tureserva_1h" <?php selected($interval, 'tureserva_1h'); ?>><?php _e('Cada hora', 'tureserva'); ?></option>
            <option value="tureserva_3h" <?php selected($interval, 'tureserva_3h'); ?>><?php _e('Cada 3 horas', 'tureserva'); ?></option>
            <option value="tureserva_6h" <?php selected($interval, 'tureserva_6h'); ?>><?php _e('Cada 6 horas', 'tureserva'); ?></option>
            <option value="tureserva_12h" <?php selected($interval, 'tureserva_12h'); ?>><?php _e('Cada 12 horas', 'tureserva'); ?></option>
            <option value="tureserva_24h" <?php selected($interval, 'tureserva_24h'); ?>><?php _e('Cada 24 horas', 'tureserva'); ?></option>
        </select>

        <p>
            <button type="submit" class="button button-primary"><?php _e('Guardar configuración', 'tureserva'); ?></button>
        </p>

        <p>
            <strong><?php _e('Última sincronización:', 'tureserva'); ?></strong> <?php echo esc_html($last_sync); ?><br>
            <strong><?php _e('Próxima ejecución:', 'tureserva'); ?></strong> <?php echo esc_html($next_sync); ?>
        </p>
    </form>
    <?php
}

// =======================================================
// 🧩 Guardar configuración automática (CRON)
// =======================================================
add_action('admin_post_tureserva_save_cron_settings', function () {
    if (!current_user_can('manage_options') || !check_admin_referer('tureserva_save_cron_settings')) return;

    $interval = sanitize_text_field($_POST['tureserva_cron_interval']);
    update_option('tureserva_cron_interval', $interval);
    if (function_exists('tureserva_schedule_cron_event')) {
        tureserva_schedule_cron_event($interval);
    }

    wp_redirect(admin_url('edit.php?post_type=reserva&page=tureserva-calendar-sync&updated=1'));
    exit;
});
