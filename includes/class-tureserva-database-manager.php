<?php
/**
 * Database Manager for TuReserva
 *
 * Handles the creation and updates of custom database tables using dbDelta.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Database_Manager {

    /**
     * Create or update all custom tables.
     */
    public function create_tables() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $this->create_sync_urls_table();
        $this->create_sync_log_table();
    }

    /**
     * Create tureserva_sync_urls table.
     */
    private function create_sync_urls_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tureserva_sync_urls';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            room_id bigint(20) NOT NULL,
            sync_id varchar(32) NOT NULL,
            calendar_url text NOT NULL,
            source_name varchar(100) DEFAULT '',
            last_sync datetime DEFAULT '0000-00-00 00:00:00',
            sync_status varchar(20) DEFAULT 'pending',
            last_error text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY room_id (room_id),
            KEY sync_id (sync_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create tureserva_sync_log table.
     */
    private function create_sync_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tureserva_sync_log';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
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

        dbDelta( $sql );
    }
}
