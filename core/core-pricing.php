<?php
/**
 * ==========================================================
 * CORE: Sistema de Cálculo de Precios — TuReserva
 * ==========================================================
 * Calcula tarifas dinámicas considerando:
 * - Precio base del alojamiento
 * - Temporadas activas (alta / baja)
 * - Tarifas variables (por noche, adulto, niño)
 * - Servicios adicionales (precio fijo o por día)
 * - Impuestos y descuentos (en desarrollo)
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔹 FUNCIÓN PRINCIPAL DE CÁLCULO DE PRECIO
// =======================================================
function tureserva_calcular_precio_total( $alojamiento_id, $check_in, $check_out, $huespedes = array(), $servicios_ids = array() ) {

    $noches = tureserva_calcular_noches( $check_in, $check_out );
    if ( $noches <= 0 ) return 0;

    // ===============================
    // 🔸 Precio base del alojamiento
    // ===============================
    $precio_base = floatval( get_post_meta( $alojamiento_id, '_tureserva_precio_base', true ) );

    // ===============================
    // 🔸 Determinar temporada activa
    // ===============================
    $temporada = tureserva_obtener_temporada_activa( $check_in, $check_out );
    $ajuste_temporada = 1;

    if ( $temporada ) {
        $factor = get_post_meta( $temporada->ID, '_tureserva_factor_precio', true );
        if ( $factor && is_numeric( $factor ) ) {
            $ajuste_temporada = floatval( $factor );
        }
    }

    // ===============================
    // 🔸 Cálculo por huéspedes
    // ===============================
    $adultos  = isset( $huespedes['adultos'] ) ? intval( $huespedes['adultos'] ) : 2;
    $ninos    = isset( $huespedes['ninos'] ) ? intval( $huespedes['ninos'] ) : 0;

    $precio_por_adulto = floatval( get_post_meta( $alojamiento_id, '_tureserva_precio_adulto', true ) );
    $precio_por_nino   = floatval( get_post_meta( $alojamiento_id, '_tureserva_precio_nino', true ) );

    $extra_adultos = max( 0, $adultos - 2 );
    $costo_huespedes = ( $extra_adultos * $precio_por_adulto ) + ( $ninos * $precio_por_nino );

    // ===============================
    // 🔸 Servicios adicionales
    // ===============================
    $costo_servicios = 0;
    if ( ! empty( $servicios_ids ) ) {
        foreach ( $servicios_ids as $serv_id ) {
            $precio_servicio = floatval( get_post_meta( $serv_id, '_tureserva_precio_servicio', true ) );
            $tipo_servicio   = get_post_meta( $serv_id, '_tureserva_tipo_servicio', true ); // "fijo" o "por_dia"
            if ( $tipo_servicio === 'por_dia' ) {
                $costo_servicios += $precio_servicio * $noches;
            } else {
                $costo_servicios += $precio_servicio;
            }
        }
    }

    // ===============================
    // 🔸 Cálculo final
    // ===============================
    $subtotal = ( $precio_base * $noches * $ajuste_temporada ) + $costo_huespedes + $costo_servicios;
    $impuestos = tureserva_calcular_impuestos( $subtotal );

    $total = $subtotal + $impuestos;

    return array(
        'alojamiento_id'   => $alojamiento_id,
        'noches'           => $noches,
        'precio_base'      => $precio_base,
        'temporada'        => $temporada ? $temporada->post_title : 'Estándar',
        'ajuste_temporada' => $ajuste_temporada,
        'costo_huespedes'  => $costo_huespedes,
        'costo_servicios'  => $costo_servicios,
        'subtotal'         => $subtotal,
        'impuestos'        => $impuestos,
        'total'            => $total,
        'moneda'           => get_option( 'tureserva_moneda', 'USD' ),
    );
}

// =======================================================
// 🧮 CALCULAR NÚMERO DE NOCHES
// =======================================================
function tureserva_calcular_noches( $check_in, $check_out ) {
    $inicio = strtotime( $check_in );
    $fin    = strtotime( $check_out );
    if ( ! $inicio || ! $fin ) return 0;
    return max( 0, floor( ( $fin - $inicio ) / DAY_IN_SECONDS ) );
}

// =======================================================
// 🗓️ OBTENER TEMPORADA ACTIVA
// =======================================================
function tureserva_obtener_temporada_activa( $check_in, $check_out ) {
    $args = array(
        'post_type'      => 'tureserva_temporadas',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $temporadas = get_posts( $args );
    if ( empty( $temporadas ) ) return null;

    $inicio = strtotime( $check_in );
    $fin    = strtotime( $check_out );

    foreach ( $temporadas as $temp ) {
        $desde = strtotime( get_post_meta( $temp->ID, '_tureserva_inicio', true ) );
        $hasta = strtotime( get_post_meta( $temp->ID, '_tureserva_fin', true ) );

        if ( $inicio >= $desde && $inicio <= $hasta ) {
            return $temp;
        }
        if ( $fin >= $desde && $fin <= $hasta ) {
            return $temp;
        }
    }

    return null;
}

// =======================================================
// 💰 CÁLCULO DE IMPUESTOS (configurable)
// =======================================================
function tureserva_calcular_impuestos( $subtotal ) {
    $porcentaje = floatval( get_option( 'tureserva_impuesto', 0.07 ) ); // 7% por defecto
    return round( $subtotal * $porcentaje, 2 );
}

// =======================================================
// 🧠 FUNCIÓN DE DEPURACIÓN (solo desarrollo)
// =======================================================
// add_action( 'init', function() {
//     if ( isset($_GET['debug_precio']) ) {
//         $resultado = tureserva_calcular_precio_total(
//             123, // ID de alojamiento
//             '2025-12-20',
//             '2025-12-23',
//             array( 'adultos' => 2, 'ninos' => 1 ),
//             array( 45, 46 )
//         );
//         echo '<pre>'; print_r( $resultado ); echo '</pre>'; exit;
//     }
// });
