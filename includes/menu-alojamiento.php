<?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamientos — TuReserva
 * ==========================================================
 * Este archivo crea el menú principal "Alojamientos" y todos 
 * sus submenús. El CPT tureserva_alojamiento tiene 
 * show_in_menu => false para permitir control total desde aquí.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit; // Seguridad

// ==========================================================
// 🧭 REGISTRO DEL MENÚ PRINCIPAL "ALOJAMIENTOS"
// ==========================================================
/**
 * NOTA IMPORTANTE:
 * - Antes existían dos registros add_action('admin_menu').
 * - Esto causaba que:
 *    ❌ algunas veces NO apareciera el menú
 *    ❌ se duplicaran entradas
 *    ❌ WordPress ignorara el menú por conflicto de prioridad
 *
 * ✔ Ahora se registra solamente UNA VEZ con prioridad 15.
 */
add_action('admin_menu', 'tureserva_admin_menu_alojamiento', 15);

function tureserva_admin_menu_alojamiento()
{
    // ======================================================
    // ✔ Verificar existencia del CPT antes de registrar menú
    // ======================================================
    /**
     * Si el CPT no ha sido registrado aún (por orden de carga),
     * WordPress no mostrará el menú. Esto evita errores silenciosos.
     */
    if (!post_type_exists('tureserva_alojamiento')) return;

    // ======================================================
    // 🏨 Menú principal "Alojamientos"
    // ======================================================
    add_menu_page(
        __('Alojamientos', 'tureserva'),                 // Título de pantalla
        __('Alojamientos', 'tureserva'),                 // Texto del menú
        'manage_options',                                 // Permisos
        'edit.php?post_type=tureserva_alojamiento',      // Pantalla del CPT
        '',                                               // Callback vacío (WP usa el core)
        'dashicons-building',                             // Ícono
        5                                                 // Posición (antes que "Reservas")
    );

    // ======================================================
    // 📋 Submenú: Todos los alojamientos
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Todos los alojamientos', 'tureserva'),
        __('Todos los alojamientos', 'tureserva'),
        'manage_options',
        'edit.php?post_type=tureserva_alojamiento'
    );

    // ======================================================
    // ➕ Submenú: Agregar nuevo
    // ======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Agregar nuevo', 'tureserva'),
        __('Agregar nuevo', 'tureserva'),
        'manage_options',
        'post-new.php?post_type=tureserva_alojamiento'
    );

    // ======================================================
    // ⚙️ Generar Alojamientos (pantalla personalizada)
    // ======================================================
    /**
     * ✔ Debe existir:
     *   function tureserva_render_generar_alojamientos_page()
     * en /admin/pages/generar-alojamientos.php
     */
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Generar alojamientos', 'tureserva'),
        __('Generar alojamientos', 'tureserva'),
        'manage_options',
        'tureserva-generar-alojamientos',
        'tureserva_render_generar_alojamientos_page'
    );

    // ======================================================
    // 🔧 Ajustes del módulo Alojamiento
    // ======================================================
    /**
     * ✔ Debe existir:
     *   function tureserva_render_ajustes_alojamiento_page()
     * en /admin/pages/ajustes-alojamiento.php
     */
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Ajustes de Alojamiento', 'tureserva'),
        __('Ajustes', 'tureserva'),
        'manage_options',
        'tureserva-ajustes-alojamiento',
        'tureserva_render_ajustes_alojamiento_page'
    );
}
