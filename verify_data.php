<?php
// Load WordPress environment
// Adjust the path to wp-load.php as needed based on the file location
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

if ( php_sapi_name() !== 'cli' ) {
    echo "<pre>";
}

echo "==========================================================\n";
echo "VERIFICACIÓN DE DATOS: ALOJAMIENTOS Y RESERVAS\n";
echo "==========================================================\n\n";

// 1. Verify Accommodations
$args_alojamientos = array(
    'post_type'      => 'trs_alojamiento',
    'posts_per_page' => -1,
    'post_status'    => 'any',
);

$alojamientos = get_posts( $args_alojamientos );

echo "TOTAL ALOJAMIENTOS ENCONTRADOS: " . count( $alojamientos ) . "\n\n";

$alojamiento_ids = array();

if ( ! empty( $alojamientos ) ) {
    foreach ( $alojamientos as $alojamiento ) {
        $id = $alojamiento->ID;
        $title = $alojamiento->post_title;
        $status = $alojamiento->post_status;
        $alojamiento_ids[] = $id;

        echo "----------------------------------------------------------\n";
        echo "Alojamiento ID: $id | Título: $title | Estado: $status\n";
        
        // Check for reservations linked to this accommodation
        $args_reservas = array(
            'post_type'      => 'tureserva_reserva',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => array(
                array(
                    'key'   => '_tureserva_alojamiento_id',
                    'value' => $id,
                ),
            ),
        );

        $reservas = get_posts( $args_reservas );
        $count_reservas = count( $reservas );

        echo "  -> Reservas Conectadas: $count_reservas\n";

        if ( $count_reservas > 0 ) {
            foreach ( $reservas as $reserva ) {
                $r_id = $reserva->ID;
                $r_status = get_post_meta( $r_id, '_tureserva_estado', true );
                $r_checkin = get_post_meta( $r_id, '_tureserva_checkin', true );
                $r_checkout = get_post_meta( $r_id, '_tureserva_checkout', true );
                $r_cliente = get_post_meta( $r_id, '_tureserva_cliente_nombre', true );
                
                echo "     - Reserva ID: $r_id | Estado: $r_status | Fechas: $r_checkin a $r_checkout | Cliente: $r_cliente\n";
            }
        } else {
            echo "     (No hay reservas asociadas a este alojamiento)\n";
        }
    }
} else {
    echo "ERROR: No se encontraron alojamientos (post_type: trs_alojamiento).\n";
}

echo "\n==========================================================\n";
echo "BUSCANDO RESERVAS HUÉRFANAS (Sin Alojamiento Válido)\n";
echo "==========================================================\n";

// 2. Check for Orphaned Reservations
$args_all_reservas = array(
    'post_type'      => 'tureserva_reserva',
    'posts_per_page' => -1,
    'post_status'    => 'any',
);

$all_reservas = get_posts( $args_all_reservas );
$orphans_found = 0;

foreach ( $all_reservas as $reserva ) {
    $r_id = $reserva->ID;
    $linked_room_id = get_post_meta( $r_id, '_tureserva_alojamiento_id', true );

    if ( empty( $linked_room_id ) || ! in_array( $linked_room_id, $alojamiento_ids ) ) {
        $orphans_found++;
        $r_status = get_post_meta( $r_id, '_tureserva_estado', true );
        echo "HUÉRFANA -> Reserva ID: $r_id | Alojamiento ID guardado: " . ( empty($linked_room_id) ? 'VACÍO' : $linked_room_id ) . " | Estado: $r_status\n";
    }
}

if ( $orphans_found === 0 ) {
    echo "Excelente: No se encontraron reservas huérfanas.\n";
} else {
    echo "\nATENCIÓN: Se encontraron $orphans_found reservas sin un alojamiento válido asociado.\n";
}

echo "\n==========================================================\n";
echo "FIN DEL REPORTE\n";
echo "==========================================================\n";
