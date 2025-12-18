<?php
/**
 * AJAX Handler: Sincronización Manual Global
 *
 * Gestiona la petición de 'Sincronizar Ahora' desde el panel administrativo.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Sync_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_tureserva_manual_global_sync', array( $this, 'handle_manual_sync' ) );
    }

    public function handle_manual_sync() {
        // 1. Security Check
        check_ajax_referer( 'tureserva_sync_settings_action', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos.', 'tureserva' ) ) );
        }

        // 2. Trigger Sync
        // Obtenemos todos los IDs y los procesamos.
        // Nota: Para grandes volúmenes esto debería enviar a una cola (Background Process),
        // pero para el requisito de "Real AJAX" y feedback inmediato en MVP,
        // intentaremos procesar un lote pequeño o desencadenar la cola.
        
        $room_repo = new TuReserva_Room_Repository();
        $ids = $room_repo->get_all_ids();

        if ( empty( $ids ) ) {
            wp_send_json_error( array( 'message' => __( 'No hay alojamientos activos para sincronizar.', 'tureserva' ) ) );
        }

        // Usamos el Queued Synchronizer para procesar
        $synchronizer = new TuReserva_Queued_Synchronizer();
        $synchronizer->sync( $ids );
        
        // Esperamos un momento para permitir que el primer lote se procese (simulación de feedback inmediato)
        // En un sistema real asíncrono, devolveríamos "En progreso" y el UI haría polling.
        // Aquí devolvemos éxito indicando que se inició.
        
        wp_send_json_success( array( 
            'message' => __( 'Sincronización iniciada correctamente para ' . count($ids) . ' alojamientos.', 'tureserva' ),
            'count' => count($ids)
        ) );
    }
}

new TuReserva_Sync_Ajax();
