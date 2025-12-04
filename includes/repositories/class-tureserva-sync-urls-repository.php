<?php
/**
 * Repositorio de URLs de Sincronización
 *
 * Gestiona la tabla de base de datos donde se guardan los enlaces a calendarios externos.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Sync_Urls_Repository {

    protected $table_name = 'tureserva_sync_urls';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . $this->table_name;
    }

    /**
     * Prepara las URLs generando su hash MD5.
     */
    protected function prepare_urls( $urls ) {
        $prepared = array();
        foreach ( $urls as $url ) {
            // Limpieza básica de URL
            $url = esc_url_raw( trim( $url ) );
            if ( empty( $url ) ) continue;

            $sync_id = md5( $url );
            $prepared[ $sync_id ] = $url;
        }
        return $prepared;
    }

    /**
     * Inserta nuevas URLs para un alojamiento.
     */
    public function insert_urls( $room_id, $urls ) {
        global $wpdb;
        
        if ( empty( $urls ) ) {
            return;
        }

        $urls_prepared = $this->prepare_urls( $urls );
        
        if ( empty( $urls_prepared ) ) {
            return;
        }

        $values = array();
        foreach ( $urls_prepared as $sync_id => $url ) {
            $values[] = $wpdb->prepare( '(%d, %s, %s)', $room_id, $sync_id, $url );
        }

        $sql = "INSERT INTO {$this->table_name} (room_id, sync_id, calendar_url) VALUES " . implode( ', ', $values );
        $wpdb->query( $sql );
    }

    /**
     * Obtiene todos los IDs de alojamientos que tienen calendarios sincronizados.
     *
     * @return int[]
     */
    public function get_all_room_ids() {
        global $wpdb;
        $room_ids = $wpdb->get_col( "SELECT DISTINCT room_id FROM {$this->table_name}" );
        return array_map( 'absint', $room_ids );
    }

    /**
     * Obtiene las URLs de un alojamiento.
     *
     * @param int $room_id
     * @return array [ 'sync_id' => 'url' ]
     */
    public function get_urls( $room_id ) {
        global $wpdb;
        $sql  = $wpdb->prepare( "SELECT sync_id, calendar_url FROM {$this->table_name} WHERE room_id = %d", $room_id );
        $rows = $wpdb->get_results( $sql, ARRAY_A );

        if ( empty( $rows ) ) {
            return array();
        }

        $urls = array();
        foreach ( $rows as $row ) {
            $urls[ $row['sync_id'] ] = $row['calendar_url'];
        }
        return $urls;
    }

    /**
     * Actualiza las URLs de un alojamiento (Sincronización completa: inserta nuevas, borra viejas).
     */
    public function update_urls( $room_id, $urls ) {
        if ( empty( $urls ) ) {
            $this->remove_urls( $room_id );
        } else {
            $new_urls      = $this->prepare_urls( $urls );
            $existing_urls = $this->get_urls( $room_id );

            $to_insert     = array_diff_key( $new_urls, $existing_urls );
            $to_remove     = array_diff_key( $existing_urls, $new_urls );

            if ( ! empty( $to_insert ) ) {
                $this->insert_urls( $room_id, $to_insert );
            }

            if ( ! empty( $to_remove ) ) {
                $this->remove_urls( $room_id, array_keys( $to_remove ) );
            }
        }
    }

    /**
     * Elimina URLs de un alojamiento.
     *
     * @param int $room_id
     * @param null|string|array $sync_id Si es null, borra todas.
     */
    public function remove_urls( $room_id, $sync_id = null ) {
        global $wpdb;

        if ( is_null( $sync_id ) ) {
            $sql = $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE room_id = %d", $room_id );
        } else {
            if ( is_array( $sync_id ) ) {
                $sync_ids_escaped = array();
                foreach($sync_id as $sid) {
                    $sync_ids_escaped[] = esc_sql($sid);
                }
                $sync_ids_str = "'" . implode( "', '", $sync_ids_escaped ) . "'";
                $sql = $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE room_id = %d AND sync_id IN ({$sync_ids_str})", $room_id );
            } else {
                $sql = $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE room_id = %d AND sync_id = %s", $room_id, $sync_id );
            }
        }
        $wpdb->query( $sql );
    }

    /**
     * Actualiza el estado de sincronización de una URL.
     *
     * @param string $sync_id
     * @param string $status 'success' | 'error'
     * @param string $error_msg
     */
    public function update_sync_status( $sync_id, $status, $error_msg = '' ) {
        global $wpdb;
        
        $data = array(
            'last_sync'   => current_time( 'mysql' ),
            'sync_status' => $status,
            'last_error'  => $error_msg
        );
        
        $where = array( 'sync_id' => $sync_id );
        
        $wpdb->update( $this->table_name, $data, $where );
    }

    /**
     * Obtiene el estado de sincronización de un alojamiento.
     *
     * @param int $room_id
     * @return object|null
     */
    public function get_sync_status( $room_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE room_id = %d LIMIT 1", $room_id ) );
    }
    
}
