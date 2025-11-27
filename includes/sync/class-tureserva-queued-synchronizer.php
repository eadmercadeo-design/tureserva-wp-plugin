<?php
/**
 * Sincronizador en Cola (Gestor)
 *
 * Gestiona la cola de alojamientos a sincronizar para evitar sobrecarga.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Queued_Synchronizer {

    protected $option_name = 'tureserva_ical_queue';
    protected $current_item_option = 'tureserva_ical_current_item';

    public function __construct() {
        // Hook para procesar el siguiente item cuando el anterior termine
        add_action( 'tureserva_ical_sync_complete', array( $this, 'do_next' ) );
    }

    /**
     * Añade alojamientos a la cola y comienza el proceso.
     *
     * @param array $room_ids
     */
    public function sync( $room_ids ) {
        // 1. Filtrar alojamientos que realmente tienen URLs de sincronización
        $repo = new TuReserva_Sync_Urls_Repository();
        $rooms_with_calendars = $repo->get_all_room_ids();
        
        $rooms_to_sync = array_intersect( $room_ids, $rooms_with_calendars );

        if ( empty( $rooms_to_sync ) ) {
            return;
        }

        // 2. Añadir a la cola
        $this->add_to_queue( $rooms_to_sync );

        // 3. Iniciar proceso
        $this->do_next();
    }

    /**
     * Añade items a la cola (array en wp_options).
     */
    protected function add_to_queue( $room_ids ) {
        $queue = get_option( $this->option_name, array() );
        
        foreach ( $room_ids as $id ) {
            if ( ! in_array( $id, $queue ) ) {
                $queue[] = $id;
            }
        }

        update_option( $this->option_name, $queue );
    }

    /**
     * Procesa el siguiente item en la cola.
     */
    public function do_next() {
        // Verificar si ya hay algo ejecutándose (simple lock)
        // En un sistema real de background processing usaríamos WP_Background_Process
        // Aquí usaremos una implementación simplificada pero funcional.
        
        $queue = get_option( $this->option_name, array() );

        if ( empty( $queue ) ) {
            return; // Cola vacía
        }

        // Obtener el siguiente ID
        $next_room_id = array_shift( $queue );
        update_option( $this->option_name, $queue ); // Guardar cola actualizada

        // Ejecutar sincronización en segundo plano (simulado o real)
        // Para evitar timeouts en bucles largos, delegamos al Background Synchronizer
        $background_sync = new TuReserva_Background_Synchronizer();
        $background_sync->process_room( $next_room_id );
    }
}
