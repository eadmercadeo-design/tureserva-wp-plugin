<?php
/**
 * ==========================================================
 * CORE: Ajustes Globales — TuReserva
 * ==========================================================
 * Define y gestiona los parámetros generales del sistema:
 *  - Moneda y símbolo
 *  - Impuestos
 *  - Formato de fecha
 *  - Idioma del sistema
 *  - Configuración avanzada (en desarrollo)
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// ⚙️ VALORES POR DEFECTO AL ACTIVAR
// =======================================================
add_action( 'tureserva_activated', 'tureserva_ajustes_default' );
function tureserva_ajustes_default() {
    if ( ! get_option( 'tureserva_moneda' ) ) {
        update_option( 'tureserva_moneda', 'USD' ); // Moneda por defecto
    }
    if ( ! get_option( 'tureserva_simbolo_moneda' ) ) {
        update_option( 'tureserva_simbolo_moneda', '$' );
    }
    if ( ! get_option( 'tureserva_impuesto' ) ) {
        update_option( 'tureserva_impuesto', 0.07 ); // 7% por defecto
    }
    if ( ! get_option( 'tureserva_formato_fecha' ) ) {
        update_option( 'tureserva_formato_fecha', 'd/m/Y' );
    }
    if ( ! get_option( 'tureserva_idioma' ) ) {
        update_option( 'tureserva_idioma', 'es_ES' );
    }
}

// =======================================================
// 🧾 FUNCIONES DE ACCESO RÁPIDO A LOS AJUSTES
// =======================================================

function tureserva_get_moneda() {
    return get_option( 'tureserva_moneda', 'USD' );
}

function tureserva_get_simbolo_moneda() {
    return get_option( 'tureserva_simbolo_moneda', '$' );
}

function tureserva_get_impuesto() {
    return floatval( get_option( 'tureserva_impuesto', 0.07 ) );
}

function tureserva_get_formato_fecha() {
    return get_option( 'tureserva_formato_fecha', 'd/m/Y' );
}

function tureserva_get_idioma() {
    return get_option( 'tureserva_idioma', 'es_ES' );
}

// =======================================================
// 🧮 FUNCIÓN UTILITARIA PARA DAR FORMATO A MONEDAS
// =======================================================
function tureserva_formatear_moneda( $cantidad ) {
    $simbolo = tureserva_get_simbolo_moneda();
    $moneda  = tureserva_get_moneda();
    $formatted = number_format_i18n( $cantidad, 2 );
    return "{$simbolo}{$formatted} {$moneda}";
}

// =======================================================
// 📦 ENDPOINT AJAX PARA ACTUALIZAR AJUSTES (ADMIN)
// =======================================================
add_action( 'wp_ajax_tureserva_guardar_ajustes', 'tureserva_guardar_ajustes' );

function tureserva_guardar_ajustes() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );

    check_ajax_referer( 'tureserva_settings_nonce', 'nonce' );

    $campos = array(
        'tureserva_moneda',
        'tureserva_simbolo_moneda',
        'tureserva_impuesto',
        'tureserva_formato_fecha',
        'tureserva_idioma'
    );

    foreach ( $campos as $campo ) {
        if ( isset( $_POST[$campo] ) ) {
            update_option( $campo, sanitize_text_field( $_POST[$campo] ) );
        }
    }

    wp_send_json_success( array( 'mensaje' => '✅ Ajustes guardados correctamente.' ) );
}
