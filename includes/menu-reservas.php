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
        function() {
            require_once TURESERVA_PATH . 'admin/reservas/add-new.php';
        }
    );
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Calendario de reservas', 'tureserva'),
        __('Calendario', 'tureserva'),
        'manage_options',
        'tureserva_calendario',
        'tureserva_vista_calendario'
    );

    // =======================================================
    // 🔔 Notificaciones
    // =======================================================
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        'Notificaciones',
        'Notificaciones',
        'manage_options',
        'tureserva_notificaciones',
        'tureserva_panel_notificaciones'
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
        'edit.php?post_type=tureserva_cupon'
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
    
    // Aseguramos cargar archivo
    require_once TURESERVA_PATH . 'admin/pages/reglas.php';

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
    
    // Aseguramos cargar archivo
    require_once TURESERVA_PATH . 'admin/pages/impuestos.php';

    // =======================================================
    // 🔄 Sincronización de calendarios
    // =======================================================
    require_once TURESERVA_PATH . 'admin/pages/calendarios.php';
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        __('Sincronizar calendarios', 'tureserva'),
        __('Sincronizar (iCal)', 'tureserva'),
        'manage_options',
        'tureserva-calendarios',
        'trs_ical_admin_render'
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

    // Aseguramos cargar archivo
    require_once TURESERVA_PATH . 'admin/pages/clientes.php';









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
