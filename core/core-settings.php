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
// ⚙️ VALORES POR DEFECTO AL ACTIVAR EL PLUGIN
// =======================================================
add_action( 'tureserva_activated', 'tureserva_ajustes_default' );

function tureserva_ajustes_default() {

    $defaults = array(
        'tureserva_moneda'           => 'USD',
        'tureserva_simbolo_moneda'   => '$',
        'tureserva_impuesto'         => 0.07,
        'tureserva_formato_fecha'    => 'd/m/Y',
        'tureserva_idioma'           => 'es_ES',
    );

    foreach ( $defaults as $opcion => $valor ) {
        if ( ! get_option( $opcion ) ) {
            update_option( $opcion, $valor );
        }
    }
}

// =======================================================
// 🧾 FUNCIONES DE ACCESO RÁPIDO A LOS AJUSTES
// =======================================================

/**
 * Devuelve el código de moneda configurado.
 */
function tureserva_get_moneda() {
    return get_option( 'tureserva_moneda', 'USD' );
}

/**
 * Devuelve el símbolo de moneda.
 */
function tureserva_get_simbolo_moneda() {
    return get_option( 'tureserva_simbolo_moneda', '$' );
}

/**
 * Devuelve el porcentaje de impuesto configurado.
 */
function tureserva_get_impuesto() {
    return floatval( get_option( 'tureserva_impuesto', 0.07 ) );
}

/**
 * Devuelve el formato de fecha (compatible con PHP date()).
 */
function tureserva_get_formato_fecha() {
    return get_option( 'tureserva_formato_fecha', 'd/m/Y' );
}

/**
 * Devuelve el código de idioma del sistema.
 */
function tureserva_get_idioma() {
    return get_option( 'tureserva_idioma', 'es_ES' );
}

// =======================================================
// 🧮 FUNCIÓN UTILITARIA: FORMATEAR MONEDAS
// =======================================================
/**
 * Devuelve una cantidad formateada con el símbolo y moneda definidos.
 *
 * @param float $cantidad
 * @return string
 */
function tureserva_formatear_moneda( $cantidad ) {
    $simbolo = tureserva_get_simbolo_moneda();
    $moneda  = tureserva_get_moneda();
    $formatted = number_format_i18n( floatval( $cantidad ), 2 );

    return sprintf( '%s%s %s', esc_html( $simbolo ), esc_html( $formatted ), esc_html( $moneda ) );
}

// =======================================================
// 📦 ENDPOINT AJAX PARA GUARDAR AJUSTES (ADMIN)
// =======================================================
add_action( 'wp_ajax_tureserva_guardar_ajustes', 'tureserva_guardar_ajustes' );

function tureserva_guardar_ajustes() {

    // Seguridad
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( '❌ No autorizado para realizar esta acción.', 'tureserva' ) );
    }

    if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'tureserva_settings_nonce' ) ) {
        wp_send_json_error( __( '❌ Validación de seguridad fallida.', 'tureserva' ) );
    }

    // Campos permitidos
    $campos = array(
        'tureserva_moneda',
        'tureserva_simbolo_moneda',
        'tureserva_impuesto',
        'tureserva_formato_fecha',
        'tureserva_idioma'
    );

    $valores_guardados = array();

    foreach ( $campos as $campo ) {
        if ( isset( $_POST['data'] ) && is_string( $_POST['data'] ) ) {
            parse_str( $_POST['data'], $parsed_data );
        } else {
            $parsed_data = $_POST;
        }

        if ( isset( $parsed_data[ $campo ] ) ) {
            $valor = sanitize_text_field( $parsed_data[ $campo ] );

            // Convertir numérico si aplica
            if ( $campo === 'tureserva_impuesto' ) {
                $valor = floatval( str_replace(',', '.', $valor) );
            }

            update_option( $campo, $valor );
            $valores_guardados[ $campo ] = $valor;
        }
    }

    wp_send_json_success( array(
        'mensaje' => __( '✅ Ajustes guardados correctamente.', 'tureserva' ),
        'valores' => $valores_guardados
    ));
}
