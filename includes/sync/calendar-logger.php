<?php
/**
 * ==========================================================
 * LOGGER — Registros de sincronización
 * ==========================================================
 * Crea la tabla personalizada wp_tureserva_sync_log y gestiona
 * los registros de cada sincronización externa.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;



// =======================================================
// 📥 Insertar registro
// =======================================================
function tureserva_add_sync_log($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';
    $wpdb->insert($table, $data);
}

// =======================================================
// 🧹 Eliminar todos los registros
// =======================================================
add_action('admin_post_tureserva_clear_sync_logs', function () {
    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';
    $wpdb->query("TRUNCATE TABLE $table");
    wp_redirect(admin_url('edit.php?post_type=tureserva_reserva&page=tureserva-calendar-sync'));
    exit;
});
