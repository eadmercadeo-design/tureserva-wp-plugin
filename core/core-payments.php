<?php
/**
 * ==========================================================
 * CORE: Gestión y Procesamiento de Pagos — TuReserva
 * ==========================================================
 * Combina:
 *  - Lógica general de guardado y actualización de metadatos
 *  - Integración con Stripe (creación de cargos)
 *  - Registro interno y logs de cambios
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ CONFIGURACIÓN POR DEFECTO DE STRIPE
// =======================================================
if ( ! function_exists( 'tureserva_payments_default_options' ) ) {
    function tureserva_payments_default_options() {
        if ( ! get_option( 'tureserva_stripe_secret_key' ) ) {
            update_option( 'tureserva_stripe_secret_key', '' );
        }
        if ( ! get_option( 'tureserva_stripe_public_key' ) ) {
            update_option( 'tureserva_stripe_public_key', '' );
        }
        if ( ! get_option( 'tureserva_stripe_mode' ) ) {
            update_option( 'tureserva_stripe_mode', 'test' ); // test | live
        }
    }
}
add_action( 'tureserva_activated', 'tureserva_payments_default_options' );

// =======================================================
// 💾 GUARDADO GENERAL DE METADATOS DE PAGOS
// =======================================================
add_action('save_post_tureserva_pagos', 'tureserva_save_pago_data', 10, 3);
function tureserva_save_pago_data($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'tureserva_pagos') return;
    if (!current_user_can('edit_post', $post_id)) return;

    // ===============================
    // 💾 Guardar metadatos principales
    // ===============================
    $campos = [
        '_tureserva_pasarela',
        '_tureserva_modo_pasarela',
        '_tureserva_pago_monto',
        '_tureserva_pago_moneda',
        '_tureserva_pago_tipo',
        '_tureserva_pago_id',
        '_tureserva_reserva_id',
        '_tureserva_cliente_nombre',
        '_tureserva_cliente_apellido',
        '_tureserva_cliente_email',
        '_tureserva_cliente_telefono',
        '_tureserva_cliente_pais',
        '_tureserva_cliente_direccion1',
        '_tureserva_cliente_direccion2',
        '_tureserva_cliente_ciudad',
        '_tureserva_cliente_estado',
        '_tureserva_cliente_cp'
    ];

    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    // ===============================
    // 🧾 Generar ID automático PG-XXXX
    // ===============================
    $custom_id = get_post_meta($post_id, '_tureserva_pago_codigo', true);
    if (empty($custom_id)) {
        $nuevo_id = sprintf('PG-%04d', $post_id);
        update_post_meta($post_id, '_tureserva_pago_codigo', $nuevo_id);
        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $nuevo_id
        ]);
    }

    // ===============================
    // 🪶 Registro interno del cambio
    // ===============================
    $log = get_post_meta($post_id, '_tureserva_pago_log', true) ?: [];
    $log[] = [
        'fecha'    => current_time('mysql'),
        'mensaje'  => $update
            ? 'Pago actualizado correctamente.'
            : 'Pago creado correctamente.',
        'usuario'  => wp_get_current_user()->display_name
    ];
    update_post_meta($post_id, '_tureserva_pago_log', $log);
}

// =======================================================
// 💳 CREAR UN PAGO EN STRIPE
// =======================================================
if ( ! function_exists( 'tureserva_create_stripe_payment' ) ) {
    function tureserva_create_stripe_payment( $reserva_id, $amount, $currency = 'usd', $token = '' ) {

        $secret = get_option( 'tureserva_stripe_secret_key' );

        if ( empty( $secret ) ) {
            error_log('[TuReserva Payments] ❌ Stripe no configurado.');
            return false;
        }

        // Petición directa a la API de Stripe
        $response = wp_remote_post( 'https://api.stripe.com/v1/charges', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret,
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'amount'      => intval( $amount * 100 ), // Stripe usa centavos
                'currency'    => strtolower( $currency ),
                'source'      => $token,
                'description' => 'Pago de reserva #' . $reserva_id,
            ),
            'timeout' => 20,
        ));

        // Validación de errores
        if ( is_wp_error( $response ) ) {
            error_log('[TuReserva Payments] ❌ Error de conexión: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Éxito del pago
        if ( $code === 200 && ! empty( $body['status'] ) && $body['status'] === 'succeeded' ) {

            // Guardar datos en la reserva
            update_post_meta( $reserva_id, '_tureserva_pago_estado', 'pagado' );
            update_post_meta( $reserva_id, '_tureserva_pago_id', $body['id'] );
            update_post_meta( $reserva_id, '_tureserva_pago_monto', $amount );
            update_post_meta( $reserva_id, '_tureserva_pago_moneda', $currency );

            // Acción hook personalizable
            do_action( 'tureserva_pago_confirmado', $reserva_id, $body );

            // Registro en historial de pagos (CPT)
            $cliente_nombre = get_post_meta( $reserva_id, '_tureserva_cliente_nombre', true ) ?: 'Cliente';

            $post_id = wp_insert_post( array(
                'post_type'   => 'tureserva_pagos',
                'post_status' => 'publish',
                'post_title'  => 'Pago de reserva #' . $reserva_id,
                'meta_input'  => array(
                    '_tureserva_reserva_id'     => $reserva_id,
                    '_tureserva_cliente_nombre' => $cliente_nombre,
                    '_tureserva_pago_estado'    => 'pagado',
                    '_tureserva_pago_monto'     => $amount,
                    '_tureserva_pago_moneda'    => $currency,
                    '_tureserva_pago_id'        => $body['id'],
                    '_tureserva_metodo'         => 'Stripe',
                    '_tureserva_fecha'          => current_time( 'mysql' ),
                ),
            ));

            if ( $post_id ) {
                error_log('[TuReserva Payments] ✅ Pago registrado (#' . $post_id . ')');
            }

            return true;

        } else {
            // Error en la respuesta de Stripe
            $error_msg = $body['error']['message'] ?? 'Error desconocido en Stripe.';
            error_log('[TuReserva Payments] ❌ ' . $error_msg);
            return false;
        }
    }
}

// =======================================================
// 🧾 MANEJADOR DE PAGOS DESDE AJAX
// =======================================================
if ( ! function_exists( 'tureserva_ajax_procesar_pago' ) ) {
    function tureserva_ajax_procesar_pago() {

        if ( ! isset( $_POST['reserva_id'], $_POST['token'], $_POST['amount'] ) ) {
            wp_send_json_error( 'Datos incompletos.' );
        }

        $reserva_id = intval( $_POST['reserva_id'] );
        $token      = sanitize_text_field( $_POST['token'] );
        $amount     = floatval( $_POST['amount'] );

        $resultado = tureserva_create_stripe_payment( $reserva_id, $amount, 'usd', $token );

        if ( $resultado ) {
            wp_send_json_success( 'Pago procesado con éxito.' );
        } else {
            wp_send_json_error( 'No se pudo procesar el pago.' );
        }
    }
}
add_action( 'wp_ajax_tureserva_procesar_pago', 'tureserva_ajax_procesar_pago' );
add_action( 'wp_ajax_nopriv_tureserva_procesar_pago', 'tureserva_ajax_procesar_pago' );
// ==========================================================
// ☁️ SINCRONIZACIÓN DE PAGOS CON SUPABASE
// ==========================================================
if (!function_exists('tureserva_cloud_sync_payment')) {
    function tureserva_cloud_sync_payment($pago_id) {

        // Recupera las claves guardadas (defínelas en Ajustes Generales o .env)
        $supabase_url  = get_option('tureserva_supabase_url');
        $supabase_key  = get_option('tureserva_supabase_key');

        if (empty($supabase_url) || empty($supabase_key)) {
            error_log('[TuReserva Cloud] ❌ Falta configuración de Supabase.');
            return false;
        }

        // Obtiene los metadatos del pago
        $data = [
            'pago_id'       => $pago_id,
            'codigo'        => get_post_meta($pago_id, '_tureserva_pago_codigo', true),
            'reserva_id'    => get_post_meta($pago_id, '_tureserva_reserva_id', true),
            'estado'        => get_post_meta($pago_id, '_tureserva_pago_estado', true),
            'monto'         => get_post_meta($pago_id, '_tureserva_pago_monto', true),
            'moneda'        => get_post_meta($pago_id, '_tureserva_pago_moneda', true),
            'metodo'        => get_post_meta($pago_id, '_tureserva_metodo', true),
            'cliente'       => trim(get_post_meta($pago_id, '_tureserva_cliente_nombre', true) . ' ' . get_post_meta($pago_id, '_tureserva_cliente_apellido', true)),
            'email'         => get_post_meta($pago_id, '_tureserva_cliente_email', true),
            'fecha'         => get_post_meta($pago_id, '_tureserva_fecha', true) ?: current_time('mysql'),
        ];

        // Envía a Supabase (función o REST endpoint)
        $response = wp_remote_post(
            trailingslashit($supabase_url) . 'rest/v1/tureserva_pagos',
            [
                'headers' => [
                    'apikey'        => $supabase_key,
                    'Authorization' => 'Bearer ' . $supabase_key,
                    'Content-Type'  => 'application/json',
                    'Prefer'        => 'resolution=merge-duplicates'
                ],
                'body'    => wp_json_encode($data),
                'timeout' => 20,
            ]
        );

        if (is_wp_error($response)) {
            error_log('[TuReserva Cloud] ❌ Error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 200 && $code < 300) {
            error_log('[TuReserva Cloud] ✅ Pago sincronizado (#' . $pago_id . ')');
            return true;
        } else {
            error_log('[TuReserva Cloud] ⚠️ Error de sincronización HTTP ' . $code);
            return false;
        }
    }
}

// ==========================================================
// 🔄 Hook: sincronizar cada vez que se guarde un pago
// ==========================================================
add_action('save_post_tureserva_pagos', function($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_status !== 'auto-draft') {
        tureserva_cloud_sync_payment($post_id);
    }
}, 20, 3);
