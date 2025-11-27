<?php
// Script de Verificación para TuReserva iCal
// Ejecutar con: wp eval-file verification_test.php

if ( ! defined( 'ABSPATH' ) ) {
    require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
}

echo "=== INICIANDO VERIFICACIÓN iCal ===\n";

// 1. Crear tabla si no existe (simular activación)
echo "1. Verificando tabla DB...\n";
$repo = new TuReserva_Sync_Urls_Repository();
$repo->create_table();
echo "   Tabla verificada.\n";

// 1.1 Verificar columna created_at
global $wpdb;
$cols = $wpdb->get_col( "DESCRIBE {$wpdb->prefix}tureserva_sync_urls" );
if ( in_array( 'created_at', $cols ) ) {
    echo "   [EXITO] Columna 'created_at' encontrada.\n";
} else {
    echo "   [FALLO] Columna 'created_at' NO encontrada.\n";
}

// 2. Crear Alojamiento de Prueba
echo "2. Creando Alojamiento de Prueba...\n";
$room_id = wp_insert_post( array(
    'post_title'  => 'Alojamiento Test iCal',
    'post_type'   => 'trs_alojamiento',
    'post_status' => 'publish'
) );

if ( is_wp_error( $room_id ) ) {
    die( "Error creando alojamiento: " . $room_id->get_error_message() );
}
echo "   Alojamiento creado: ID $room_id\n";

// 3. Añadir URL de Sincronización
echo "3. Añadiendo URL de Sincronización...\n";
$dummy_ics_url = plugin_dir_url( __FILE__ ) . 'dummy_calendar.ics';
$repo->update_urls( $room_id, array( $dummy_ics_url ) );
$urls = $repo->get_urls( $room_id );
echo "   URLs guardadas: " . print_r( $urls, true ) . "\n";

// 4. Ejecutar Sincronización (Simulada)
echo "4. Ejecutando Sincronización...\n";
// Forzamos la sincronización directa sin cola para el test
$bg_sync = new TuReserva_Background_Synchronizer();
$bg_sync->process_room( $room_id );

// 5. Verificar Reserva Creada
echo "5. Verificando Reserva...\n";
$booking_repo = new TuReserva_Booking_Repository();
$reservas = $booking_repo->find_all( array(
    'rooms' => array( $room_id )
) );

if ( ! empty( $reservas ) ) {
    foreach ( $reservas as $reserva ) {
        echo "   [EXITO] Reserva encontrada: ID " . $reserva->ID . "\n";
        echo "           Check-in: " . get_post_meta( $reserva->ID, '_tureserva_checkin', true ) . "\n";
        echo "           Check-out: " . get_post_meta( $reserva->ID, '_tureserva_checkout', true ) . "\n";
        echo "           UID: " . get_post_meta( $reserva->ID, '_tureserva_ical_uid', true ) . "\n";
    }
} else {
    echo "   [FALLO] No se encontraron reservas.\n";
}

// 5.1 Verificar Estado de Sincronización en DB
echo "5.1 Verificando Estado Sync en DB...\n";
$urls_data = $repo->get_urls( $room_id );
// Nota: get_urls solo devuelve [sync_id => url], necesitamos consultar la tabla directa para ver status
global $wpdb;
$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$repo->table_name} WHERE room_id = %d", $room_id ) );

if ( $row && $row->sync_status === 'success' ) {
    echo "   [EXITO] Estado Sync: " . $row->sync_status . "\n";
    echo "           Last Sync: " . $row->last_sync . "\n";
} else {
    echo "   [FALLO] Estado Sync incorrecto: " . ( $row ? $row->sync_status : 'NULL' ) . "\n";
    if ( $row && ! empty( $row->last_error ) ) {
        echo "           Error: " . $row->last_error . "\n";
    }
}

// 6. Verificar Exportación
echo "6. Verificando Exportación...\n";
$exporter = new TuReserva_Ical_Exporter();
$ics_content = $exporter->export( $room_id );

if ( strpos( $ics_content, 'BEGIN:VCALENDAR' ) !== false && strpos( $ics_content, 'UID:' ) !== false ) {
    echo "   [EXITO] Contenido iCal generado correctamente.\n";
    // echo $ics_content; // Descomentar para ver contenido
} else {
    echo "   [FALLO] Contenido iCal inválido.\n";
}

// Limpieza
echo "7. Limpiando...\n";
wp_delete_post( $room_id, true );
// Borrar reservas creadas (opcional, pero recomendado)
foreach ( $reservas as $r ) wp_delete_post( $r->ID, true );
$repo->remove_urls( $room_id );

echo "=== VERIFICACIÓN COMPLETADA ===\n";
