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
        'edit.php?post_type=tureserva_reservas', // usa la pantalla del CPT
        '',
        'dashicons-calendar-alt',
        6
    );

    // -------------------------------
    // 📋 Submenús del CPT
    // -------------------------------

    // Todas las reservas
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Todas las reservas', 'tureserva'),
        __('Todas las reservas', 'tureserva'),
        'manage_options',
        'edit.php?post_type=tureserva_reservas'
    );

    // Añadir nueva (pantalla personalizada mejorada)
add_submenu_page(
    'edit.php?post_type=tureserva_reservas',
    __('Añadir nueva reserva', 'tureserva'),
    __('Añadir nueva', 'tureserva'),
    'manage_options',
    'tureserva-add-reserva',
    function() {
        require_once TURESERVA_PATH . 'admin/reservas/add-new.php';
    }
);

  // =======================================================
// 💳 Historial de pagos
// =======================================================
require_once TURESERVA_PATH . 'admin/pages/historial-pagos.php';

add_submenu_page(
    'edit.php?post_type=tureserva_reservas',
    __('Historial de pagos', 'tureserva'),
    __('Historial de pagos', 'tureserva'),
    'manage_options',
    'tureserva-historial-pagos',
    'tureserva_historial_pagos_page_render'
);

    // Calendario
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Calendario de reservas', 'tureserva'),
        __('Calendario', 'tureserva'),
        'manage_options',
        'tureserva_calendario',
        'tureserva_vista_calendario'
    );

    // Clientes
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Clientes', 'tureserva'),
        __('Clientes', 'tureserva'),
        'manage_options',
        'tureserva-clientes',
        'tureserva_clientes_page_render'
    );

    // Cupones
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Cupones de descuento', 'tureserva'),
        __('Cupones', 'tureserva'),
        'manage_options',
        'tureserva-cupones',
        'tureserva_cupones_page_render'
    );

    // Reglas de reserva
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Reglas de reserva', 'tureserva'),
        __('Reglas de reserva', 'tureserva'),
        'manage_options',
        'tureserva-reglas',
        'tureserva_reglas_page_render'
    );

    // Impuestos y cargos
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Impuestos y cargos', 'tureserva'),
        __('Impuestos y cargos', 'tureserva'),
        'manage_options',
        'tureserva-impuestos',
        'tureserva_impuestos_page_render'
    );

    // Sincronizar calendarios
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Sincronización de Calendarios', 'tureserva'),
        __('Sincronizar calendarios', 'tureserva'),
        'manage_options',
        'tureserva-calendar-sync',
        'tureserva_calendar_sync_page_render'
    );
    // Informes
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Informes y estadísticas', 'tureserva'),
        __('Informes', 'tureserva'),
        'manage_options',
        'tureserva-informes',
        'tureserva_informes_page_render'
    );

    // Extensiones
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        __('Extensiones del sistema', 'tureserva'),
        __('Extensiones', 'tureserva'),
        'manage_options',
        'tureserva-extensiones',
        'tureserva_extensiones_page_render'
    );

} // ✅ Cierra correctamente la función tureserva_admin_menu_reservas()
/**
 * ==========================================================
 * CALLBACK: Renderizar la página de Cloud Sync (Supabase)
 * ==========================================================
 */
function tureserva_render_supabase_panel() {
    require_once TURESERVA_PATH . 'admin/pages/panel-supabase.php';
}

// =======================================================
// 🔗 Registrar el menú en WordPress
// =======================================================
add_action('admin_menu', 'tureserva_admin_menu_reservas', 9);


// =======================================================
// 📅 Página de Calendario — La función tureserva_vista_calendario()
// está definida en menu-calendario.php y se usa aquí
// =======================================================

// =======================================================
// 📋 CALLBACKS DE PÁGINAS (Placeholders)
// =======================================================

/**
 * Página de Clientes
 */
function tureserva_clientes_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Clientes', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de clientes del sistema de reservas.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Cupones
 */
function tureserva_cupones_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Cupones de Descuento', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de cupones y códigos promocionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Reglas de Reserva
 */
function tureserva_reglas_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Reglas de Reserva', 'tureserva'); ?></h1>
        <p><?php _e('Configuración de reglas y políticas de reserva.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Impuestos y Cargos
 */
function tureserva_impuestos_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Impuestos y Cargos', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de impuestos y cargos adicionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Sincronización de Calendarios
 */
function tureserva_calendar_sync_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Sincronización de Calendarios', 'tureserva'); ?></h1>
        <p><?php _e('Configuración de sincronización con calendarios externos (Google Calendar, iCal, etc.).', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Informes
 */
function tureserva_informes_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Informes y Estadísticas', 'tureserva'); ?></h1>
        <p><?php _e('Visualización de informes y estadísticas del sistema de reservas.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

/**
 * Página de Extensiones
 */
function tureserva_extensiones_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Extensiones del Sistema', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de extensiones y complementos adicionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

