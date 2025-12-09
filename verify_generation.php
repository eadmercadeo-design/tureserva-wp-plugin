<?php
// Script de verificación para la generación de alojamientos
require_once('../../../wp-load.php');

// 1. Crear un alojamiento "base"
$base_id = wp_insert_post([
    'post_type' => TURESERVA_CPT_ALOJAMIENTO,
    'post_title' => 'Alojamiento Base Test',
    'post_status' => 'publish',
]);
update_post_meta($base_id, '_tureserva_precio', 100);
update_post_meta($base_id, '_tureserva_capacidad', 4);

echo "Alojamiento base creado: ID $base_id\n";

// 2. Simular la función de generación
if (function_exists('tureserva_generar_alojamientos')) {
    ob_start(); // Capturar output HTML
    tureserva_generar_alojamientos(2, $base_id, 'Copia Test');
    $output = ob_get_clean();
    echo "Función ejecutada.\n";
} else {
    echo "Error: Función tureserva_generar_alojamientos no encontrada.\n";
    exit;
}

// 3. Verificar resultados
$copias = get_posts([
    'post_type' => TURESERVA_CPT_ALOJAMIENTO,
    's' => 'Copia Test',
    'post_status' => 'publish'
]);

echo "Se encontraron " . count($copias) . " copias.\n";

foreach ($copias as $copia) {
    $precio = get_post_meta($copia->ID, '_tureserva_precio', true);
    $capacidad = get_post_meta($copia->ID, '_tureserva_capacidad', true);
    echo "- Copia ID {$copia->ID}: Precio=$precio, Capacidad=$capacidad\n";
    
    if ($precio == 100 && $capacidad == 4) {
        echo "  ✅ Metadatos correctos.\n";
    } else {
        echo "  ❌ Metadatos incorrectos.\n";
    }
    
    // Limpieza
    wp_delete_post($copia->ID, true);
}

// Limpieza base
wp_delete_post($base_id, true);
echo "Limpieza completada.\n";
