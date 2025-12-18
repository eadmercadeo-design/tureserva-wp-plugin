<?php
// Script de Verificación de Cupones - TuReserva
// Ejecutar con: wp eval-file verification_test_coupons.php

if ( ! defined( 'ABSPATH' ) ) {
    require_once( dirname( __FILE__ ) . '/wp-load.php' );
}

echo "========================================\n";
echo "🎫 VERIFICACIÓN DE MÓDULO DE CUPONES\n";
echo "========================================\n";

// 1. Crear un Cupón de Prueba
$code = 'TEST' . rand(100,999);
echo "\n1. Creando cupón de prueba '$code' (10% de descuento)...\n";

$coupon_id = wp_insert_post([
    'post_type'   => 'tureserva_cupon',
    'post_title'  => $code,
    'post_status' => 'publish',
    'meta_input'  => [
        '_tureserva_tipo_cupon' => 'percentage',
        '_tureserva_monto'      => 10,
        '_tureserva_limite_uso' => 10,
        '_tureserva_uso_actual' => 0
    ]
]);

if ( is_wp_error($coupon_id) ) {
    echo "❌ Error al crear cupón: " . $coupon_id->get_error_message() . "\n";
    exit;
}
echo "✅ Cupón creado con ID: $coupon_id\n";

// 2. Validar Cupón
echo "\n2. Validando cupón...\n";
$validation = tureserva_validate_coupon( $code, [] );
if ( is_wp_error( $validation ) ) {
    echo "❌ Validación fallida: " . $validation->get_error_message() . "\n";
} else {
    echo "✅ Validación exitosa.\n";
}

// 3. Crear Reserva simula con Cupón
echo "\n3. Simulando creación de reserva con cupón...\n";

// Necesitamos un ID de alojamiento válido.
$alojamientos = get_posts(['post_type' => 'trs_alojamiento', 'posts_per_page' => 1]);
if ( empty($alojamientos) ) {
    echo "❌ No hay alojamientos para probar. Crea uno primero.\n";
    exit;
}
$alojamiento_id = $alojamientos[0]->ID;

// Mock datos
$reserva_data = [
    'alojamiento_id' => $alojamiento_id,
    'check_in'       => date('Y-m-d', strtotime('+5 days')),
    'check_out'      => date('Y-m-d', strtotime('+7 days')),
    'huespedes'      => ['adultos' => 2, 'ninos' => 0],
    'cliente'        => [
        'nombre' => 'Tester Coupon',
        'email'  => 'test@coupon.com',
        'telefono' => '123456',
        'notas' => 'Prueba automated'
    ],
    'coupon_code'    => $code
];

$reserva_id = tureserva_crear_reserva( $reserva_data );

if ( is_wp_error( $reserva_id ) ) {
    echo "❌ Error al crear reserva: " . $reserva_id->get_error_message() . "\n";
} else {
    echo "✅ Reserva creada con ID: $reserva_id\n";
    
    // Verificar metadatos
    $saved_code = get_post_meta( $reserva_id, '_tureserva_coupon_code', true );
    $saved_discount = get_post_meta( $reserva_id, '_tureserva_discount_amount', true );
    $precio_total = get_post_meta( $reserva_id, '_tureserva_precio_total', true );
    $desglose = get_post_meta( $reserva_id, '_tureserva_desglose_precio', true );
    
    echo "   - Código guardado: " . ($saved_code === $code ? 'CORRECTO' : 'INCORRECTO') . " ($saved_code)\n";
    echo "   - Descuento guardado: $saved_discount\n";
    echo "   - Precio Total final: $precio_total\n";
    echo "   - Subtotal Original: " . ($desglose['subtotal'] + $saved_discount) . " (aprox)\n"; // Subtotal ya tiene descuento aplicado en la lógica, el desglose guarda el estado final
}

// 4. Verificar incremento de uso
echo "\n4. Verificando contador de uso...\n";
$uso_actual = get_post_meta( $coupon_id, '_tureserva_uso_actual', true );
echo "   - Uso actual: $uso_actual " . ($uso_actual == 1 ? '✅ CORRECTO' : '❌ INCORRECTO') . "\n";

// Limpieza (opcional, borrar reserva y cupón)
wp_delete_post($coupon_id, true);
// wp_delete_post($reserva_id, true); // Dejamos la reserva para inspección manual si se quiere

echo "\n🏁 Prueba finalizada.\n";
