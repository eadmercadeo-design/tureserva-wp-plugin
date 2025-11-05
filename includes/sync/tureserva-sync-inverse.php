<?php
/**
 * ==========================================================
 * SYNC: Sincronización Inversa (Descarga desde Supabase)
 * ==========================================================
 * Descarga pagos desde Supabase y los actualiza en WordPress
 * si existen nuevos registros o cambios.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 📥 SINCRONIZACIÓN INVERSA: Descargar pagos desde Supabase
// =======================================================
function tureserva_sync_pagos_from_supabase($limit = 50) {
    
    $supabase_url = get_option('tureserva_supabase_url');
    $supabase_key = get_option('tureserva_supabase_key');

    if (empty($supabase_url) || empty($supabase_key)) {
        error_log('[TuReserva Sync Inverse] ❌ Falta configuración de Supabase.');
        return false;
    }

    // Normalizar URL
    $supabase_url = rtrim($supabase_url, '/');
    if (strpos($supabase_url, '/rest/v1') !== false) {
        $supabase_url = str_replace('/rest/v1', '', $supabase_url);
    }

    $endpoint = trailingslashit($supabase_url) . "rest/v1/tureserva_pagos?order=fecha.desc&limit=$limit";

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
        ],
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        error_log('[TuReserva Sync Inverse] ❌ Error de conexión: ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        error_log("[TuReserva Sync Inverse] ❌ Error HTTP $code");
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body)) {
        error_log('[TuReserva Sync Inverse] ❌ Respuesta inválida desde Supabase.');
        return false;
    }

    $count_created = 0;
    $count_updated = 0;
    $errors = 0;

    foreach ($body as $pago_supabase) {
        
        // Buscar pago existente por código
        $codigo = sanitize_text_field($pago_supabase['codigo'] ?? '');
        if (empty($codigo)) {
            $errors++;
            continue;
        }

        $existing = get_posts([
            'post_type' => 'tureserva_pagos',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_tureserva_pago_id',
                    'value' => $codigo,
                    'compare' => '='
                ]
            ]
        ]);

        $post_data = [
            'post_type' => 'tureserva_pagos',
            'post_status' => 'publish',
            'meta_input' => [
                '_tureserva_pago_id' => $codigo,
                '_tureserva_pago_monto' => floatval($pago_supabase['monto'] ?? 0),
                '_tureserva_pago_moneda' => strtoupper($pago_supabase['moneda'] ?? 'USD'),
                '_tureserva_pago_estado' => strtolower($pago_supabase['estado'] ?? 'pendiente'),
                '_tureserva_fact_nombre' => sanitize_text_field($pago_supabase['cliente'] ?? ''),
                '_tureserva_sync_status' => 'sincronizado',
                '_tureserva_sync_fecha' => current_time('mysql'),
                '_tureserva_sync_source' => 'supabase',
            ]
        ];

        if (!empty($existing)) {
            // Actualizar existente
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data);
            if ($post_id && !is_wp_error($post_id)) {
                $count_updated++;
            } else {
                $errors++;
            }
        } else {
            // Crear nuevo
            $post_data['post_title'] = 'Pago ' . $codigo;
            $post_id = wp_insert_post($post_data);
            if ($post_id && !is_wp_error($post_id)) {
                $count_created++;
            } else {
                $errors++;
            }
        }
    }

    $resultado = [
        'created' => $count_created,
        'updated' => $count_updated,
        'errors' => $errors,
        'total' => count($body)
    ];

    error_log(sprintf(
        '[TuReserva Sync Inverse] ✅ Sincronización completada: %d creados, %d actualizados, %d errores de %d totales.',
        $count_created,
        $count_updated,
        $errors,
        count($body)
    ));

    return $resultado;
}

// =======================================================
// 🔄 AJAX: Sincronización inversa manual
// =======================================================
add_action('wp_ajax_tureserva_sync_pagos_from_supabase', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos suficientes.');
    }

    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
    $resultado = tureserva_sync_pagos_from_supabase($limit);

    if ($resultado !== false) {
        $mensaje = sprintf(
            '✅ Sincronización inversa completada: %d creados, %d actualizados, %d errores de %d totales.',
            $resultado['created'],
            $resultado['updated'],
            $resultado['errors'],
            $resultado['total']
        );
        wp_send_json_success($mensaje);
    } else {
        wp_send_json_error('Error al sincronizar desde Supabase.');
    }
});

