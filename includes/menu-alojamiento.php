<?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamiento — TuReserva
 * ==========================================================
 * Crea el menú principal "Alojamiento" en el panel
 * y delega los submenús a las páginas ubicadas en:
 * /admin/pages/
 * ==========================================================
 <?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamiento — TuReserva
 * ==========================================================
 * Este archivo asegura que los submenús personalizados
 * (como "Generar alojamientos" o "Ajustes") se integren
 * correctamente bajo el menú del CPT "Alojamiento".
 *
 * 🚫 Ya no registra un menú principal duplicado.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 📌 Verificar existencia del CPT antes de cargar submenús
// =======================================================
add_action( 'admin_menu', function() {

    if ( ! post_type_exists( 'alojamiento' ) ) {
        return;
    }

    // ⚙️ Aquí podrías añadir submenús personalizados si fuera necesario
    // Ejemplo:
    // add_submenu_page(
    //     'edit.php?post_type=alojamiento',
    //     __( 'Ejemplo', 'tureserva' ),
    //     __( 'Ejemplo', 'tureserva' ),
    //     'manage_options',
    //     'tureserva-ejemplo',
    //     'tureserva_render_ejemplo_page'
    // );

}, 11 );
