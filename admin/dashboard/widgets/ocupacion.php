<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * WIDGET: Estadísticas de Ocupación — TuReserva
 * ==========================================================
 * Muestra una gráfica comparativa (año actual vs anterior)
 * basada en las reservas registradas en el CPT 'reserva'.
 * ==========================================================
 */

function tureserva_widget_ocupacion_render() {
    global $wpdb;

    $year_now  = date('Y');
    $year_prev = $year_now - 1;

    $ocupacion_now  = [];
    $ocupacion_prev = [];

    // Contar reservas por mes del año actual
    for ($mes = 1; $mes <= 12; $mes++) {
        $inicio = date("$year_now-$mes-01");
        $fin    = date("$year_now-$mes-t");

        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(ID)
            FROM $wpdb->posts
            WHERE post_type = 'tureserva_reserva'
            AND post_status = 'publish'
            AND post_date BETWEEN %s AND %s
        ", $inicio, $fin));

        $ocupacion_now[] = (int) $count;
    }

    // Contar reservas por mes del año anterior
    for ($mes = 1; $mes <= 12; $mes++) {
        $inicio = date("$year_prev-$mes-01");
        $fin    = date("$year_prev-$mes-t");

        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(ID)
            FROM $wpdb->posts
            WHERE post_type = 'tureserva_reserva'
            AND post_status = 'publish'
            AND post_date BETWEEN %s AND %s
        ", $inicio, $fin));

        $ocupacion_prev[] = (int) $count;
    }

    // Total de alojamientos activos (para calcular porcentaje si se requiere)
    $total_alojamientos = (int) $wpdb->get_var("
        SELECT COUNT(ID)
        FROM $wpdb->posts
        WHERE post_type = 'trs_alojamiento'
        AND post_status = 'publish'
    ");

    // Calcular porcentaje de ocupación (si hay alojamientos registrados)
    if ($total_alojamientos > 0) {
        foreach ($ocupacion_now as $i => $val) {
            $ocupacion_now[$i] = round(($val / $total_alojamientos) * 100, 2);
        }
        foreach ($ocupacion_prev as $i => $val) {
            $ocupacion_prev[$i] = round(($val / $total_alojamientos) * 100, 2);
        }
    }

    ?>
    <div style="margin-top:10px;">
        <canvas id="tureservaOcupacionChart" height="90"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('tureservaOcupacionChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
            datasets: [
                {
                    label: '<?php echo esc_js($year_prev); ?>',
                    data: <?php echo json_encode($ocupacion_prev); ?>,
                    borderColor: '#7b8cff',
                    backgroundColor: 'rgba(123,140,255,0.15)',
                    tension: 0.3,
                    borderWidth: 2,
                    fill: true
                },
                {
                    label: '<?php echo esc_js($year_now); ?>',
                    data: <?php echo json_encode($ocupacion_now); ?>,
                    borderColor: '#00b894',
                    backgroundColor: 'rgba(0,184,148,0.15)',
                    tension: 0.3,
                    borderWidth: 2,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Comparativa de Ocupación (%)',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + '%' }
                    }
                }
            }
        }
    });
    </script>
    <?php
}
