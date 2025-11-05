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
    // 🔑 Credenciales Supabase (desde opciones de WordPress)
    // =======================================================
    $supabase_url  = get_option('tureserva_supabase_url');
    $supabase_key  = get_option('tureserva_supabase_key');
    $table_pagos   = 'tureserva_pagos';
    $table_log     = 'tureserva_sync_log';

    // Validar que existan las credenciales
    if (empty($supabase_url) || empty($supabase_key)) {
        error_log("ℹ Pago #$post_id omitido: Falta configuración de Supabase.");
        return;
    }

    // Normalizar URL (eliminar /rest/v1 si está incluido)
    $supabase_url = rtrim($supabase_url, '/');
    if (strpos($supabase_url, '/rest/v1') !== false) {
        $supabase_url = str_replace('/rest/v1', '', $supabase_url);
    }

    // =======================================================
    // 📦 Datos del pago
    // =======================================================
    $meta = get_post_meta($post_id);
    $codigo   = $meta['_tureserva_pago_id'][0] ?? get_post_meta($post_id, '_tureserva_pago_codigo', true) ?: '';
    $cliente  = trim(($meta['_tureserva_fact_nombre'][0] ?? '') . ' ' . ($meta['_tureserva_fact_apellido'][0] ?? ''));
    $cliente  = $cliente ?: ($meta['_tureserva_cliente_nombre'][0] ?? '');
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
    $ping_url = trailingslashit($supabase_url) . "rest/v1/$table_pagos?select=id&limit=1";
    $ping = wp_remote_get($ping_url, [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => "Bearer $supabase_key"
        ],
        'timeout' => 10
    ]);

    if (is_wp_error($ping)) {
        error_log('[TuReserva Sync Pagos] ❌ No se pudo conectar con Supabase: ' . $ping->get_error_message());
        update_post_meta($post_id, '_tureserva_sync_status', 'error');
        return;
    }

    // =======================================================
    // 🔄 Enviar datos a Supabase
    // =======================================================
    $endpoint_url = trailingslashit($supabase_url) . "rest/v1/$table_pagos";
    $response = wp_remote_post($endpoint_url, [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=representation' // Para obtener el registro insertado
        ],
        'body' => wp_json_encode([$data]),
        'timeout' => 20
    ]);

// =======================================================
// 🧠 Registrar resultado detallado (modo depuración)
// =======================================================
$success = false;
$message = '';

if (is_wp_error($response)) {
    $message = '❌ Error HTTP: ' . $response->get_error_message();
    error_log('[TuReserva Sync Pagos] ' . $message);
} else {
    $status = wp_remote_retrieve_response_code($response);
    $body   = wp_remote_retrieve_body($response);

    // Log completo para diagnóstico (solo si WP_DEBUG está activo)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[TuReserva Sync Pagos] --- SUPABASE RESPONSE ---');
        error_log("[TuReserva Sync Pagos] HTTP Status: $status");
        error_log("[TuReserva Sync Pagos] Body: " . substr($body, 0, 500));
    }

    if ($status >= 200 && $status < 300) {
        $success = true;
        $message = "✅ Pago sincronizado correctamente ($codigo)";
        error_log("[TuReserva Sync Pagos] $message");
    } elseif ($status === 401) {
        $message = "🔒 Error 401: Clave Supabase incorrecta o sin permisos.";
        error_log("[TuReserva Sync Pagos] $message");
    } elseif ($status === 404) {
        $message = "📂 Error 404: Tabla '$table_pagos' no encontrada en Supabase.";
        error_log("[TuReserva Sync Pagos] $message");
    } elseif ($status === 400) {
        $message = "⚠️ Error 400: Datos mal formateados. Revisa los campos enviados.";
        error_log("[TuReserva Sync Pagos] $message - Body: " . substr($body, 0, 200));
    } else {
        $message = "⚠️ Error desconocido ($status): " . substr($body, 0, 200);
        error_log("[TuReserva Sync Pagos] $message");
    }
}

// =======================================================
// 🗂 Registrar log en Supabase
// =======================================================
$log_entry = [
    'entidad' => 'pago',
    'codigo'  => $codigo ?: "WP-$post_id",
    'estado'  => $success ? 'éxito' : 'error',
    'detalle' => $message,
    'fecha'   => current_time('mysql'),
];

$log_url = trailingslashit($supabase_url) . "rest/v1/$table_log";
wp_remote_post($log_url, [
    'headers' => [
        'apikey'        => $supabase_key,
        'Authorization' => "Bearer $supabase_key",
        'Content-Type'  => 'application/json'
    ],
    'body' => wp_json_encode([$log_entry]),
    'timeout' => 15
]);

// Actualizar estado de sincronización en el post
if ($success) {
    update_post_meta($post_id, '_tureserva_sync_status', 'sincronizado');
    update_post_meta($post_id, '_tureserva_sync_fecha', current_time('mysql'));
    error_log("[TuReserva Sync Pagos] ✔ Registro $codigo sincronizado correctamente y marcado como 'sincronizado'.");
} else {
    update_post_meta($post_id, '_tureserva_sync_status', 'error');
    update_post_meta($post_id, '_tureserva_sync_error', $message);
    error_log("[TuReserva Sync Pagos] ❌ Fallo al sincronizar el pago $codigo.");
}
} // ✅ Cierre de la función tureserva_sync_pago_supabase
