<?php
/**
 * ==========================================================
 * CORE: Cron y Control de Sincronización — TuReserva
 * ==========================================================
 * Permite definir cada cuánto tiempo se sincroniza con Supabase:
 *  - Cada X minutos
 *  - Cada X horas
 *  - Manualmente bajo demanda
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ OPCIONES DE CONFIGURACIÓN
// =======================================================
add_action( 'tureserva_activated', 'tureserva_cron_default_options' );
function tureserva_cron_default_options() {
    if ( ! get_option( 'tureserva_sync_interval' ) ) {
        update_option( 'tureserva_sync_interval', 'manual' ); // opciones: manual, 5min, 1h, 3h, 6h, 12h, 24h
    }
}

// =======================================================
// 🔁 REGISTRO DE INTERVALOS PERSONALIZADOS
// =======================================================
add_filter( 'cron_schedules', 'tureserva_cron_custom_intervals' );
function tureserva_cron_custom_intervals( $schedules ) {
    $schedules['tureserva_5min']  = array( 'interval' => 300, 'display' => 'Cada 5 minutos' );
    $schedules['tureserva_1h']    = array( 'interval' => 3600, 'display' => 'Cada 1 hora' );
    $schedules['tureserva_3h']    = array( 'interval' => 10800, 'display' => 'Cada 3 horas' );
    $schedules['tureserva_6h']    = array( 'interval' => 21600, 'display' => 'Cada 6 horas' );
    $schedules['tureserva_12h']   = array( 'interval' => 43200, 'display' => 'Cada 12 horas' );
    $schedules['tureserva_24h']   = array( 'interval' => 86400, 'display' => 'Cada 24 horas' );
    return $schedules;
}

// =======================================================
// 🕒 REGISTRO Y CONTROL DEL CRON JOB
// =======================================================
add_action( 'tureserva_sync_cron_event', 'tureserva_cron_execute' );

function tureserva_cron_execute() {
    $ok = tureserva_sync_alojamientos();
    if ( $ok ) {
        update_option( 'tureserva_ultima_sync', current_time( 'mysql' ) );
        error_log('[TuReserva Cron] ✅ Sincronización automática completada.');
    } else {
        error_log('[TuReserva Cron] ❌ Error en la sincronización automática.');
    }
}

// =======================================================
// 🔧 ACTUALIZAR CRON SEGÚN AJUSTE SELECCIONADO
// =======================================================
function tureserva_update_cron_schedule() {
    $intervalo = get_option( 'tureserva_sync_interval', 'manual' );

    // Primero limpiar cron anterior
    $timestamp = wp_next_scheduled( 'tureserva_sync_cron_event' );
    if ( $timestamp ) wp_unschedule_event( $timestamp, 'tureserva_sync_cron_event' );

    if ( $intervalo !== 'manual' ) {
        wp_schedule_event( time() + 60, 'tureserva_' . $intervalo, 'tureserva_sync_cron_event' );
    }
}

// Ejecutar al actualizar ajustes
add_action( 'update_option_tureserva_sync_interval', 'tureserva_update_cron_schedule', 10, 2 );

// =======================================================
// 🔘 SINCRONIZACIÓN MANUAL
// =======================================================
add_action( 'wp_ajax_tureserva_cron_manual', 'tureserva_cron_manual' );
function tureserva_cron_manual() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );

    $ok = tureserva_sync_alojamientos();
    update_option( 'tureserva_ultima_sync', current_time( 'mysql' ) );

    wp_send_json_success( array(
        'mensaje' => $ok ? '✅ Sincronización manual completada.' : '❌ Error de conexión con Supabase.'
    ));
}
