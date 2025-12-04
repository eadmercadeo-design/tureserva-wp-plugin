<?php
// Script de verificación para el widget de dashboard

// Cargar WordPress
// Ajustar path relativo desde plugins/tureserva/verify_dashboard_widget.php a wp-load.php (root)
require_once __DIR__ . '/../../../../wp-load.php';

// Incluir el archivo del widget
require_once __DIR__ . '/admin/dashboard/widgets/estado-alojamientos.php';

echo "=== INICIO VERIFICACIÓN WIDGET ===\n";

// Simular renderizado
ob_start();
tureserva_widget_estado_alojamientos_render();
$output = ob_get_clean();

echo "Output length: " . strlen($output) . " bytes\n";

if (strpos($output, 'trs-status-table') !== false) {
    echo "✅ Tabla encontrada.\n";
} else {
    echo "❌ Tabla NO encontrada.\n";
}

if (strpos($output, 'Fecha:') !== false) {
    echo "✅ Fecha encontrada.\n";
} else {
    echo "❌ Fecha NO encontrada.\n";
}

// Verificar si hay alojamientos
$alojamientos = get_posts(array('post_type' => 'trs_alojamiento', 'posts_per_page' => 1));
if (empty($alojamientos)) {
    echo "⚠️ No hay alojamientos para probar. Se recomienda crear uno.\n";
} else {
    echo "✅ Hay alojamientos en el sistema.\n";
}

echo "=== FIN VERIFICACIÓN ===\n";
