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
function tureserva_calcular_precio_total( $alojamiento_id, $check_in, $check_out, $huespedes = array(), $servicios_ids = array(), $coupon_code = '' ) {

    $noches = tureserva_calcular_noches( $check_in, $check_out );
    if ( $noches <= 0 ) return 0;

    // ===============================
    // 🔸 ESTRATEGIA DE PRECIOS (MODELO AVANZADO)
    // ===============================
    // 1. Buscar si existe una TARIFA (tureserva_tarifa) específica para este alojamiento y fechas
    $tarifa_especifica = tureserva_obtener_tarifa_avanzada($alojamiento_id, $check_in, $check_out);
    
    $precio_base_final = 0;
    $ajuste_temporada = 1;
    $usando_tarifa_avanzada = false;

    if ($tarifa_especifica) {
        // ✅ TARIFA AVANZADA ENCONTRADA
        $precio_base_final = floatval($tarifa_especifica['precio']);
        $usando_tarifa_avanzada = true;
        $temporada_nombre = $tarifa_especifica['nombre_tarifa'];
    } else {
        // ⚠️ FALLBACK: MODELO SIMPLE (Precio Base Alojamiento * Factor Temporada)
        $precio_base_alojamiento = floatval( get_post_meta( $alojamiento_id, '_tureserva_precio_base', true ) );
        
        // Determinar temporada activa
        $temporada = tureserva_obtener_temporada_activa( $check_in, $check_out );
        
        if ( $temporada ) {
            $factor = get_post_meta( $temporada->ID, '_tureserva_factor_precio', true );
            if ( $factor && is_numeric( $factor ) ) {
                $ajuste_temporada = floatval( $factor );
            }
            $temporada_nombre = $temporada->post_title;
        } else {
            $temporada_nombre = 'Estándar';
        }

        $precio_base_final = $precio_base_alojamiento * $ajuste_temporada;
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
            $tipo_servicio   = get_post_meta( $serv_id, '_tureserva_tipo_servicio', true ); // "fijo", "por_dia", "por_persona"
            
            if ( $tipo_servicio === 'por_dia' ) {
                $costo_servicios += $precio_servicio * $noches;
            } elseif ( $tipo_servicio === 'por_persona' ) {
                $total_huespedes = $adultos + $ninos;
                $costo_servicios += $precio_servicio * $total_huespedes;
            } else {
                $costo_servicios += $precio_servicio;
            }
        }
    }

    // ===============================
    // 🔸 Cálculo final (Subtotal)
    // ===============================
    if ($usando_tarifa_avanzada) {
        $subtotal = ($precio_base_final * $noches) + $costo_huespedes + $costo_servicios;
    } else {
        $subtotal = ($precio_base_final * $noches) + $costo_huespedes + $costo_servicios;
    }

    // ===============================
    // 🎟️ APLICAR CUPÓN (Si existe)
    // ===============================
    $descuento = 0;
    $coupon_data = null;
    $coupon_error = null;

    if ( ! empty( $coupon_code ) && function_exists('tureserva_validate_coupon') ) {
        $res_data = [
            'alojamiento_id' => $alojamiento_id,
            'check_in'       => $check_in,
            'check_out'      => $check_out,
            'noches'         => $noches,
            'amount'         => $subtotal // Validar reglas de monto mínimo si existieran
        ];
        
        $coupon = tureserva_validate_coupon( $coupon_code, $res_data );

        if ( is_wp_error( $coupon ) ) {
            $coupon_error = $coupon->get_error_message();
        } else {
            $descuento = tureserva_calculate_discount( $coupon->ID, $subtotal, $noches );
            $subtotal_con_descuento = max( 0, $subtotal - $descuento );
            $coupon_data = [
                'code' => $coupon_code,
                'id' => $coupon->ID,
                'discount' => $descuento
            ];
            // Actualizar subtotal para el cálculo de impuestos
            // OJO: ¿Los impuestos se calculan antes o después del descuento?
            // Generalmente impuestos se calculan sobre el precio final a pagar.
            $subtotal = $subtotal_con_descuento; 
        }
    }

    $impuestos = tureserva_calcular_impuestos( $subtotal );
    $total = $subtotal + $impuestos;

    return array(
        'alojamiento_id'   => $alojamiento_id,
        'noches'           => $noches,
        'precio_base'      => $precio_base_final,
        'temporada'        => $temporada_nombre,
        'ajuste_temporada' => $ajuste_temporada,
        'costo_huespedes'  => $costo_huespedes,
        'costo_servicios'  => $costo_servicios,
        'cupon'            => $coupon_data,
        'error_cupon'      => $coupon_error,
        'descuento'        => $descuento,
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
        'post_type'      => 'temporada',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $temporadas = get_posts( $args );
    if ( empty( $temporadas ) ) return null;

    $inicio = strtotime( $check_in );
    $fin    = strtotime( $check_out );

    foreach ( $temporadas as $temp ) {
        $desde = strtotime( get_post_meta( $temp->ID, '_tureserva_fecha_inicio', true ) );
        $hasta = strtotime( get_post_meta( $temp->ID, '_tureserva_fecha_fin', true ) );

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
// 🧠 HELPER: BUSCAR TARIFA AVANZADA (tureserva_tarifa)
// =======================================================
function tureserva_obtener_tarifa_avanzada($alojamiento_id, $check_in, $check_out) {
    // 1. Obtener todas las tarifas activas para este alojamiento
    $tarifas = get_posts([
        'post_type'      => 'tureserva_tarifa',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => '_tureserva_alojamiento_id',
                'value'   => $alojamiento_id,
                'compare' => '='
            ]
        ]
    ]);

    if (empty($tarifas)) return null;

    $inicio_reserva = strtotime($check_in);
    $fin_reserva    = strtotime($check_out);
    $noches         = max(1, floor(($fin_reserva - $inicio_reserva) / DAY_IN_SECONDS));

    foreach ($tarifas as $tarifa) {
        // Verificar rango de fechas de la tarifa
        $inicio_tarifa = strtotime(get_post_meta($tarifa->ID, '_tureserva_fecha_inicio', true));
        $fin_tarifa    = strtotime(get_post_meta($tarifa->ID, '_tureserva_fecha_fin', true));

        // Si la reserva cae dentro del rango de la tarifa
        if ($inicio_reserva >= $inicio_tarifa && $fin_reserva <= $fin_tarifa) {
            
            // Buscar precio específico en la estructura compleja
            $precios_data = get_post_meta($tarifa->ID, '_tureserva_precios', true);
            
            if (!empty($precios_data) && is_array($precios_data)) {
                // Por simplicidad, tomamos el primer bloque válido o iteramos
                foreach ($precios_data as $bloque) {
                    // Aquí podríamos validar temporada_id si fuera necesario
                    
                    // Verificar precios variables por duración
                    if (!empty($bloque['variables'])) {
                        foreach ($bloque['variables'] as $var) {
                            if ($noches >= $var['min'] && $noches <= $var['max']) {
                                return [
                                    'precio' => floatval($var['price']),
                                    'nombre_tarifa' => $tarifa->post_title . ' (Variable ' . $noches . ' noches)'
                                ];
                            }
                        }
                    }
                    
                    // Si no hay variable, devolver precio base del bloque
                    return [
                        'precio' => floatval($bloque['precio_base']),
                        'nombre_tarifa' => $tarifa->post_title
                    ];
                }
            }
            
            // Fallback al precio base de la tarifa si no hay bloques complejos
            $precio_base_tarifa = get_post_meta($tarifa->ID, '_tureserva_precio_base', true);
            if ($precio_base_tarifa) {
                return [
                    'precio' => floatval($precio_base_tarifa),
                    'nombre_tarifa' => $tarifa->post_title
                ];
            }
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
