<?php
/**
 * ==========================================================
 * CRON — Sincronización Automática de Calendarios
 * ==========================================================
 * Permite ejecutar la sincronización según un intervalo elegido por el usuario.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// ⚙️ Registrar intervalos personalizados
// =======================================================
add_filter('cron_schedules', function ($schedules) {
    $schedules['tureserva_15min'] = ['interval' => 900,  'display' => __('Cada 15 minutos', 'tureserva')];
    $schedules['tureserva_30min'] = ['interval' => 1800, 'display' => __('Cada 30 minutos', 'tureserva')];
    $schedules['tureserva_1h']    = ['interval' => 3600, 'display' => __('Cada hora', 'tureserva')];
    $schedules['tureserva_3h']    = ['interval' => 10800, 'display' => __('Cada 3 horas', 'tureserva')];
    $schedules['tureserva_6h']    = ['interval' => 21600, 'display' => __('Cada 6 horas', 'tureserva')];
    $schedules['tureserva_12h']   = ['interval' => 43200, 'display' => __('Cada 12 horas', 'tureserva')];
    $schedules['tureserva_24h']   = ['interval' => 86400, 'display' => __('Cada 24 horas', 'tureserva')];
    return $schedules;
});

// =======================================================
// 🕒 Activar cron dinámico al guardar configuración
// =======================================================
function tureserva_schedule_cron_event($interval) {
    wp_clear_scheduled_hook('tureserva_cron_sync_calendars');
    if ($interval && $interval !== 'none') {
        wp_schedule_event(time(), $interval, 'tureserva_cron_sync_calendars');
    }
}

// =======================================================
// 🔁 Acción programada
// =======================================================
add_action('tureserva_cron_sync_calendars', 'tureserva_run_auto_sync');

function tureserva_run_auto_sync() {
    // Simplemente llama al handler de sincronización existente
    if (function_exists('tureserva_sync_all_calendars')) {
        tureserva_sync_all_calendars();
        update_option('tureserva_last_sync', current_time('mysql'));
    }
}
