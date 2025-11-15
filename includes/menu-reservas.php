<?php
/**
 * ==========================================================
 * MENÚ ADMINISTRATIVO: Reservas (versión corregida y optimizada)
 * ==========================================================
 * Este archivo unifica todas las pantallas del módulo de
 * reservas bajo un único menú principal.
 *
 * ✔ Uso correcto del CPT: tureserva_reserva (singular)
 * ✔ Submenús organizados y sin duplicados
 * ✔ Rutas corregidas
 * ✔ Código limpio y mantenible
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔗 Registrar el menú en WordPress
// =======================================================
add_action('admin_menu', 'tureserva_admin_menu_reservas', 20);

function tureserva_admin_menu_reservas()
{
    // ------------------------------------------------------------------
    // ✔ Verificamos que el CPT exista antes de intentar crear el menú
    // ------------------------------------------------------------------
    if (!post_type_exists('tureserva_reserva')) return;

    // =======================================================
    // 📅 MENÚ PRINCIPAL "Reservas"
    // =======================================================
    add_menu_page(
        __('Reservas', 'tureserva'),               // Título
        __('Reservas', 'tureserva'),               // Etiqueta menú
        'manage_options',                          // Permisos
        'edit.php?post_type=tureserva_reserva',    // URL del CPT
        '',                                         // Callback (WP por defecto)
        'dashicons-calendar-alt',                  // Icono
        6                                           // Posición
    );

    // =======================================================
    // 📋 Submenú: Todas las reservas
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Todas las reservas', 'tureserva'),
        __('Todas las reservas', 'tureserva'),
        'manage_options',
        'edit.php?post_type=tureserva_reserva'
    );

    // =======================================================
    // ➕ Submenú: Añadir nueva reserva
    // Interfaz personalizada reemplaza pantalla nativa
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Añadir nueva', 'tureserva'),
        __('Añadir nueva', 'tureserva'),
        'manage_options',
        'tureserva-add-reserva',
        function () {
            require_once TURESERVA_PATH . 'admin/reservas/add-new.php';
        }
    );

    // =======================================================
    // 💳 Historial de pagos
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Historial de pagos', 'tureserva'),
        __('Historial de pagos', 'tureserva'),
        'manage_options',
        'tureserva-historial-pagos',
        'tureserva_historial_pagos_page_render'
    );

    // Aseguramos cargar archivo
    require_once TURESERVA_PATH . 'admin/pages/historial-pagos.php';

    // =======================================================
    // 📆 Calendario de reservas
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Calendario de reservas', 'tureserva'),
        __('Calendario', 'tureserva'),
        'manage_options',
        'tureserva_calendario',
        'tureserva_vista_calendario'
    );

    // =======================================================
    // 👥 Clientes
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Clientes', 'tureserva'),
        __('Clientes', 'tureserva'),
        'manage_options',
        'tureserva-clientes',
        'tureserva_clientes_page_render'
    );

    // =======================================================
    // 💸 Cupones
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Cupones de descuento', 'tureserva'),
        __('Cupones', 'tureserva'),
        'manage_options',
        'tureserva-cupones',
        'tureserva_cupones_page_render'
    );

    // =======================================================
    // 📏 Reglas de reserva
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Reglas de reserva', 'tureserva'),
        __('Reglas de reserva', 'tureserva'),
        'manage_options',
        'tureserva-reglas',
        'tureserva_reglas_page_render'
    );

    // =======================================================
    // 💰 Impuestos y cargos
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Impuestos y cargos', 'tureserva'),
        __('Impuestos y cargos', 'tureserva'),
        'manage_options',
        'tureserva-impuestos',
        'tureserva_impuestos_page_render'
    );

    // =======================================================
    // 🔄 Sincronización de calendarios
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Sincronización de calendarios', 'tureserva'),
        __('Sincronizar calendarios', 'tureserva'),
        'manage_options',
        'tureserva-calendar-sync',
        'tureserva_calendar_sync_page_render'
    );

    // =======================================================
    // 📊 Informes
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Informes y estadísticas', 'tureserva'),
        __('Informes', 'tureserva'),
        'manage_options',
        'tureserva-informes',
        'tureserva_informes_page_render'
    );

    // =======================================================
    // 🔌 Extensiones
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Extensiones del sistema', 'tureserva'),
        __('Extensiones', 'tureserva'),
        'manage_options',
        'tureserva-extensiones',
        'tureserva_extensiones_page_render'
    );
} // FIN DE LA FUNCIÓN PRINCIPAL


// =======================================================
// 🧩 CALLBACKS — Placeholders
// =======================================================
// (Estos están correctos; solo los documento mejor)

function tureserva_clientes_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Clientes', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de clientes del sistema de reservas.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_cupones_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Cupones de Descuento', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de cupones y códigos promocionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_reglas_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Reglas de Reserva', 'tureserva'); ?></h1>
        <p><?php _e('Configuración de reglas y políticas de reserva.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_impuestos_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Impuestos y Cargos', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de impuestos y cargos adicionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_calendar_sync_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Sincronización de Calendarios', 'tureserva'); ?></h1>
        <p><?php _e('Configuración de sincronización con calendarios externos.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_informes_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Informes y Estadísticas', 'tureserva'); ?></h1>
        <p><?php _e('Visualización de informes del sistema de reservas.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}

function tureserva_extensiones_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Extensiones del Sistema', 'tureserva'); ?></h1>
        <p><?php _e('Gestión de extensiones adicionales.', 'tureserva'); ?></p>
        <p><em><?php _e('Esta funcionalidad está en desarrollo.', 'tureserva'); ?></em></p>
    </div>
    <?php
}
