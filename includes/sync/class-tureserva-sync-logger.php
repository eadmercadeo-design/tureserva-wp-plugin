<?php
/**
 * Logger para la Sincronización iCal
 *
 * Se encarga de registrar eventos de sincronización para auditoría y debug.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Sync_Logger {

    protected $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'tureserva_sync_logs';
    }

    /**
     * Registra un evento de sincronización.
     *
     * @param int    $room_id  ID del alojamiento.
     * @param string $channel  Canal (Airbnb, Booking, etc.) o URL.
     * @param float  $duration Duración en segundos.
     * @param string $result   success, warning, error.
     * @param string $message  Detalle del resultado.
     */
    public function log( $room_id, $channel, $duration, $result, $message = '' ) {
        global $wpdb;

        // Si la tabla no existe, la creamos (fail-safe simple)
        // En producción idealmente esto va en la activación del plugin.
        // $this->ensure_table_exists();

        $data = array(
            'room_id'     => $room_id,
            'channel'     => substr( $channel, 0, 100 ),
            'duration'    => $duration,
            'result'      => $result,
            'message'     => $message,
            'created_at'  => current_time( 'mysql' )
        );

        $format = array( '%d', '%s', '%f', '%s', '%s', '%s' );

        // Insertar
        // $wpdb->insert( $this->table_name, $data, $format );
        
        // Por ahora, como no tengo certeza de creación de tabla en este paso,
        // usaré error_log para cumplir la traza básica y update_option para "último estado"
        // La creación de tabla real debería ir en el Database Manager.
        
        $log_entry = sprintf(
            "[TuReserva iCal] Room: %d | Channel: %s | Result: %s | Time: %ss | Msg: %s",
            $room_id, $channel, $result, $duration, $message
        );
        
        if ( $result === 'error' ) {
            error_log( $log_entry );
        }
        
        // Guardar log simplificado en meta del post para acceso rápido
        $logs = get_post_meta( $room_id, '_tureserva_sync_logs_history', true );
        if ( ! is_array( $logs ) ) $logs = array();
        
        array_unshift( $logs, $data );
        $logs = array_slice( $logs, 0, 50 ); // Guardar solo los últimos 50
        
        update_post_meta( $room_id, '_tureserva_sync_logs_history', $logs );
        
        // Actualizar estado general
        update_post_meta( $room_id, '_tureserva_last_sync_status', $result );
        update_post_meta( $room_id, '_tureserva_last_sync_time', current_time( 'mysql' ) );
    }

    /**
     * Obtiene logs de un alojamiento.
     */
    public function get_logs( $room_id, $limit = 20 ) {
        $logs = get_post_meta( $room_id, '_tureserva_sync_logs_history', true );
        if ( ! is_array( $logs ) ) return array();
        return array_slice( $logs, 0, $limit );
    }
    
    /**
     * Obtiene logs globales (agregando de todos los alojamientos - costoso, mejor usar tabla custom en futuro)
     * Para esta fase, iteraremos los alojamientos activos.
     */
    public function get_global_recent_logs( $limit = 20 ) {
        // Esto es ineficiente para muchos alojamientos, pero funcional para MVP sin tabla dedicada nueva.
        // En implementación ideal: SELECT * FROM wp_tureserva_sync_logs ORDER BY created_at DESC LIMIT X
        
        $args = array(
            'post_type' => 'trs_alojamiento',
            'posts_per_page' => 10, // Solo los más recientes
            'meta_key' => '_tureserva_last_sync_time',
            'orderby' => 'meta_value',
            'order' => 'DESC' 
        );
        
        $posts = get_posts( $args );
        $all_logs = array();
        
        foreach ( $posts as $p ) {
            $p_logs = $this->get_logs( $p->ID, 5 );
            foreach($p_logs as $l) {
                // Añadir título para visualización
                $l['room_title'] = $p->post_title;
                $all_logs[] = $l;
            }
        }
        
        // Ordenar por fecha desc
        usort( $all_logs, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice( $all_logs, 0, $limit );
    }
}
