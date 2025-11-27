<?php
/**
 * Repositorio de Reservas
 *
 * Wrapper para interactuar con el CPT 'tureserva_reserva' de forma orientada a objetos.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Booking_Repository {

    /**
     * Busca reservas según criterios.
     *
     * @param array $args
     * @return WP_Post[]
     */
    public function find_all( $args = array() ) {
        $defaults = array(
            'post_type'      => 'tureserva_reserva',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'pending', 'confirmed', 'private' ), // private para reservas importadas a veces
        );

        // Mapeo de argumentos personalizados a WP_Query
        if ( isset( $args['rooms'] ) ) {
            $args['meta_query'][] = array(
                'key'     => '_tureserva_alojamiento_id',
                'value'   => $args['rooms'],
                'compare' => 'IN',
            );
            unset( $args['rooms'] );
        }

        if ( isset( $args['date_from'] ) && isset( $args['date_to'] ) ) {
            // Buscamos reservas que se solapen con el rango
            // (StartA <= EndB) and (EndA >= StartB)
            $args['meta_query'][] = array(
                'relation' => 'AND',
                array(
                    'key'     => '_tureserva_checkout',
                    'value'   => $args['date_from'],
                    'compare' => '>',
                    'type'    => 'DATE'
                ),
                array(
                    'key'     => '_tureserva_checkin',
                    'value'   => $args['date_to'],
                    'compare' => '<',
                    'type'    => 'DATE'
                )
            );
            unset( $args['date_from'], $args['date_to'] );
        }

        if ( isset( $args['room_locked'] ) && $args['room_locked'] ) {
             $args['meta_query'][] = array(
                'key'     => '_tureserva_estado',
                'value'   => array( 'confirmada', 'pendiente' ),
                'compare' => 'IN'
            );
            unset( $args['room_locked'] );
        }

        $query_args = wp_parse_args( $args, $defaults );
        return get_posts( $query_args );
    }

    /**
     * Guarda una reserva (Wrapper para tureserva_crear_reserva o update).
     * 
     * @param array $data Datos de la reserva
     * @return int|WP_Error ID de la reserva o error
     */
    public function save( $data ) {
        if ( isset( $data['ID'] ) ) {
            // Actualización (simplificada por ahora, solo meta)
            $id = $data['ID'];
            foreach ( $data['meta'] as $key => $value ) {
                update_post_meta( $id, $key, $value );
            }
            return $id;
        } else {
            // Creación
            if ( function_exists( 'tureserva_crear_reserva' ) ) {
                // Adaptar formato de datos si es necesario
                return tureserva_crear_reserva( $data );
            }
            return new WP_Error( 'function_missing', 'tureserva_crear_reserva no existe' );
        }
    }

    /**
     * Elimina una reserva.
     *
     * @param int $id
     * @return bool
     */
    public function delete( $id ) {
        return wp_delete_post( $id, true );
    }
}
