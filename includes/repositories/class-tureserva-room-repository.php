<?php
/**
 * Repositorio de Alojamientos
 *
 * Wrapper para interactuar con el CPT 'trs_alojamiento' de forma orientada a objetos.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Room_Repository {

    /**
     * Busca un alojamiento por su ID.
     *
     * @param int $id
     * @return WP_Post|null
     */
    public function find_by_id( $id ) {
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'trs_alojamiento' ) {
            return null;
        }
        return $post;
    }

    /**
     * Obtiene las URLs de sincronización de un alojamiento.
     *
     * @param int $room_id
     * @return array
     */
    public function get_sync_urls( $room_id ) {
        // Delegamos al repositorio de URLs si existe la instancia global,
        // o instanciamos uno nuevo si es necesario (aunque lo ideal es inyección de dependencias).
        // Por simplicidad en este paso, asumimos que se usará el repositorio de URLs directamente
        // donde se necesite, pero este método puede servir de atajo.
        
        $repo = new TuReserva_Sync_Urls_Repository();
        return $repo->get_urls( $room_id );
    }

    /**
     * Obtiene todos los alojamientos publicados.
     * 
     * @return int[] IDs de alojamientos
     */
    public function get_all_ids() {
        return get_posts( array(
            'post_type'      => 'trs_alojamiento',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ) );
    }
}
