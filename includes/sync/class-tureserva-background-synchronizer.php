<?php
/**
 * Sincronizador en Segundo Plano (Worker)
 *
 * Realiza la descarga real de los archivos .ics desde las URLs externas.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Background_Synchronizer {

    /**
     * Procesa la sincronización de un alojamiento específico.
     *
     * @param int $room_id
     */
    public function process_room( $room_id ) {
        // 1. Obtener URLs
        $repo = new TuReserva_Sync_Urls_Repository();
        $urls = $repo->get_urls( $room_id );

        if ( empty( $urls ) ) {
            // No hay URLs, terminamos y pasamos al siguiente
            do_action( 'tureserva_ical_sync_complete' );
            return;
        }

        $importer = new TuReserva_Ical_Importer();

        foreach ( $urls as $sync_id => $url ) {
            try {
                // 2. Descargar contenido
                $ical_content = $this->fetch_feed( $url );

                // 3. Importar eventos
                $importer->parse_and_import( $ical_content, $room_id, $sync_id );
                
                // Actualizar estado en DB (éxito)
                $repo->update_sync_status( $sync_id, 'success' );
                
            } catch ( Exception $e ) {
                // Registrar error
                $error_msg = $e->getMessage();
                error_log( "TuReserva iCal Error [Room $room_id]: " . $error_msg );
                
                // Actualizar estado en DB (error)
                $repo->update_sync_status( $sync_id, 'error', $error_msg );
            }
        }

        // 4. Finalizar
        do_action( 'tureserva_ical_sync_complete' );
    }

    /**
     * Descarga el feed iCal.
     *
     * @param string $url
     * @return string Contenido del archivo .ics
     * @throws Exception
     */
    protected function fetch_feed( $url ) {
        $response = wp_remote_get( $url, array(
            'timeout'    => 30, // 30 segundos
            'user-agent' => 'TuReserva/' . TURESERVA_VERSION . '; ' . home_url(),
            'sslverify'  => false // A veces necesario para ciertos hosts, aunque no ideal
        ) );

        if ( is_wp_error( $response ) ) {
            throw new Exception( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            throw new Exception( "HTTP Error $code" );
        }

        return wp_remote_retrieve_body( $response );
    }
}
