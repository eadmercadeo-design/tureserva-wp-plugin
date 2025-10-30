<?php
/**
 * ==========================================================
 * ADMIN MENU: Reservas — Sistema TuReserva
 * ==========================================================
 * Crea el menú principal “Reservas” y organiza todos los submenús:
 * - Todas las reservas (CPT)
 * - Añadir nueva reserva (manual)
 * - Historia de pagos
 * - Calendario
 * - Clientes
 * - Cupones
 * - Reglas de reserva
 * - Impuestos y cargos
 * - Sincronizar calendarios
 * - Informes
 * - Extensiones
 *
 * Este archivo reemplaza cualquier versión anterior duplicada.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 MENÚ PRINCIPAL Y SUBMENÚS
// =======================================================
if ( ! function_exists( 'tureserva_admin_menu_nueva_reserva' ) ) {

    function tureserva_admin_menu_nueva_reserva() {

        // -------------------------------
        // 📅 Menú principal “Reservas”
        // -------------------------------
        add_menu_page(
            __( 'Reservas', 'tureserva' ),
            __( 'Reservas', 'tureserva' ),
            'manage_options',
            'tureserva_reservas_panel',
            'tureserva_reservas_dashboard_page',
            'dashicons-calendar-alt',
            6 // Posición junto a “Alojamiento”
        );

        // -------------------------------
        // 📋 Submenú: Todas las reservas (CPT)
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Todas las reservas', 'tureserva' ),
            __( 'Todas las reservas', 'tureserva' ),
            'manage_options',
            'edit.php?post_type=tureserva_reservas'
        );

        // -------------------------------
        // ➕ Submenú: Agregar nueva reserva (manual)
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Añadir nueva reserva', 'tureserva' ),
            __( 'Añadir nueva reserva', 'tureserva' ),
            'manage_options',
            'tureserva_nueva_reserva',
            'tureserva_nueva_reserva_page'
        );

        // -------------------------------
        // 💳 Submenú: Historia de pagos
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Historia de pagos', 'tureserva' ),
            __( 'Historia de pagos', 'tureserva' ),
            'manage_options',
            'tureserva_historial_pagos',
            'tureserva_historial_pagos_page'
        );

        // -------------------------------
        // 📆 Submenú: Calendario
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Calendario', 'tureserva' ),
            __( 'Calendario', 'tureserva' ),
            'manage_options',
            'tureserva_calendario',
            'tureserva_calendario_page'
        );

        // -------------------------------
        // 👥 Submenú: Clientes
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Clientes', 'tureserva' ),
            __( 'Clientes', 'tureserva' ),
            'manage_options',
            'tureserva_clientes',
            'tureserva_clientes_page'
        );

        // -------------------------------
        // 🎟️ Submenú: Cupones
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Cupones', 'tureserva' ),
            __( 'Cupones', 'tureserva' ),
            'manage_options',
            'tureserva_cupones',
            'tureserva_cupones_page'
        );

        // -------------------------------
        // ⚙️ Submenú: Reglas de reserva
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Reglas de reserva', 'tureserva' ),
            __( 'Reglas de reserva', 'tureserva' ),
            'manage_options',
            'tureserva_reglas_reserva',
            'tureserva_reglas_reserva_page'
        );

        // -------------------------------
        // 💰 Submenú: Impuestos y cargos
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Impuestos y cargos', 'tureserva' ),
            __( 'Impuestos y cargos', 'tureserva' ),
            'manage_options',
            'tureserva_impuestos',
            'tureserva_impuestos_page'
        );

        // -------------------------------
        // 🔄 Submenú: Sincronizar calendarios
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Sincronizar calendarios', 'tureserva' ),
            __( 'Sincronizar calendarios', 'tureserva' ),
            'manage_options',
            'tureserva_sync',
            'tureserva_sync_page'
        );

        // -------------------------------
        // 📊 Submenú: Informes
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Informes', 'tureserva' ),
            __( 'Informes', 'tureserva' ),
            'manage_options',
            'tureserva_reportes',
            'tureserva_reportes_page'
        );

        // -------------------------------
        // 🧩 Submenú: Extensiones
        // -------------------------------
        add_submenu_page(
            'tureserva_reservas_panel',
            __( 'Extensiones', 'tureserva' ),
            __( 'Extensiones', 'tureserva' ),
            'manage_options',
            'tureserva_extensiones',
            'tureserva_extensiones_page'
        );
    }

    add_action( 'admin_menu', 'tureserva_admin_menu_nueva_reserva' );
}

//
// =======================================================
// 📄 PÁGINAS PLACEHOLDER — Temporales
// =======================================================
// Estas páginas se pueden reemplazar por plantillas reales
// (cuando se activen sus módulos correspondientes).
//

function tureserva_reservas_dashboard_page() {
    echo '<div class="wrap"><h1>📅 ' . __( 'Panel de Reservas', 'tureserva' ) . '</h1>';
    echo '<p>Desde aquí puedes gestionar las reservas, pagos y sincronización.</p></div>';
}

function tureserva_nueva_reserva_page() {
    echo '<div class="wrap"><h1>➕ ' . __( 'Añadir nueva reserva', 'tureserva' ) . '</h1>';
    echo '<p>Formulario para registrar manualmente una nueva reserva.</p></div>';
}

function tureserva_historial_pagos_page() {
    echo '<div class="wrap"><h1>💳 ' . __( 'Historia de pagos', 'tureserva' ) . '</h1>';
    echo '<p>Aquí podrás revisar los pagos realizados, pendientes o cancelados.</p>';
    echo '<p>En futuras versiones se integrará con pasarelas como Stripe o PayU.</p></div>';
}

function tureserva_calendario_page() {
    echo '<div class="wrap"><h1>📆 ' . __( 'Calendario de Reservas', 'tureserva' ) . '</h1>';
    echo '<p>Vista global de ocupación, check-ins y check-outs.</p></div>';
}

function tureserva_clientes_page() {
    echo '<div class="wrap"><h1>👥 ' . __( 'Clientes', 'tureserva' ) . '</h1>';
    echo '<p>Listado general de clientes con historial de reservas.</p></div>';
}

function tureserva_cupones_page() {
    echo '<div class="wrap"><h1>🎟️ ' . __( 'Cupones de descuento', 'tureserva' ) . '</h1>';
    echo '<p>Administra cupones y promociones aplicables a reservas.</p></div>';
}

function tureserva_reglas_reserva_page() {
    echo '<div class="wrap"><h1>⚙️ ' . __( 'Reglas de reserva', 'tureserva' ) . '</h1>';
    echo '<p>Define políticas de estancia mínima, horarios de check-in/out y más.</p></div>';
}

function tureserva_impuestos_page() {
    echo '<div class="wrap"><h1>💰 ' . __( 'Impuestos y cargos', 'tureserva' ) . '</h1>';
    echo '<p>Gestiona cargos adicionales, tasas e impuestos aplicados.</p></div>';
}

function tureserva_sync_page() {
    echo '<div class="wrap"><h1>🔄 ' . __( 'Sincronizar calendarios', 'tureserva' ) . '</h1>';
    echo '<p>Sincroniza con plataformas externas como Airbnb, Booking o Supabase.</p></div>';
}

function tureserva_reportes_page() {
    echo '<div class="wrap"><h1>📊 ' . __( 'Informes y estadísticas', 'tureserva' ) . '</h1>';
    echo '<p>Consulta métricas de desempeño, ingresos y ocupación.</p></div>';
}

function tureserva_extensiones_page() {
    echo '<div class="wrap"><h1>🧩 ' . __( 'Extensiones', 'tureserva' ) . '</h1>';
    echo '<p>Instala o activa módulos adicionales del ecosistema TuReserva.</p></div>';
}
