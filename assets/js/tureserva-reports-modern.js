// tureserva-reports-modern.js
// Handles tab navigation, AJAX fetching of report data, and rendering charts using Chart.js
document.addEventListener('DOMContentLoaded', function () {
    // Tab navigation
    const tabs = document.querySelectorAll('.tureserva-tabs button');
    const contents = document.querySelectorAll('.tureserva-tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Activate selected tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            // Show corresponding content
            const target = tab.getAttribute('data-tab');
            contents.forEach(c => {
                c.classList.toggle('active', c.id === target);
            });
        });
    });

    // Generate report button
    const btn = document.getElementById('tureserva-boton-reporte');
    if (btn) {
        btn.addEventListener('click', () => {
            const data = {
                inicio: document.getElementById('tureserva_inicio').value,
                fin: document.getElementById('tureserva_fin').value,
                alojamiento: document.getElementById('tureserva_alojamiento').value,
                estado: document.getElementById('tureserva_estado').value,
                nonce: tureservaReportes.nonce,
            };
            const params = new URLSearchParams(data).toString();
            fetch(`${tureservaReportes.ajax_url}?action=tureserva_get_reporte&${params}`)
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        const r = resp.data;
                        // Update KPI boxes
                        document.getElementById('total-reservas').textContent = r.total_reservas;
                        document.getElementById('ingresos-totales').textContent = `$${r.ingresos_totales}`;
                        document.getElementById('promedio-reserva').textContent = `$${r.promedio_reserva}`;
                        document.getElementById('ocupacion-dias').textContent = r.ocupacion_dias;
                        // Show results section
                        document.getElementById('tureserva-resultados').style.display = 'block';
                        // Render chart (simple bar for daily occupancy)
                        renderOccupancyChart(r.daily_occupancy);
                    } else {
                        alert('Error al obtener el reporte');
                    }
                });
        });
    }
});

function renderOccupancyChart(data) {
    const ctx = document.getElementById('tureserva-chart').getContext('2d');
    const labels = Object.keys(data);
    const values = Object.values(data);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ocupación Diaria (%)',
                data: values,
                backgroundColor: 'rgba(30,144,255,0.6)',
                borderColor: 'rgba(30,144,255,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
}
