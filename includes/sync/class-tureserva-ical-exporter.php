<?php
/**
 * Exportador iCal
 *
 * Genera el contenido del archivo .ics con las reservas locales.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_iCal_Exporter {

    /**
     * Genera el contenido iCal para un alojamiento.
     *
     * @param int $room_id ID del alojamiento.
     * @param bool $include_pending Incluir reservas pendientes en la exportación (default false).
     * @return string Contenido .ics
     */
    public function export( $room_id, $include_pending = false ) {
        $room = get_post( $room_id );
        if ( ! $room || $room->post_type !== 'trs_alojamiento' ) {
            return '';
        }

        $eol = "\r\n";
        $output  = "BEGIN:VCALENDAR" . $eol;
        $output .= "VERSION:2.0" . $eol;
        $output .= "PRODID:-//" . sanitize_title( get_bloginfo( 'name' ) ) . "//TuReserva//EN" . $eol;
        $output .= "CALSCALE:GREGORIAN" . $eol;
        $output .= "METHOD:PUBLISH" . $eol;
        $output .= "X-WR-CALNAME:" . $this->escape_text( $room->post_title . ' - ' . get_bloginfo( 'name' ) ) . $eol;
        $output .= "X-WR-TIMEZONE:UTC" . $eol;

        // 1. Obtener Reservas
        $booking_repo = new TuReserva_Booking_Repository();
        
        // Determinar estados a exportar
        $status_filter = array( 'publish', 'confirmed' );
        if ( $include_pending ) {
            $status_filter[] = 'pending';
        }

        $reservas = $booking_repo->find_all( array(
            'rooms'       => array( $room_id ),
            'post_status' => $status_filter,
        ) );

        foreach ( $reservas as $reserva ) {
            $output .= $this->create_vevent( $reserva, $room_id );
        }

        // 2. Obtener Bloqueos Manuales
        $bloqueos = get_post_meta( $room_id, '_tureserva_bloqueos', true );
        if ( is_array( $bloqueos ) ) {
            foreach ( $bloqueos as $bloqueo ) {
                $output .= $this->create_blocked_vevent( $bloqueo, $room_id );
            }
        }

        $output .= "END:VCALENDAR";

        return $output;
    }

    /**
     * Crea un VEVENT para una reserva.
     *
     * @param WP_Post $reserva
     * @param int $room_id
     * @return string
     */
    protected function create_vevent( $reserva, $room_id ) {
        $eol = "\r\n";
        
        $check_in  = get_post_meta( $reserva->ID, '_tureserva_checkin', true );
        $check_out = get_post_meta( $reserva->ID, '_tureserva_checkout', true );
        $estado    = get_post_meta( $reserva->ID, '_tureserva_estado', true );
        
        // Si es importada, usar su UID original si existe
        $uid = get_post_meta( $reserva->ID, '_tureserva_ical_uid', true );
        if ( empty( $uid ) ) {
            $uid = 'trs-res-' . $reserva->ID . '@' . $_SERVER['HTTP_HOST'];
        }

        // Formato Ymd
        $dtstart = date( 'Ymd', strtotime( $check_in ) );
        $dtend   = date( 'Ymd', strtotime( $check_out ) );
        $dtstamp = gmdate( 'Ymd\THis\Z' );

        $summary = "Reserva #" . $reserva->ID;
        if ( $estado === 'pendiente' ) {
            $summary .= " (Pendiente)";
        }

        $out  = "BEGIN:VEVENT" . $eol;
        $out .= "UID:" . $uid . $eol;
        $out .= "DTSTAMP:" . $dtstamp . $eol;
        $out .= "DTSTART;VALUE=DATE:" . $dtstart . $eol;
        $out .= "DTEND;VALUE=DATE:" . $dtend . $eol;
        $out .= "SUMMARY:" . $this->escape_text( $summary ) . $eol;
        $out .= "STATUS:" . ( $estado === 'cancelada' ? 'CANCELLED' : 'CONFIRMED' ) . $eol;
        $out .= "END:VEVENT" . $eol;

        return $out;
    }

    /**
     * Crea un VEVENT para un bloqueo manual.
     *
     * @param array $bloqueo
     * @param int $room_id
     * @return string
     */
    protected function create_blocked_vevent( $bloqueo, $room_id ) {
        $eol = "\r\n";

        $inicio = isset( $bloqueo['inicio'] ) ? $bloqueo['inicio'] : '';
        $fin    = isset( $bloqueo['fin'] ) ? $bloqueo['fin'] : '';
        $motivo = isset( $bloqueo['motivo'] ) ? $bloqueo['motivo'] : 'Bloqueo Manual';

        if ( empty( $inicio ) || empty( $fin ) ) return '';

        // UID único para el bloqueo
        $uid = md5( $inicio . $fin . $room_id . 'blocked' ) . '@' . $_SERVER['HTTP_HOST'];
        
        $dtstart = date( 'Ymd', strtotime( $inicio ) );
        $dtend   = date( 'Ymd', strtotime( $fin ) );
        $dtstamp = gmdate( 'Ymd\THis\Z' );

        $out  = "BEGIN:VEVENT" . $eol;
        $out .= "UID:" . $uid . $eol;
        $out .= "DTSTAMP:" . $dtstamp . $eol;
        $out .= "DTSTART;VALUE=DATE:" . $dtstart . $eol;
        $out .= "DTEND;VALUE=DATE:" . $dtend . $eol;
        $out .= "SUMMARY:BLOCKED" . $eol;
        $out .= "DESCRIPTION:" . $this->escape_text( $motivo ) . $eol;
        $out .= "STATUS:CONFIRMED" . $eol;
        $out .= "END:VEVENT" . $eol;

        return $out;
    }

    /**
     * Escapa texto para iCal.
     *
     * @param string $text
     * @return string
     */
    protected function escape_text( $text ) {
        $text = str_replace( array( "\r\n", "\n", "\r" ), "\\n", $text );
        $text = str_replace( array( ",", ";" ), array( "\\,", "\\;" ), $text );
        return $text;
    }
}
