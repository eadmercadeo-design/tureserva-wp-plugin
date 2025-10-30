<?php
/**
 * ==========================================================
 * ADMIN MENU: Reservas — TuReserva
 * ==========================================================
 * Menú principal del sistema de reservas.
 * Incluye accesos a pagos, reportes, calendario y más.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📋 MENÚ PRINCIPAL "RESERVAS"
// =======================================================
function tureserva_admin_menu_reservas() {

    // Menú principal
    add_menu_page(
        __( 'Reservas', 'tureserva' ),
        __( 'Reservas', 'tureserva' ),
        'manage_options',
        'tureserva_menu_reservas',
        'tureserva_reservas_dashboard',
        'dashicons-calendar-alt',
        6 // posición para que aparezca junto a "Alojamiento"
    );

    // Submenús
    add_submenu_page( 'tureserva_menu_reservas', 'Todas las reservas', 'Todas las reservas', 'manage_options', 'edit.php?post_type=tureserva_reservas' );
    add_submenu_page( 'tureserva_menu_reservas', 'Añadir nueva reserva', 'Añadir nueva reserva', 'manage_options', 'post-new.php?post_type=tureserva_reservas' );
    add_submenu_page( 'tureserva_menu_reservas', 'Todos los pagos', 'Todos los pagos', 'manage_options', 'edit.php?post_type=tureserva_pagos' );
    add_submenu_page( 'tureserva_menu_reservas', 'Notificaciones', 'Notificaciones', 'manage_options', 'tureserva_notificaciones', 'tureserva_notificaciones_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Calendario', 'Calendario', 'manage_options', 'tureserva_calendario', 'tureserva_calendario_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Reportes', 'Reportes', 'manage_options', 'tureserva_reportes', 'tureserva_reportes_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Ajustes', 'Ajustes', 'manage_options', 'tureserva_ajustes', 'tureserva_ajustes_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'API Tokens', 'API Tokens', 'manage_options', 'tureserva_tokens', 'tureserva_tokens_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Sincronización Cloud', 'Sincronización Cloud', 'manage_options', 'tureserva_sync', 'tureserva_sync_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Frecuencia de Sincronización', 'Frecuencia de Sincronización', 'manage_options', 'tureserva_cron', 'tureserva_cron_page' );
    add_submenu_page( 'tureserva_menu_reservas', 'Configuración de Pagos', 'Configuración de Pagos', 'manage_options', 'tureserva_payments', 'tureserva_payments_page' );
}
add_action( 'admin_menu', 'tureserva_admin_menu_reservas' );

// =======================================================
// 📊 PANTALLA PRINCIPAL DE RESERVAS
// =======================================================
function tureserva_reservas_dashboard() {
    echo '<div class="wrap"><h1>📅 Panel de Reservas</h1><p>Desde aquí puedes acceder a todas las funciones del sistema de reservas: crear nuevas reservas, gestionar pagos, ver reportes y sincronización.</p></div>';
}
