<?php
/**
 * ==========================================================
 * MENÚ ADMINISTRATIVO — Sincronización
 * ==========================================================
 * Crea las entradas principales de sincronización
 * dentro del menú "Reservas":
 * - Sincronizar calendarios
 * - Cloud Sync (Supabase)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 SUBMENÚ: Sincronizar calendarios
// =======================================================
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=reserva',                   // Menú padre: Reservas
        __('Sincronización de Calendarios', 'tureserva'),
        __('Sincronizar calendarios', 'tureserva'),
        'manage_options',
        'tureserva-calendar-sync',                      // Slug único
        'tureserva_calendar_sync_page',                 // Callback de render
        20
    );
});

// =======================================================
// 🔧 SUBMENÚ: Cloud Sync (Supabase)
// =======================================================
add_action('admin_menu', 'tureserva_register_cloudsync_submenu', 25);

function tureserva_register_cloudsync_submenu() {
    add_submenu_page(
        'edit.php?post_type=reserva',                   // Menú padre
        __('Cloud Sync (Supabase)', 'tureserva'),       // Título de página
        __('Cloud Sync (Supabase)', 'tureserva'),       // Texto visible en menú
        'manage_options',                               // Permisos
        'tureserva-cloud-sync',                         // Slug único
        'tureserva_cloud_sync_page'                     // Callback de render
    );
}