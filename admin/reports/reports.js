jQuery(document).ready(function ($) {

    // --- Tabs Handling ---
    $('.tureserva-reports-nav .nav-tab').on('click', function (e) {
        e.preventDefault();
        $('.tureserva-reports-nav .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        var tabId = $(this).data('tab');
        $('.report-tab-content').hide();
        $('#tab-' + tabId).show();

        // Trigger chart resize/render if needed when tab becomes visible
        if (typeof Chart !== 'undefined') {
            // force update logic if required
        }

        // Load data if empty
        // if (tabId === 'reservas') loadReservas();
    });

    // --- Chart.js Instances ---
    let reservasLineChart, canalesDoughnutChart, paisesBarChart;

    // --- Init ---
    initDashboard();

    // --- Dashboard Logic ---
    function initDashboard() {
        if ($('#reservasLineChart').length) {
            loadDashboardData();
        }
    }

    $('#refresh-dashboard').on('click', function () {
        loadDashboardData();
    });

    $('#dashboard-period').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#custom-date-range').css('display', 'inline-block');
        } else {
            $('#custom-date-range').hide();
            loadDashboardData();
        }
    });

    function loadDashboardData() {
        const period = $('#dashboard-period').val();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'tureserva_get_dashboard_data',
                period: period,
                start: $('#dashboard-start').val(),
                end: $('#dashboard-end').val(),
                _nonce: '' // TODO: Add nonce
            },
            success: function (response) {
                if (response.success) {
                    renderDashboard(response.data);
                } else {
                    console.error('Error fetching dashboard data');
                }
            }
        });
    }

    function renderDashboard(data) {
        // KPIs
        $('#kpi-total-reservas').text(data.kpis.total_reservas);
        $('#kpi-ocupacion').text(data.kpis.ocupacion + '%');
        $('#kpi-ingresos-brutos').text(data.kpis.ingresos);
        $('#kpi-adr').text(data.kpis.adr);

        // Chart: Reservas Timeline
        const ctxLine = document.getElementById('reservasLineChart').getContext('2d');
        if (reservasLineChart) reservasLineChart.destroy();

        reservasLineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: data.charts.timeline.labels,
                datasets: [{
                    label: 'Reservas',
                    data: data.charts.timeline.data,
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Chart: Canales
        const ctxDoughnut = document.getElementById('canalesDoughnutChart').getContext('2d');
        if (canalesDoughnutChart) canalesDoughnutChart.destroy();

        canalesDoughnutChart = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: data.charts.canales.labels,
                datasets: [{
                    data: data.charts.canales.data,
                    backgroundColor: ['#2271b1', '#00a32a', '#dba617', '#d63638']
                }]
            }
        });

        // Chart: Paises
        const ctxBar = document.getElementById('paisesBarChart').getContext('2d');
        if (paisesBarChart) paisesBarChart.destroy();

        paisesBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: data.charts.paises.labels,
                datasets: [{
                    label: 'Huéspedes',
                    data: data.charts.paises.data,
                    backgroundColor: '#72aee6'
                }]
            }
        });
    }

    // --- Mock Data Calls for other tabs to prevent errors if clicked ---
    // (Implement real logic in next steps)

});
