<?php
/**
 * Cron de Sincronización Automática
 *
 * Disparador programado que inicia el proceso de sincronización.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Ical_Cron {

    public function __construct() {
        add_action( 'tureserva_cron_sync_calendars', array( $this, 'do_cron_job' ) );
    }

    /**
     * Ejecuta el trabajo del Cron.
     * Obtiene todos los alojamientos y los añade a la cola de sincronización.
     */
    public function do_cron_job() {
        // Obtener todos los IDs de alojamientos publicados
        $room_repo = new TuReserva_Room_Repository();
        $ids = $room_repo->get_all_ids();

        if ( empty( $ids ) ) {
            return;
        }

        // Instanciar el sincronizador en cola
        // (En una implementación ideal, esto se inyectaría)
        $synchronizer = new TuReserva_Queued_Synchronizer();
        $synchronizer->sync( $ids );

        // Registrar que el cron se ejecutó
        update_option( 'tureserva_ical_last_cron_run', current_time( 'mysql' ) );
    }
}
