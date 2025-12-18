<?php
/**
 * ==========================================================
 * CORE: Lógica de Cupones — TuReserva
 * ==========================================================
 * Funciones para validar, aplicar y gestionar el uso de cupones.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🔍 OBTENER CUPÓN POR CÓDIGO (POST TITLE)
// =======================================================
function tureserva_get_coupon_by_code( $code ) {
    $code = sanitize_text_field( $code );
    
    $args = array(
        'post_type'      => 'tureserva_cupon',
        'title'          => $code,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids'
    );
    
    // Búsqueda exacta por título no es nativa 100% en WP_Query sin filtros, 
    // pero intentamos con 'title' o 'name' (slug). 
    // Mejor usamos get_page_by_title para exactitud si el slug coincide.
    
    $coupon = get_page_by_title( $code, OBJECT, 'tureserva_cupon' );
    
    if ( ! $coupon || $coupon->post_status !== 'publish' ) {
        return null;
    }
    
    return $coupon;
}

// =======================================================
// ✅ VALIDAR CUPÓN PARA UNA RESERVA
// =======================================================
function tureserva_validate_coupon( $code, $reservation_data = array() ) {
    $coupon = tureserva_get_coupon_by_code( $code );
    
    if ( ! $coupon ) {
        return new WP_Error( 'coupon_not_found', 'El cupón no existe o no está activo.' );
    }
    
    $coupon_id = $coupon->ID;
    
    // 1. Validar Fechas de Caducidad
    $fecha_caducidad = get_post_meta( $coupon_id, '_tureserva_fecha_caducidad', true );
    if ( ! empty( $fecha_caducidad ) && strtotime( $fecha_caducidad ) < current_time( 'timestamp' ) ) {
        return new WP_Error( 'coupon_expired', 'El cupón ha caducado.' );
    }
    
    // 2. Validar Límite de Uso Global
    $limite_uso = (int) get_post_meta( $coupon_id, '_tureserva_limite_uso', true );
    $uso_actual = (int) get_post_meta( $coupon_id, '_tureserva_uso_actual', true );
    
    if ( $limite_uso > 0 && $uso_actual >= $limite_uso ) {
        return new WP_Error( 'coupon_limit_reached', 'El cupón ha alcanzado su límite de uso.' );
    }
    
    // 3. Validar Alojamiento Específico
    $valid_accommodations = get_post_meta( $coupon_id, '_tureserva_alojamientos', true );
    if ( ! empty( $valid_accommodations ) && is_array( $valid_accommodations ) ) {
        $current_accommodation = isset( $reservation_data['alojamiento_id'] ) ? $reservation_data['alojamiento_id'] : 0;
        if ( ! in_array( $current_accommodation, $valid_accommodations ) ) {
            return new WP_Error( 'coupon_restricted_accommodation', 'Este cupón no es válido para este alojamiento.' );
        }
    }
    
    // 4. Validar Mínimo de Días
    $min_days = (int) get_post_meta( $coupon_id, '_tureserva_min_days', true );
    if ( $min_days > 0 ) {
        $noches = isset( $reservation_data['noches'] ) ? $reservation_data['noches'] : 0;
        if ( $noches < $min_days ) {
            return new WP_Error( 'coupon_min_days', sprintf( 'Este cupón requiere una estancia mínima de %d noches.', $min_days ) );
        }
    }

    // 5. Validar Reglas de Anticipación (Early Bird / Last Minute)
    $today = current_time('Y-m-d');
    $check_in = isset( $reservation_data['check_in'] ) ? $reservation_data['check_in'] : '';
    
    if ( $check_in ) {
        $days_diff = (strtotime($check_in) - strtotime($today)) / DAY_IN_SECONDS;
        
        $min_days_before = get_post_meta( $coupon_id, '_tureserva_min_days_before', true );
        if ( $min_days_before !== '' && $days_diff < $min_days_before ) {
             return new WP_Error( 'coupon_early_bird', sprintf( 'Este cupón requiere reservar con al menos %d días de antelación.', $min_days_before ) );
        }
        
        $max_days_before = get_post_meta( $coupon_id, '_tureserva_max_days_before', true );
        if ( $max_days_before !== '' && $days_diff > $max_days_before ) {
             return new WP_Error( 'coupon_last_minute', sprintf( 'Este cupón solo es válido para reservas dentro de los próximos %d días.', $max_days_before ) );
        }
    }

    // Si pasa todo
    return $coupon;
}

// =======================================================
// 💰 CALCULAR DESCUENTO
// =======================================================
function tureserva_calculate_discount( $coupon_id, $total_amount, $noches = 1 ) {
    $type  = get_post_meta( $coupon_id, '_tureserva_tipo_cupon', true ); // percentage | fixed | per_night | total
    $amount = floatval( get_post_meta( $coupon_id, '_tureserva_monto', true ) );
    
    $discount = 0;
    
    if ( $type === 'percentage' ) {
        $discount = ( $total_amount * $amount ) / 100;
    } elseif ( $type === 'fixed' || $type === 'total' ) {
        $discount = $amount;
    } elseif ( $type === 'per_night' ) {
        $discount = $amount * $noches;
    }
    
    // Asegurar que no sea mayor al total
    if ( $discount > $total_amount ) {
        $discount = $total_amount;
    }
    
    return round( $discount, 2 );
}

// =======================================================
// 📈 INCREMENTAR USO
// =======================================================
function tureserva_increment_coupon_usage( $coupon_id ) {
    $current = (int) get_post_meta( $coupon_id, '_tureserva_uso_actual', true );
    update_post_meta( $coupon_id, '_tureserva_uso_actual', $current + 1 );
}

// =======================================================
// 📉 DECREMENTAR USO (Al cancelar reserva)
// =======================================================
function tureserva_decrement_coupon_usage( $reserva_id ) {
    $coupon_code = get_post_meta( $reserva_id, '_tureserva_coupon_code', true );
    if ( ! $coupon_code ) return;
    
    $coupon = tureserva_get_coupon_by_code( $coupon_code );
    if ( $coupon ) {
        $current = (int) get_post_meta( $coupon->ID, '_tureserva_uso_actual', true );
        if ( $current > 0 ) {
            update_post_meta( $coupon->ID, '_tureserva_uso_actual', $current - 1 );
        }
    }
}
add_action( 'tureserva_reserva_cancelada', 'tureserva_decrement_coupon_usage' );

