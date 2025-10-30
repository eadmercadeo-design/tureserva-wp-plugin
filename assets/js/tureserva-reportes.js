jQuery(document).ready(function ($) {

    const btn = $('#tureserva-boton-reporte');
    const resultados = $('#tureserva-resultados');
    let chartInstance = null;

    btn.on('click', function () {

        const inicio = $('#tureserva_inicio').val();
        const fin = $('#tureserva_fin').val();
        const alojamiento = $('#tureserva_alojamiento').val();
        const estado = $('#tureserva_estado').val();

        btn.prop('disabled', true).text('Cargando...');

        $.ajax({
            url: tureservaReportes.ajax_url,
            method: 'GET',
            data: {
                action: 'tureserva_get_reporte',
                inicio: inicio,
                fin: fin,
                alojamiento: alojamiento,
                estado: estado
            },
            success: function (resp) {
                btn.prop('disabled', false).text('📊 Generar reporte');
                if (!resp.success) {
                    alert('Error al generar el reporte');
                    return;
                }

                const d = resp.data;
                resultados.show();

                $('#total-reservas').text(d.total_reservas);
                $('#ingresos-totales').text('$' + d.ingresos_totales.toFixed(2));
                $('#promedio-reserva').text('$' + d.promedio_reserva.toFixed(2));
                $('#ocupacion-dias').text(d.ocupacion_dias);

                // ChartJS
                const ctx = document.getElementById('tureserva-chart');
                const data = {
                    labels: ['Confirmadas', 'Pendientes', 'Canceladas'],
                    datasets: [{
                        data: [
                            d.por_estado.confirmada,
                            d.por_estado.pendiente,
                            d.por_estado.cancelada
                        ],
                        backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
                    }]
                };
                const options = {
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Distribución de reservas por estado'
                        }
                    }
                };

                if (chartInstance) chartInstance.destroy();
                chartInstance = new Chart(ctx, { type: 'doughnut', data, options });
            },
            error: function () {
                btn.prop('disabled', false).text('📊 Generar reporte');
                alert('Error de conexión con el servidor.');
            }
        });
    });
});
