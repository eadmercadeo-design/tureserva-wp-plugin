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

register_activation_hook(__FILE__, 'tureserva_create_sync_table');

function tureserva_create_sync_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'tureserva_sync_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        alojamiento varchar(255) NOT NULL,
        estado varchar(50) DEFAULT '' NOT NULL,
        total int DEFAULT 0,
        exitoso int DEFAULT 0,
        omitidos int DEFAULT 0,
        erroneo int DEFAULT 0,
        eliminado int DEFAULT 0,
        fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

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
    wp_redirect(admin_url('edit.php?post_type=reserva&page=tureserva-calendar-sync'));
    exit;
});
