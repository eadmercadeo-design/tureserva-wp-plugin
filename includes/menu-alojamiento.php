<?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamientos — TuReserva (VERSIÓN CORREGIDA)
 * ==========================================================
 * Este archivo crea el menú principal "Alojamientos" y todos 
 * sus submenús. El CPT corre con el nombre válido:
 *
 *      trs_alojamiento
 *
 * (Antes era tureserva_alojamiento — 21 caracteres → inválido)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🧭 REGISTRO DEL MENÚ PRINCIPAL "ALOJAMIENTOS"
// ==========================================================
add_action('admin_menu', 'tureserva_admin_menu_alojamiento', 15);

function tureserva_admin_menu_alojamiento()
{
    // ======================================================
    // ✔ Verificar existencia del CPT antes de registrar menú
    // ======================================================
    if (!post_type_exists('trs_alojamiento')) return;

    // ======================================================
    // 🏨 Menú principal "Alojamientos"
    // ======================================================
    add_menu_page(
        __('Alojamientos', 'tureserva'),
        __('Alojamientos', 'tureserva'),
        'manage_options',
        'edit.php?post_type=trs_alojamiento',   // ← CORREGIDO
        '',
        'dashicons-building',
        5
    );

    // ======================================================
    // 📋 Submenú: Todos los alojamientos
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',   // ← CORREGIDO
        __('Todos los alojamientos', 'tureserva'),
        __('Todos los alojamientos', 'tureserva'),
        'manage_options',
        'edit.php?post_type=trs_alojamiento'    // ← CORREGIDO
    );

    // ======================================================
    // ➕ Submenú: Agregar nuevo
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',    // ← CORREGIDO
        __('Agregar nuevo', 'tureserva'),
        __('Agregar nuevo', 'tureserva'),
        'manage_options',
        'post-new.php?post_type=trs_alojamiento' // ← CORREGIDO
    );

    // ======================================================
    // ⚙️ Generar Alojamientos (pantalla personalizada)
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',    // ← CORREGIDO
        __('Generar alojamientos', 'tureserva'),
        __('Generar alojamientos', 'tureserva'),
        'manage_options',
        'tureserva-generar-alojamientos',
        'tureserva_render_generar_alojamientos_page'
    );

    // ======================================================
    // 🔧 Ajustes del módulo Alojamiento
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',    // ← CORREGIDO
        __('Ajustes de Alojamiento', 'tureserva'),
        __('Ajustes', 'tureserva'),
        'manage_options',
        'tureserva-ajustes-alojamiento',
        'tureserva_render_ajustes_alojamiento_page'
    );
}
