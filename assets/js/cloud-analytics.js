/**
 * ==========================================================
 * JS — Dashboard Visual con Recarga (Fase 10 – Paso 4)
 * ==========================================================
 * Muestra gráficos y permite refrescar datos sin recargar.
 * ==========================================================
 */

jQuery(document).ready(function ($) {

    const ctxBar = document.getElementById('tureservaChart');
    if (!ctxBar) return;

    // Crear canvas extra para donut
    const donutCanvas = $('<canvas id="tureservaDonut" height="140"></canvas>');
    $('#tureserva-analytics').append(donutCanvas);

    let chartBar = null;
    let chartDonut = null;

    // =======================================================
    // 🚀 Función principal de carga de datos
    // =======================================================
    function loadStats() {
        $.ajax({
            url: tureserva_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'tureserva_get_sync_logs',
                security: tureserva_ajax.nonce
            },
            beforeSend: function () {
                $('#tureserva-refresh-stats').text('Actualizando...').prop('disabled', true);
            },
            success: function (response) {
                if (!response.success || !response.data.length) {
                    console.warn('⚠️ No hay registros.');
                    return;
                    // =======================================================
// 🧭 ACTUALIZAR TARJETAS KPI
// =======================================================
$('#kpi-total').text(logs.length);
$('#kpi-exitoso').text(totalExitos);
$('#kpi-fallido').text(totalFallos);
$('#kpi-duracion').text(duracionProm + 's');

                }

                const logs = response.data;
                const labels = logs.map(i => i.fecha_fin || i.fecha_inicio);
                const exitosos = logs.map(i => parseInt(i.exitoso));
                const fallidos = logs.map(i => parseInt(i.fallido));
                const duraciones = logs.map(i => parseInt(i.duracion));

                const totalExitos = exitosos.reduce((a, b) => a + b, 0);
                const totalFallos = fallidos.reduce((a, b) => a + b, 0);
                const duracionProm = Math.round(
                    duraciones.reduce((a, b) => a + b, 0) / (duraciones.length || 1)
                );

                // 🔁 Reiniciar gráficos si ya existen
                if (chartBar) chartBar.destroy();
                if (chartDonut) chartDonut.destroy();

                // 📊 Gráfico de barras
                chartBar = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels.reverse().slice(-10),
                        datasets: [
                            {
                                label: 'Exitosas',
                                data: exitosos.reverse().slice(-10),
                                backgroundColor: '#4CAF50'
                            },
                            {
                                label: 'Fallidas',
                                data: fallidos.reverse().slice(-10),
                                backgroundColor: '#F44336'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: `Historial — ${logs.length} registros` },
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Fecha' } },
                            y: { beginAtZero: true, title: { display: true, text: 'Cantidad' } }
                        }
                    }
                });

                // 🍩 Donut
                chartDonut = new Chart($('#tureservaDonut')[0], {
                    type: 'doughnut',
                    data: {
                        labels: ['Exitosas', 'Fallidas'],
                        datasets: [{
                            data: [totalExitos, totalFallos],
                            backgroundColor: ['#4CAF50', '#F44336']
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            title: { display: true, text: 'Proporción total — Éxitos vs Fallos' },
                            legend: { position: 'bottom' }
                        }
                    }
                });

                // 🧾 Resumen textual (se reemplaza si ya existe)
                $('#tureserva-summary').remove();
                const resumenHtml = `
                    <div id="tureserva-summary" style="margin-top:15px;font-size:14px;">
                        <strong>Total sincronizaciones:</strong> ${logs.length}<br>
                        <strong>Éxitos:</strong> ${totalExitos}<br>
                        <strong>Fallos:</strong> ${totalFallos}<br>
                        <strong>Duración promedio:</strong> ${duracionProm}s
                    </div>
                `;
                $('#tureserva-analytics').append(resumenHtml);
            },
            complete: function () {
                $('#tureserva-refresh-stats').text('🔄 Actualizar estadísticas').prop('disabled', false);
            },
            error: function () {
                console.error('❌ Error al actualizar estadísticas.');
                $('#tureserva-refresh-stats').text('Reintentar').prop('disabled', false);
            }
        });
    }

    // Cargar al inicio
    loadStats();

    // Botón de actualización
    $('#tureserva-refresh-stats').on('click', function () {
        loadStats();
    });
});
