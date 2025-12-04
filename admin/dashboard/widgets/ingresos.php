<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * WIDGET: Ingresos del Mes — TuReserva
 * ==========================================================
 * Muestra el total de ingresos del mes actual y una comparativa
 * con el mes anterior, basado en el meta `_tureserva_total`.
 * ==========================================================
 */

function tureserva_widget_ingresos_render() {
    global $wpdb;

    // Campos de meta (precio total de la reserva)
    $meta_total = '_tureserva_precio_total';

    // Fechas base
    $year_now  = date('Y');
    $month_now = date('m');

    // Mes anterior
    $date_prev = strtotime("-1 month");
    $year_prev = date('Y', $date_prev);
    $month_prev = date('m', $date_prev);

    // Totales del mes actual
    $fecha_inicio_actual = "$year_now-$month_now-01";
    $fecha_fin_actual = date('Y-m-t', strtotime($fecha_inicio_actual));
    
    $total_actual = (float) $wpdb->get_var($wpdb->prepare("
        SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'tureserva_reserva'
        AND p.post_status = 'publish'
        AND pm.meta_key = %s
        AND p.post_date >= %s
        AND p.post_date <= %s
    ", $meta_total, $fecha_inicio_actual, $fecha_fin_actual));

    // Totales del mes anterior
    $fecha_inicio_anterior = "$year_prev-$month_prev-01";
    $fecha_fin_anterior = date('Y-m-t', strtotime($fecha_inicio_anterior));
    
    $total_anterior = (float) $wpdb->get_var($wpdb->prepare("
        SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'tureserva_reserva'
        AND p.post_status = 'publish'
        AND pm.meta_key = %s
        AND p.post_date >= %s
        AND p.post_date <= %s
    ", $meta_total, $fecha_inicio_anterior, $fecha_fin_anterior));

    // Calcular variación
    $variacion = 0;
    if ($total_anterior > 0) {
        $variacion = (($total_actual - $total_anterior) / $total_anterior) * 100;
    }

    // Determinar color y símbolo de variación
    $color_variacion = $variacion >= 0 ? '#00b894' : '#d63031';
    $simbolo = $variacion >= 0 ? '▲' : '▼';

    // Valor formateado
    $fmt_actual = number_format($total_actual, 2, '.', ',');
    $fmt_anterior = number_format($total_anterior, 2, '.', ',');
    $fmt_variacion = number_format(abs($variacion), 2);

    // Barra de progreso (escala 0–100%)
    $porcentaje_barra = min(100, ($total_actual > 0 ? ($total_actual / max($total_anterior, 1)) * 100 : 0));
    ?>

    <style>
    .tureserva-ingresos-container { font-family: "Inter", system-ui, sans-serif; }
    .tureserva-ingresos-total { font-size: 24px; font-weight: 600; color: #2d3436; }
    .tureserva-ingresos-anterior { font-size: 14px; color: #636e72; margin-bottom: 5px; }
    .tureserva-barra {
        width: 100%; height: 10px; border-radius: 5px;
        background-color: #dfe6e9; margin: 8px 0;
    }
    .tureserva-barra-fill {
        height: 10px; border-radius: 5px;
        background-color: #00b894;
        transition: width 0.5s ease;
    }
    </style>

    <div class="tureserva-ingresos-container">
        <div class="tureserva-ingresos-total">
            B/. <?php echo esc_html($fmt_actual); ?>
        </div>
        <div class="tureserva-ingresos-anterior">
            <?php echo date('F Y'); ?>  
            <small>(Mes anterior: B/. <?php echo esc_html($fmt_anterior); ?>)</small>
        </div>

        <div class="tureserva-barra">
            <div class="tureserva-barra-fill" style="width:<?php echo esc_attr($porcentaje_barra); ?>%;"></div>
        </div>

        <div style="margin-top:5px; font-size:14px;">
            <strong style="color:<?php echo esc_attr($color_variacion); ?>;">
                <?php echo $simbolo . ' ' . esc_html($fmt_variacion); ?>%
            </strong>
            respecto al mes anterior
        </div>
    </div>
    <?php
}
