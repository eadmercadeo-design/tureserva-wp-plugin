<?php
/**
 * ==========================================================
 * CORE: Autenticación API — TuReserva
 * ==========================================================
 * Proporciona seguridad a la API REST mediante tokens (API Keys).
 * - Generación y gestión de claves desde el panel admin.
 * - Validación automática en todos los endpoints REST.
 * - Permite integraciones seguras con Netlify, Supabase o apps móviles.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔐 OPCIÓN DE ALMACENAMIENTO DE TOKENS
// =======================================================
// Se guardan como array asociativo en la tabla wp_options
// Ejemplo: [ 'token_id' => ['key'=>'abcdef1234...', 'nombre'=>'Frontend Netlify', 'creado'=>'2025-10-29'] ]
// =======================================================
define( 'TURE_SERVA_AUTH_OPTION', 'tureserva_api_tokens' );

// =======================================================
// 🧰 FUNCIÓN AUXILIAR: Generar token seguro
// =======================================================
function tureserva_generar_token() {
    return bin2hex( random_bytes(20) ); // 40 caracteres
}

// =======================================================
// 📦 CREAR NUEVO TOKEN
// =======================================================
function tureserva_crear_token( $nombre = 'Nuevo Token' ) {
    $tokens = get_option( TURE_SERVA_AUTH_OPTION, array() );

    $nuevo_id = uniqid('token_', true);
    $nuevo_token = array(
        'key'     => tureserva_generar_token(),
        'nombre'  => sanitize_text_field( $nombre ),
        'creado'  => current_time( 'mysql' ),
        'activo'  => true,
    );

    $tokens[$nuevo_id] = $nuevo_token;
    update_option( TURE_SERVA_AUTH_OPTION, $tokens );

    return $nuevo_token;
}

// =======================================================
// ❌ REVOCAR TOKEN
// =======================================================
function tureserva_revocar_token( $token_key ) {
    $tokens = get_option( TURE_SERVA_AUTH_OPTION, array() );
    foreach ( $tokens as $id => $t ) {
        if ( $t['key'] === $token_key ) {
            $tokens[$id]['activo'] = false;
            update_option( TURE_SERVA_AUTH_OPTION, $tokens );
            return true;
        }
    }
    return false;
}

// =======================================================
// ✅ VALIDAR TOKEN RECIBIDO EN PETICIÓN API
// =======================================================
function tureserva_validar_token( $token_key ) {
    if ( empty( $token_key ) ) return false;

    $tokens = get_option( TURE_SERVA_AUTH_OPTION, array() );
    foreach ( $tokens as $t ) {
        if ( $t['key'] === $token_key && $t['activo'] ) {
            return true;
        }
    }
    return false;
}

// =======================================================
// 🔒 VERIFICACIÓN AUTOMÁTICA PARA ENDPOINTS
// =======================================================
function tureserva_auth_permission_callback( $request ) {
    $auth_header = $request->get_header( 'authorization' );
    if ( ! $auth_header ) {
        return new WP_Error( 'no_token', 'Token de autenticación faltante.', array( 'status' => 401 ) );
    }

    // Permitir formato: "Bearer TOKEN"
    if ( stripos( $auth_header, 'Bearer ' ) === 0 ) {
        $token = trim( str_ireplace( 'Bearer ', '', $auth_header ) );
    } else {
        $token = trim( $auth_header );
    }

    if ( ! tureserva_validar_token( $token ) ) {
        return new WP_Error( 'token_invalido', 'Token inválido o revocado.', array( 'status' => 403 ) );
    }

    return true;
}

// =======================================================
// 🔧 ENDPOINT ADMIN (AJAX) PARA CREAR Y REVOCAR TOKENS
// =======================================================
add_action( 'wp_ajax_tureserva_crear_token', 'tureserva_ajax_crear_token' );
add_action( 'wp_ajax_tureserva_revocar_token', 'tureserva_ajax_revocar_token' );

function tureserva_ajax_crear_token() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );
    $nombre = sanitize_text_field( $_POST['nombre'] ?? 'Nuevo Token' );
    $token = tureserva_crear_token( $nombre );
    wp_send_json_success( $token );
}

function tureserva_ajax_revocar_token() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );
    $key = sanitize_text_field( $_POST['key'] ?? '' );
    $ok = tureserva_revocar_token( $key );
    wp_send_json_success( array( 'revocado' => $ok ) );
}
