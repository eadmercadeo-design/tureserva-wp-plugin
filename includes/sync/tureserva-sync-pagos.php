<?php
/**
 * ==========================================================
 * SYNC: Sincronización de Pagos COMPLETADOS con Supabase
 * ==========================================================
 * Envía automáticamente los pagos completados del CPT
 * "tureserva_pagos" a la tabla "tureserva_pagos" en Supabase.
 * Registra también el resultado en 'tureserva_sync_log'.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🚀 Sincronizar pago al guardar o actualizar
// =======================================================
add_action('save_post_tureserva_pagos', 'tureserva_sync_pago_supabase', 20, 2);

function tureserva_sync_pago_supabase($post_id, $post) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status !== 'publish') return;

    // 🧾 Obtener estado del pago
    $estado = strtolower(get_post_meta($post_id, '_tureserva_pago_estado', true) ?: 'pendiente');

    // 🚫 Solo sincronizar si el estado es "completado"
    if ($estado !== 'completado') {
        error_log("ℹ Pago #$post_id omitido (estado: $estado)");
        return;
    }

    // =======================================================
    // 🔑 Credenciales Supabase
    // =======================================================
    $supabase_url  = 'https://qsfqdyptjwzijsbcatyu.supabase.co';
    $supabase_key  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFzZnFkeXB0and6aWpzYmNhdHl1Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MTg0NTkwOSwiZXhwIjoyMDc3NDIxOTA5fQ._9P1Wn3A-d798ii3ZB49L9WeotoI2OMEs6OVdUy9RE8'; // <-- aquí pega tu clave completa en tu entorno local
    $table_pagos   = 'tureserva_pagos';
    $table_log     = 'tureserva_sync_log';

    // =======================================================
    // 📦 Datos del pago
    // =======================================================
    $meta = get_post_meta($post_id);
    $codigo   = $meta['_tureserva_pago_id'][0] ?? '';
    $cliente  = $meta['_tureserva_fact_nombre'][0] ?? '';
    $monto    = floatval($meta['_tureserva_pago_monto'][0] ?? 0);
    $moneda   = strtoupper($meta['_tureserva_pago_moneda'][0] ?? 'USD');
    $fecha    = get_the_date('c', $post_id);

    $data = [
        'codigo'   => $codigo,
        'cliente'  => $cliente,
        'monto'    => $monto,
        'moneda'   => $moneda,
        'estado'   => ucfirst($estado),
        'fecha'    => $fecha,
    ];

    // =======================================================
    // 🔍 Verificar conexión antes de enviar
    // =======================================================
    $ping = wp_remote_get("$supabase_url/rest/v1/$table_pagos?select=id&limit=1", [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => "Bearer $supabase_key"
        ],
        'timeout' => 10
    ]);

    if (is_wp_error($ping)) {
        error_log('❌ No se pudo conectar con Supabase: ' . $ping->get_error_message());
        return;
    }

    // =======================================================
    // 🔄 Enviar datos a Supabase
    // =======================================================
    $response = wp_remote_post("$supabase_url/rest/v1/$table_pagos", [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key, // ✅ CORREGIDO
            'Content-Type'  => 'application/json',
            'Prefer'        => 'resolution=merge-duplicates'
        ],
        'body' => json_encode([$data]),
        'timeout' => 20
    ]);

// =======================================================
// 🧠 Registrar resultado detallado (modo depuración)
// =======================================================
$success = false;
$message = '';

if (is_wp_error($response)) {
    $message = '❌ Error HTTP: ' . $response->get_error_message();
} else {
    $status = wp_remote_retrieve_response_code($response);
    $body   = wp_remote_retrieve_body($response);

    // Log completo para diagnóstico
    error_log('--- SUPABASE RESPONSE ---');
    error_log("HTTP Status: $status");
    error_log("Body: $body");

    if ($status >= 200 && $status < 300) {
        $success = true;
        $message = "✅ Pago sincronizado correctamente ($codigo)";
    } elseif ($status === 401) {
        $message = "🔒 Error 401: Clave Supabase incorrecta o sin permisos.";
    } elseif ($status === 404) {
        $message = "📂 Error 404: Tabla '$table_pagos' no encontrada en Supabase.";
    } elseif ($status === 400) {
        $message = "⚠️ Error 400: Datos mal formateados. Revisa los campos enviados.";
    } else {
        $message = "⚠️ Error desconocido ($status): $body";
    }
}

// Mostrar en el log
error_log($message);

// =======================================================
// 🗂 Registrar log en Supabase
// =======================================================
$log_entry = [
    'entidad' => 'pago',
    'codigo'  => $codigo,
    'estado'  => $success ? 'éxito' : 'error',
    'detalle' => $message,
    'fecha'   => current_time('mysql'),
];

wp_remote_post("$supabase_url/rest/v1/$table_log", [
    'headers' => [
        'apikey'        => $supabase_key,
        'Authorization' => "Bearer $supabase_key",
        'Content-Type'  => 'application/json'
    ],
    'body' => json_encode([$log_entry]),
    'timeout' => 15
]);

if ($success) {
    update_post_meta($post_id, '_tureserva_sync_status', 'sincronizado');
    error_log("✔ Registro $codigo sincronizado correctamente y marcado como 'sincronizado'.");
} else {
    update_post_meta($post_id, '_tureserva_sync_status', 'error');
    error_log("❌ Fallo al sincronizar el pago $codigo.");
}
