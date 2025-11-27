<?php
/**
 * ==========================================================
 * CORE: Motor de Reglas — TuReserva
 * ==========================================================
 * Valida si una reserva cumple con las reglas definidas:
 * - Estancia mínima / máxima
 * - Días de llegada / salida permitidos
 * - Antelación mínima / máxima
 * - Bloqueos manuales
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Valida todas las reglas aplicables para una reserva.
 *
 * @param int    $alojamiento_id ID del alojamiento.
 * @param string $check_in       Fecha de entrada (Y-m-d).
 * @param string $check_out      Fecha de salida (Y-m-d).
 * @return true|WP_Error         True si es válido, WP_Error con mensaje si falla.
 */
function tureserva_validar_reglas( $alojamiento_id, $check_in, $check_out ) {
    
    $inicio = strtotime( $check_in );
    $fin    = strtotime( $check_out );
    $hoy    = strtotime( date( 'Y-m-d' ) );
    
    if ( ! $inicio || ! $fin ) {
        return new WP_Error( 'invalid_dates', 'Fechas inválidas.' );
    }

    if ( $inicio >= $fin ) {
        return new WP_Error( 'invalid_range', 'La fecha de salida debe ser posterior a la entrada.' );
    }

    // Calcular duración en noches
    $noches = max( 1, floor( ( $fin - $inicio ) / DAY_IN_SECONDS ) );
    
    // Calcular días de antelación
    $dias_antelacion = floor( ( $inicio - $hoy ) / DAY_IN_SECONDS );

    // Obtener día de la semana (mon, tue, wed...)
    $dia_llegada = strtolower( date( 'D', $inicio ) ); // Mon, Tue...
    $dia_salida  = strtolower( date( 'D', $fin ) );

    // Mapeo de días PHP (Mon) a valores guardados (mon) - ya son lowercase, pero aseguramos
    // El array en meta-boxes-reglas.php usa: mon, tue, wed, thu, fri, sat, sun

    // 1. Obtener todas las reglas publicadas
    $reglas = get_posts([
        'post_type'      => 'tureserva_regla',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    foreach ( $reglas as $regla ) {
        // 🔍 FILTRO 1: ¿Aplica a este alojamiento?
        $alojamientos_afectados = get_post_meta( $regla->ID, '_tureserva_alojamientos', true );
        if ( ! empty( $alojamientos_afectados ) && ! in_array( $alojamiento_id, $alojamientos_afectados ) ) {
            continue; // No aplica a este alojamiento
        }

        // 🔍 FILTRO 2: ¿Aplica a esta temporada? (Opcional, si la regla está ligada a temporadas)
        $temporadas_afectadas = get_post_meta( $regla->ID, '_tureserva_temporadas', true );
        if ( ! empty( $temporadas_afectadas ) ) {
            // Verificar si la fecha de la reserva cae en alguna de estas temporadas
            $aplica_temporada = false;
            foreach ( $temporadas_afectadas as $temp_id ) {
                $t_inicio = strtotime( get_post_meta( $temp_id, '_tureserva_fecha_inicio', true ) );
                $t_fin    = strtotime( get_post_meta( $temp_id, '_tureserva_fecha_fin', true ) );
                
                // Si hay solapamiento entre la reserva y la temporada
                if ( $inicio < $t_fin && $fin > $t_inicio ) {
                    $aplica_temporada = true;
                    break;
                }
            }
            if ( ! $aplica_temporada ) {
                continue; // No coincide con la temporada de la regla
            }
        }

        // ✅ VALIDAR TIPO DE REGLA
        $tipo = get_post_meta( $regla->ID, '_tureserva_rule_type', true );

        switch ( $tipo ) {
            case 'min_stay':
                $min = intval( get_post_meta( $regla->ID, '_tureserva_nights', true ) );
                if ( $noches < $min ) {
                    return new WP_Error( 'rule_min_stay', sprintf( 'La estancia mínima es de %d noches.', $min ) );
                }
                break;

            case 'max_stay':
                $max = intval( get_post_meta( $regla->ID, '_tureserva_nights', true ) );
                if ( $noches > $max ) {
                    return new WP_Error( 'rule_max_stay', sprintf( 'La estancia máxima es de %d noches.', $max ) );
                }
                break;

            case 'arrival_days':
                $allowed = get_post_meta( $regla->ID, '_tureserva_days', true ); // array('mon', 'fri'...)
                if ( ! empty( $allowed ) && ! in_array( $dia_llegada, $allowed ) ) {
                    return new WP_Error( 'rule_arrival_day', 'No se permiten llegadas en este día.' );
                }
                break;

            case 'departure_days':
                $allowed = get_post_meta( $regla->ID, '_tureserva_days', true );
                if ( ! empty( $allowed ) && ! in_array( $dia_salida, $allowed ) ) {
                    return new WP_Error( 'rule_departure_day', 'No se permiten salidas en este día.' );
                }
                break;

            case 'min_advance':
                $min = intval( get_post_meta( $regla->ID, '_tureserva_days_advance', true ) );
                if ( $dias_antelacion < $min ) {
                    return new WP_Error( 'rule_min_advance', sprintf( 'Debes reservar con al menos %d días de antelación.', $min ) );
                }
                break;

            case 'max_advance':
                $max = intval( get_post_meta( $regla->ID, '_tureserva_days_advance', true ) );
                if ( $dias_antelacion > $max ) {
                    return new WP_Error( 'rule_max_advance', sprintf( 'No se puede reservar con más de %d días de antelación.', $max ) );
                }
                break;

            case 'block':
                $b_inicio = strtotime( get_post_meta( $regla->ID, '_tureserva_date_from', true ) );
                $b_fin    = strtotime( get_post_meta( $regla->ID, '_tureserva_date_to', true ) );
                $motivo   = get_post_meta( $regla->ID, '_tureserva_reason', true ) ?: 'Fechas bloqueadas';

                // Verificar solapamiento
                // (StartA < EndB) and (EndA > StartB)
                if ( $inicio < $b_fin && $fin > $b_inicio ) {
                    return new WP_Error( 'rule_blocked', 'El alojamiento no está disponible: ' . $motivo );
                }
                break;
        }
    }

    return true;
}
