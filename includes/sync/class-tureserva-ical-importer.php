<?php
/**
 * Importador iCal
 *
 * Procesa los eventos del calendario externo y crea/actualiza reservas en WordPress.
 * Adaptado para TuReserva.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Ical_Importer {

    /**
     * Parsea el contenido iCal e importa los eventos.
     *
     * @param string $ical_content
     * @param int $room_id
     * @param string $sync_id
     */
    /**
     * Parsea el contenido iCal e importa los eventos.
     *
     * @param string $ical_content
     * @param int $room_id
     * @param string $sync_id
     */
    public function parse_and_import( $ical_content, $room_id, $sync_id ) {
        // 1. Parsear eventos
        $events = $this->parse_ics( $ical_content );

        if ( empty( $events ) ) {
            return;
        }

        $booking_repo = new TuReserva_Booking_Repository();

        foreach ( $events as $event ) {
            // Validar fechas
            if ( empty( $event['dtstart'] ) || empty( $event['dtend'] ) ) {
                continue;
            }

            // Convertir a formato Y-m-d
            $check_in  = date( 'Y-m-d', $event['dtstart'] );
            $check_out = date( 'Y-m-d', $event['dtend'] );

            // Si es una reserva pasada, ignorar (opcional, pero recomendado)
            if ( strtotime( $check_out ) < current_time( 'timestamp' ) ) {
                continue;
            }

            // 2. Buscar si ya existe por UID
            $existing_booking_id = $this->find_booking_by_uid( $event['uid'] );

            if ( $existing_booking_id ) {
                // -> Existe: Actualizar si es necesario
                $this->update_booking( $existing_booking_id, $event, $check_in, $check_out );
            } else {
                // -> No existe: Verificar disponibilidad (Intersecciones)
                $existing_bookings = $booking_repo->find_all( array(
                    'rooms'     => array( $room_id ),
                    'date_from' => $check_in,
                    'date_to'   => $check_out,
                ) );

                if ( empty( $existing_bookings ) ) {
                    // -> No hay conflicto: Crear reserva
                    $this->create_booking( $event, $room_id, $sync_id, $check_in, $check_out );
                } else {
                    // -> Hay conflicto:
                    error_log( "TuReserva iCal: Conflicto en Alojamiento $room_id para fechas $check_in - $check_out. UID: {$event['uid']}" );
                }
            }
        }
    }

    /**
     * Busca una reserva por su UID de iCal.
     *
     * @param string $uid
     * @return int|false ID de la reserva o false.
     */
    protected function find_booking_by_uid( $uid ) {
        if ( empty( $uid ) ) return false;
        
        $args = array(
            'post_type'  => 'trs_reserva',
            'meta_key'   => '_tureserva_ical_uid',
            'meta_value' => $uid,
            'fields'     => 'ids',
            'numberposts' => 1
        );
        
        $posts = get_posts( $args );
        return ! empty( $posts ) ? $posts[0] : false;
    }

    /**
     * Actualiza una reserva existente si las fechas han cambiado.
     */
    protected function update_booking( $booking_id, $event, $check_in, $check_out ) {
        $current_checkin  = get_post_meta( $booking_id, '_tureserva_checkin', true );
        $current_checkout = get_post_meta( $booking_id, '_tureserva_checkout', true );

        if ( $current_checkin !== $check_in || $current_checkout !== $check_out ) {
            update_post_meta( $booking_id, '_tureserva_checkin', $check_in );
            update_post_meta( $booking_id, '_tureserva_checkout', $check_out );
            // Actualizar también el título o notas si se desea
            error_log( "TuReserva iCal: Reserva $booking_id actualizada (Fechas cambiadas)." );
        }
    }

    /**
     * Crea una reserva a partir de un evento iCal.
     */
    protected function create_booking( $event, $room_id, $sync_id, $check_in, $check_out ) {
        $booking_repo = new TuReserva_Booking_Repository();
        
        // Datos para la reserva
        $data = array(
            'alojamiento_id' => $room_id,
            'check_in'       => $check_in,
            'check_out'      => $check_out,
            'huespedes'      => array( 'adultos' => 1, 'ninos' => 0 ), // Default
            'servicios'      => array(),
            'cliente'        => array(
                'nombre'   => 'Importado iCal', // O usar $event['summary'] si es seguro
                'email'    => '',
                'telefono' => '',
                'notas'    => isset( $event['description'] ) ? $event['description'] : '',
            ),
            'estado'         => 'confirmada', // Las importaciones suelen ser confirmadas
            'origen'         => 'ical',
        );

        // Crear reserva
        $reserva_id = $booking_repo->save( $data );

        if ( ! is_wp_error( $reserva_id ) ) {
            // Guardar metadatos extra de iCal
            update_post_meta( $reserva_id, '_tureserva_ical_uid', $event['uid'] );
            update_post_meta( $reserva_id, '_tureserva_ical_sync_id', $sync_id );
            update_post_meta( $reserva_id, '_tureserva_ical_summary', $event['summary'] );
        }
    }

    /**
     * Parser simple de iCal (Regex).
     * Extrae VEVENTs y sus propiedades básicas.
     *
     * @param string $content
     * @return array
     */
    protected function parse_ics( $content ) {
        $events = array();
        
        // Normalizar saltos de línea
        $content = str_replace( array( "\r\n", "\r" ), "\n", $content );
        
        // Desdoblar líneas (unfolding)
        // Las líneas que empiezan con espacio son continuación de la anterior
        $content = preg_replace( "/\n[ \t]+/", "", $content );

        // Buscar bloques VEVENT
        preg_match_all( '/BEGIN:VEVENT(.*?)END:VEVENT/s', $content, $matches );

        if ( empty( $matches[1] ) ) {
            return array();
        }

        foreach ( $matches[1] as $event_str ) {
            $event = array(
                'uid'         => '',
                'dtstart'     => 0,
                'dtend'       => 0,
                'summary'     => '',
                'description' => '',
            );

            // UID
            if ( preg_match( '/^UID:(.*)$/m', $event_str, $m ) ) {
                $event['uid'] = trim( $m[1] );
            }

            // DTSTART
            if ( preg_match( '/^DTSTART(?:;.*)?:(\d{8}(?:T\d{6}Z?)?)/m', $event_str, $m ) ) {
                $event['dtstart'] = $this->parse_ical_date( $m[1] );
            }

            // DTEND
            if ( preg_match( '/^DTEND(?:;.*)?:(\d{8}(?:T\d{6}Z?)?)/m', $event_str, $m ) ) {
                $event['dtend'] = $this->parse_ical_date( $m[1] );
            }

            // SUMMARY
            if ( preg_match( '/^SUMMARY:(.*)$/m', $event_str, $m ) ) {
                $event['summary'] = trim( $m[1] );
            }

            // DESCRIPTION
            if ( preg_match( '/^DESCRIPTION:(.*)$/m', $event_str, $m ) ) {
                $event['description'] = trim( $m[1] );
            }

            $events[] = $event;
        }

        return $events;
    }

    /**
     * Convierte fecha iCal a timestamp.
     */
    protected function parse_ical_date( $ical_date ) {
        // Formatos: 20230101 o 20230101T120000Z
        return strtotime( $ical_date );
    }
}
