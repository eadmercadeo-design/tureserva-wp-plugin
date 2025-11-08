<?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamiento — TuReserva
 * ==========================================================
 * Registra el menú principal "Alojamiento" en el panel de administración
 * y los submenús relacionados (Generar alojamientos, Ajustes, etc.).
 * 
 * El CPT 'tureserva_alojamiento' está configurado con 'show_in_menu' => false
 * para que WordPress no lo muestre automáticamente, permitiendo un control
 * personalizado del menú.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📌 REGISTRO DEL MENÚ PRINCIPAL "ALOJAMIENTO"
// =======================================================
add_action('admin_menu', 'tureserva_admin_menu_alojamiento', 10);

function tureserva_admin_menu_alojamiento() {

    // Verificar que el CPT esté registrado
    if (!post_type_exists('tureserva_alojamiento')) {
        return;
    }

    // -------------------------------
    // 🏨 Menú principal "Alojamiento"
    // -------------------------------
    add_menu_page(
        __('Alojamientos', 'tureserva'),
        __('Alojamiento', 'tureserva'),
        'manage_options',
        'edit.php?post_type=tureserva_alojamiento',
        '',
        'dashicons-building',
        5 // 📌 Antes del menú "Reservas" (posición 6)
    );

    // -------------------------------
    // 📋 Submenús del CPT
    // -------------------------------

    // Todas los alojamientos
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Todos los alojamientos', 'tureserva'),
        __('Todos los alojamientos', 'tureserva'),
        'manage_options',
        'edit.php?post_type=tureserva_alojamiento'
    );

    // Añadir nuevo
    add_submenu_page(
        'edit.php?post_type=tureserva_alojamiento',
        __('Añadir nuevo alojamiento', 'tureserva'),
        __('Añadir nuevo', 'tureserva'),
        'manage_options',
        'post-new.php?post_type=tureserva_alojamiento'
    );

    // Submenús adicionales (registrados en otros archivos)
    // Ejemplo: menu-generar-alojamientos.php, menu-comodidades.php, etc.
}


// =======================================================
// 📌 Verificar existencia del CPT antes de cargar submenús adicionales
// =======================================================
add_action( 'admin_menu', function() {

    if ( ! post_type_exists( 'tureserva_alojamiento' ) ) {
        return;
    }

    // ⚙️ Aquí se pueden añadir submenús personalizados adicionales
    // Los submenús como "Generar alojamientos" o "Ajustes" se registran
    // en sus respectivos archivos (ej: menu-generar-alojamientos.php)

}, 11 );
