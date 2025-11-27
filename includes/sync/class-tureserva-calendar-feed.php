<?php
/**
 * Feed de Calendario
 *
 * Registra la URL del feed iCal que leerán las plataformas externas.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Calendar_Feed {

    public function __construct() {
        add_action( 'init', array( $this, 'setup_feed' ) );
    }

    /**
     * Registra el feed personalizado.
     * URL: example.com/?feed=tureserva.ics&ical_id=123
     */
    public function setup_feed() {
        add_feed( 'tureserva.ics', array( $this, 'export_ics' ) );

        // Ensure query var is recognized
        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'ical_id';
            return $vars;
        } );

        // Add rewrite rule for pretty URLs /tureserva.ics/<room_id>
        add_rewrite_rule( '^tureserva\.ics/([0-9]+)/?$', 'index.php?feed=tureserva.ics&ical_id=$matches[1]', 'top' );
    }

    /**
     * Callback del feed. Genera la salida.
     */
    public function export_ics() {
        $room_id = get_query_var( 'ical_id' );

        if ( empty( $room_id ) ) {
            status_header( 404 );
            die( 'Alojamiento no especificado.' );
        }

        $room_id = absint( $room_id );

        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="tureserva-' . $room_id . '.ics"' );

        $exporter = new TuReserva_Ical_Exporter();
        echo $exporter->export( $room_id );

        exit;
    }
}
