<?php
/**
 * ==========================================================
 * MENÚ ADMINISTRATIVO: Reservas
 * ==========================================================
 * Unifica todos los submenús bajo un solo menú principal,
 * incluyendo los accesos a las pantallas nativas del CPT.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_admin_menu_reservas() {

    // -------------------------------
    // 📅 Menú principal "Reservas"
    // -------------------------------
    add_menu_page(
        __('Reservas', 'tureserva'),
        __('Reservas', 'tureserva'),
        'manage_options',
        'edit.php?post_type=reserva', // usa la pantalla del CPT
        '',
        'dashicons-calendar-alt',
        6
    );

    // -------------------------------
    // 📋 Submenús del CPT
    // -------------------------------

    // Todas las reservas
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Todas las reservas', 'tureserva'),
        __('Todas las reservas', 'tureserva'),
        'manage_options',
        'edit.php?post_type=reserva'
    );

    // Añadir nueva
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Añadir nueva reserva', 'tureserva'),
        __('Añadir nueva reserva', 'tureserva'),
        'manage_options',
        'post-new.php?post_type=reserva'
    );

    // Historial de pagos
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Historial de pagos', 'tureserva'),
        __('Historial de pagos', 'tureserva'),
        'manage_options',
        'tureserva-historial-pagos',
        'tureserva_historial_pagos_page'
    );

    // Calendario
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Calendario de reservas', 'tureserva'),
        __('Calendario', 'tureserva'),
        'manage_options',
        'tureserva-calendario',
        'tureserva_calendario_page'
    );

    // Clientes
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Clientes', 'tureserva'),
        __('Clientes', 'tureserva'),
        'manage_options',
        'tureserva-clientes',
        'tureserva_clientes_page'
    );

    // Cupones
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Cupones de descuento', 'tureserva'),
        __('Cupones', 'tureserva'),
        'manage_options',
        'tureserva-cupones',
        'tureserva_cupones_page'
    );

    // Reglas de reserva
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Reglas de reserva', 'tureserva'),
        __('Reglas de reserva', 'tureserva'),
        'manage_options',
        'tureserva-reglas',
        'tureserva_reglas_page'
    );

    // Impuestos y cargos
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Impuestos y cargos', 'tureserva'),
        __('Impuestos y cargos', 'tureserva'),
        'manage_options',
        'tureserva-impuestos',
        'tureserva_impuestos_page'
    );

    // Sincronizar calendarios
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Sincronización de Calendarios', 'tureserva'),
        __('Sincronizar calendarios', 'tureserva'),
        'manage_options',
        'tureserva-calendar-sync',
        'tureserva_calendar_sync_page'
    );

    // Cloud Sync (Supabase)
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Cloud Sync (Supabase)', 'tureserva'),
        __('Cloud Sync (Supabase)', 'tureserva'),
        'manage_options',
        'tureserva-cloud-sync',
        'tureserva_cloud_sync_page'
    );

    // Informes
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Informes y estadísticas', 'tureserva'),
        __('Informes', 'tureserva'),
        'manage_options',
        'tureserva-informes',
        'tureserva_informes_page'
    );

    // Extensiones
    add_submenu_page(
        'edit.php?post_type=reserva',
        __('Extensiones del sistema', 'tureserva'),
        __('Extensiones', 'tureserva'),
        'manage_options',
        'tureserva-extensiones',
        'tureserva_extensiones_page'
    );
}
add_action('admin_menu', 'tureserva_admin_menu_reservas', 9);
