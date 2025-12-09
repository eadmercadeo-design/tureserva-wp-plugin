<?php
// Script de Verificación - Etapa 1: Notificaciones
// Ejecutar con: php verify_notifications_stage1.php

// 1. Cargar WordPress Environment
// Asumimos estructura: /app/public/wp-content/plugins/tureserva/verify_notifications_stage1.php
// WP-Load está en: /app/public/wp-load.php (4 niveles arriba)
$wp_load_path = dirname(__FILE__, 4) . '/wp-load.php';

if (!file_exists($wp_load_path)) {
    die("❌ Error: No se encuentra wp-load.php en: $wp_load_path\n");
}
require_once $wp_load_path;

echo "✅ WordPress cargado correctamente.\n";
echo "---------------------------------------------------\n";

// 2. Mockear wp_mail para interceptar envíos
// Usamos el filtro 'pre_wp_mail' (disponible desde WP 5.7) o 'wp_mail' hook.
// Mejor usamos una clase almacenadora.

$captured_emails = [];

add_filter('wp_mail', function($args) use (&$captured_emails) {
    echo "📧 [INTERCEPTADO] Email detectado:\n";
    echo "   To: " . (is_array($args['to']) ? implode(',', $args['to']) : $args['to']) . "\n";
    echo "   Subject: " . $args['subject'] . "\n";
    echo "   Body Length: " . strlen($args['message']) . " chars\n";
    
    $captured_emails[] = $args;
    
    // Retornamos false para que NO intente enviar de verdad durante la prueba (opcional)
    // Pero para testear que wp_mail devuelve true, tendríamos que retornar los args.
    // Dejemos que fluya, pero ya capturamos los datos.
    return $args;
});

// Configurar correos de prueba
update_option('tureserva_admin_email', 'admin_test@example.com, soporte@example.com');
update_option('tureserva_from_name', 'Hotel Test');
update_option('tureserva_from_email', 'no-reply@hoteltest.com');

echo "⚙️ Configuración inyectada temporalmente:\n";
echo "   Admin Email: " . get_option('tureserva_admin_email') . "\n";
echo "---------------------------------------------------\n";

// 3. Simular Acción: Nueva Reserva
echo "🔄 Simulando evento 'tureserva_reserva_creada'...\n";

$mock_id = 9999; // ID ficticio
$mock_data = [
    'alojamiento_id' => 123,
    'check_in'       => '2025-12-25',
    'check_out'      => '2025-12-30',
    'precio_total'   => 500.00,
    'cliente'        => [
        'nombre'   => 'Juan Pérez',
        'email'    => 'cliente_test@example.com',
        'telefono' => '555-0000'
    ]
];

// Necesitamos mockear get_the_title(123) ?
// WP intentará buscar el post 123. Si no existe, devolverá título vacío o ID.
// No importa tanto para la prueba de envío, solo queremos ver que NO CRASHEA y que envía.

do_action('tureserva_reserva_creada', $mock_id, $mock_data);

echo "---------------------------------------------------\n";
echo "📊 RESULTADOS:\n";

$found_admin = false;
$found_client = false;

foreach ($captured_emails as $mail) {
    // Check Admin
    $to = is_array($mail['to']) ? implode(',', $mail['to']) : $mail['to'];
    if (strpos($to, 'admin_test@example.com') !== false) {
        $found_admin = true;
    }
    // Check Client
    if (strpos($to, 'cliente_test@example.com') !== false) {
        $found_client = true;
    }
}

if ($found_admin) {
    echo "✅ [ÉXITO] Correo al Administrador capturado.\n";
} else {
    echo "❌ [FALLO] No se detectó correo al Administrador.\n";
}

if ($found_client) {
    echo "✅ [ÉXITO] Correo al Cliente capturado.\n";
} else {
    echo "❌ [FALLO] No se detectó correo al Cliente.\n";
}

if ($found_admin && $found_client) {
    echo "\n🏆 PRUEBA DE NOTIFICACIONES: SUPERADA\n";
    exit(0);
} else {
    echo "\n💀 PRUEBA DE NOTIFICACIONES: FALLIDA\n";
    exit(1);
}
