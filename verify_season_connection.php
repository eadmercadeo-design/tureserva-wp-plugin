<?php
require_once('wp-load.php');

function tureserva_verify_connection() {
    echo "Starting Verification...\n";

    // 1. Create a Season
    $season_id = wp_insert_post([
        'post_title' => 'Test Season ' . uniqid(),
        'post_type' => 'temporada',
        'post_status' => 'publish'
    ]);
    update_post_meta($season_id, '_tureserva_fecha_inicio', '2025-06-01');
    update_post_meta($season_id, '_tureserva_fecha_fin', '2025-06-30');
    update_post_meta($season_id, '_tureserva_factor_precio', '1.5'); // 50% increase
    echo "Created Season ID: $season_id (June 2025, Factor 1.5)\n";

    // 2. Create an Accommodation
    $accommodation_id = wp_insert_post([
        'post_title' => 'Test Accommodation ' . uniqid(),
        'post_type' => 'trs_alojamiento',
        'post_status' => 'publish'
    ]);
    update_post_meta($accommodation_id, '_tureserva_precio_base', '100');
    echo "Created Accommodation ID: $accommodation_id (Base Price 100)\n";

    // 3. Test Fallback Connection (No Rate, just Season Factor)
    echo "\nTesting Fallback Connection (Season Factor)...\n";
    $price_data = tureserva_calcular_precio_total($accommodation_id, '2025-06-10', '2025-06-12'); // 2 nights
    // Expected: 100 * 1.5 * 2 = 300
    if ($price_data['total'] == 300) {
        echo "SUCCESS: Fallback connection works. Total: " . $price_data['total'] . " (Expected 300)\n";
        echo "Season used: " . $price_data['temporada'] . "\n";
    } else {
        echo "FAILURE: Fallback connection failed. Total: " . $price_data['total'] . " (Expected 300)\n";
    }

    // 4. Create a Rate linking them
    $rate_id = wp_insert_post([
        'post_title' => 'Test Rate ' . uniqid(),
        'post_type' => 'tureserva_tarifa',
        'post_status' => 'publish'
    ]);
    update_post_meta($rate_id, '_tureserva_alojamiento_id', $accommodation_id);
    
    // Rate structure
    $precios = [
        [
            'temporada_id' => $season_id,
            'precio_base' => 200, // Specific price for this season
            'adultos' => 2,
            'ninos' => 0,
            'variables' => []
        ]
    ];
    update_post_meta($rate_id, '_tureserva_precios', $precios);
    // Set global dates (as logic does)
    update_post_meta($rate_id, '_tureserva_fecha_inicio', '2025-06-01');
    update_post_meta($rate_id, '_tureserva_fecha_fin', '2025-06-30');

    echo "\nCreated Rate ID: $rate_id (Price 200 for Season)\n";

    // 5. Test Rate Connection
    echo "Testing Rate Connection...\n";
    $price_data_rate = tureserva_calcular_precio_total($accommodation_id, '2025-06-10', '2025-06-12'); // 2 nights
    // Expected: 200 * 2 = 400
    if ($price_data_rate['total'] == 400) {
        echo "SUCCESS: Rate connection works. Total: " . $price_data_rate['total'] . " (Expected 400)\n";
    } else {
        echo "FAILURE: Rate connection failed. Total: " . $price_data_rate['total'] . " (Expected 400)\n";
    }

    // Cleanup
    wp_delete_post($season_id, true);
    wp_delete_post($accommodation_id, true);
    wp_delete_post($rate_id, true);
}

tureserva_verify_connection();
