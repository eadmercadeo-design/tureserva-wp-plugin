<?php
/**
 * ==========================================================
 * DASHBOARD PRINCIPAL — TuReserva
 * ==========================================================
 * Este archivo centraliza los widgets del escritorio
 * (panel de estadísticas, ingresos, llegadas/salidas, etc.)
 *
 * Cada widget está separado en archivos individuales
 * dentro de /admin/dashboard/widgets/
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔧 REGISTRO DE WIDGETS PERSONALIZADOS EN EL DASHBOARD
// ==========================================================
add_action('wp_dashboard_setup', 'tureserva_register_dashboard_widgets');
function tureserva_register_dashboard_widgets() {

    // Widget: Ocupación comparativa anual
    wp_add_dashboard_widget(
        'tureserva_widget_ocupacion',
        __('Estadísticas de Ocupación', 'tureserva'),
        'tureserva_widget_ocupacion_render'
    );

    // Widget: Ingresos del mes
    wp_add_dashboard_widget(
        'tureserva_widget_ingresos',
        __('Ingresos del Mes', 'tureserva'),
        'tureserva_widget_ingresos_render'
    );

    // Widget: Próximas llegadas y salidas
    wp_add_dashboard_widget(
        'tureserva_widget_llegadas_salidas',
        __('Próximas llegadas y salidas', 'tureserva'),
        'tureserva_widget_llegadas_salidas_render'
    );

    // Widget: Estado de sincronización Cloud
    wp_add_dashboard_widget(
        'tureserva_widget_cloud_sync',
        __('Sincronización Cloud', 'tureserva'),
        'tureserva_widget_cloud_sync_render'
    );
}

// ==========================================================
// 🧱 INCLUSIÓN DE ARCHIVOS DE WIDGETS
// ==========================================================
// Cada archivo contiene su propia función *_render()
// para mantener el código modular y escalable.
require_once __DIR__ . '/widgets/ocupacion.php';
require_once __DIR__ . '/widgets/ingresos.php';
require_once __DIR__ . '/widgets/llegadas-salidas.php';
require_once __DIR__ . '/widgets/cloud-sync.php';

// ==========================================================
// 🧩 NOTA DE EXTENSIÓN
// ==========================================================
// Si deseas agregar más widgets, simplemente crea un nuevo
// archivo PHP dentro de /widgets/ y añádelo aquí con:
// require_once __DIR__ . '/widgets/nombre-widget.php';

