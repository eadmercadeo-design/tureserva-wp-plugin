<?php
// Script de Verificación de Conexiones de Menús y Datos
// Ejecutar con: wp eval-file verify_menus.php

if ( ! defined( 'ABSPATH' ) ) {
    require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
}

echo "=== INICIANDO VERIFICACIÓN DE MENÚS Y DATOS ===\n";

// 1. Verificar Registro de CPTs
echo "\n1. Verificando Registro de CPTs...\n";
$cpts = [
    'trs_alojamiento'    => 'Alojamientos',
    'tureserva_tarifa'   => 'Tarifas',
    'tureserva_reserva'  => 'Reservas',
    'temporada'          => 'Temporadas',
    'tureserva_servicio' => 'Servicios'
];

$all_registered = true;
foreach ( $cpts as $slug => $name ) {
    if ( post_type_exists( $slug ) ) {
        echo "   [OK] CPT '$name' ($slug) registrado.\n";
    } else {
        echo "   [ERROR] CPT '$name' ($slug) NO registrado.\n";
        $all_registered = false;
    }
}

if ( ! $all_registered ) {
    echo "\n[FATAL] No se pueden continuar las pruebas sin todos los CPTs.\n";
    exit;
}

// 2. Crear Datos de Prueba y Verificar Conexiones
echo "\n2. Creando Datos de Prueba y Verificando Conexiones...\n";

// 2.1 Crear Temporada
$season_id = wp_insert_post( [
    'post_title'  => 'Temporada Test 2025',
    'post_type'   => 'temporada',
    'post_status' => 'publish'
] );
if ( is_wp_error( $season_id ) ) die( "Error creando temporada: " . $season_id->get_error_message() );

update_post_meta( $season_id, '_tureserva_fecha_inicio', '2025-06-01' );
update_post_meta( $season_id, '_tureserva_fecha_fin', '2025-08-31' );
update_post_meta( $season_id, '_tureserva_dias_aplicados', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] );

echo "   [OK] Temporada creada (ID: $season_id)\n";

// 2.2 Crear Alojamiento
$room_id = wp_insert_post( [
    'post_title'  => 'Alojamiento Test Conexión',
    'post_type'   => 'trs_alojamiento',
    'post_status' => 'publish'
] );
if ( is_wp_error( $room_id ) ) die( "Error creando alojamiento: " . $room_id->get_error_message() );

echo "   [OK] Alojamiento creado (ID: $room_id)\n";

// 2.3 Crear Tarifa (Vinculada a Alojamiento y Temporada)
$rate_id = wp_insert_post( [
    'post_title'  => 'Tarifa Test',
    'post_type'   => 'tureserva_tarifa',
    'post_status' => 'publish'
] );
if ( is_wp_error( $rate_id ) ) die( "Error creando tarifa: " . $rate_id->get_error_message() );

// Vincular al alojamiento
update_post_meta( $rate_id, '_tureserva_alojamiento_id', $room_id );

// Estructura de precios vinculada a la temporada
$precios = [
    [
        'temporada_id' => $season_id,
        'precio_base'  => 100,
        'adultos'      => 2,
        'ninos'        => 1,
        'variables'    => []
    ]
];
update_post_meta( $rate_id, '_tureserva_precios', $precios );
update_post_meta( $rate_id, '_tureserva_fecha_inicio', '2025-06-01' );
update_post_meta( $rate_id, '_tureserva_fecha_fin', '2025-08-31' );

echo "   [OK] Tarifa creada (ID: $rate_id) y vinculada a Alojamiento ($room_id) y Temporada ($season_id)\n";

// 2.4 Crear Servicio
$service_id = wp_insert_post( [
    'post_title'  => 'Servicio Test Desayuno',
    'post_type'   => 'tureserva_servicio',
    'post_status' => 'publish'
] );
if ( is_wp_error( $service_id ) ) die( "Error creando servicio: " . $service_id->get_error_message() );

update_post_meta( $service_id, '_tureserva_precio_servicio', 15.00 );
update_post_meta( $service_id, '_tureserva_tipo_servicio', 'por_dia' );

echo "   [OK] Servicio creado (ID: $service_id)\n";

// 2.5 Crear Reserva (Vinculada a Alojamiento)
$booking_id = wp_insert_post( [
    'post_title'  => 'Reserva Test #123',
    'post_type'   => 'tureserva_reserva',
    'post_status' => 'publish'
] );
if ( is_wp_error( $booking_id ) ) die( "Error creando reserva: " . $booking_id->get_error_message() );

update_post_meta( $booking_id, '_tureserva_alojamiento_id', $room_id );
update_post_meta( $booking_id, '_tureserva_checkin', '2025-06-10' );
update_post_meta( $booking_id, '_tureserva_checkout', '2025-06-15' );
update_post_meta( $booking_id, '_tureserva_estado', 'confirmada' );
update_post_meta( $booking_id, '_tureserva_precio_total', 500.00 );

echo "   [OK] Reserva creada (ID: $booking_id) vinculada a Alojamiento ($room_id)\n";

// 3. Verificación de Integridad de Datos
echo "\n3. Verificando Integridad de Datos...\n";

// 3.1 Verificar Tarifa -> Alojamiento
$linked_room_id = get_post_meta( $rate_id, '_tureserva_alojamiento_id', true );
if ( $linked_room_id == $room_id ) {
    echo "   [PASS] Tarifa correctamente vinculada al Alojamiento.\n";
} else {
    echo "   [FAIL] Tarifa NO vinculada correctamente (Esperado: $room_id, Encontrado: $linked_room_id)\n";
}

// 3.2 Verificar Tarifa -> Temporada
$saved_prices = get_post_meta( $rate_id, '_tureserva_precios', true );
if ( isset($saved_prices[0]['temporada_id']) && $saved_prices[0]['temporada_id'] == $season_id ) {
    echo "   [PASS] Tarifa correctamente vinculada a la Temporada.\n";
} else {
    echo "   [FAIL] Tarifa NO vinculada correctamente a la Temporada.\n";
}

// 3.3 Verificar Reserva -> Alojamiento
$booking_room_id = get_post_meta( $booking_id, '_tureserva_alojamiento_id', true );
if ( $booking_room_id == $room_id ) {
    echo "   [PASS] Reserva correctamente vinculada al Alojamiento.\n";
} else {
    echo "   [FAIL] Reserva NO vinculada correctamente (Esperado: $room_id, Encontrado: $booking_room_id)\n";
}

// 4. Limpieza
echo "\n4. Limpiando Datos de Prueba...\n";
wp_delete_post( $booking_id, true );
wp_delete_post( $rate_id, true );
wp_delete_post( $service_id, true );
wp_delete_post( $room_id, true );
wp_delete_post( $season_id, true );
echo "   [OK] Datos eliminados.\n";

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
