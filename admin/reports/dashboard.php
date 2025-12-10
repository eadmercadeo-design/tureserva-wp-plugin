<?php
/**
 * Pestaña: Dashboard General
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="tureserva-dashboard-controls">
    <select id="dashboard-period" class="tureserva-select">
        <option value="this_month">Este Mes</option>
        <option value="last_month">Mes Pasado</option>
        <option value="this_quarter">Este Trimestre</option>
        <option value="this_year">Este Año</option>
        <option value="custom">Personalizado</option>
    </select>
    <div id="custom-date-range" style="display:none; display:inline-block;">
        <input type="date" id="dashboard-start" class="tureserva-date">
        <input type="date" id="dashboard-end" class="tureserva-date">
    </div>
    <button id="refresh-dashboard" class="button button-secondary">Actualizar</button>
</div>

<div class="tureserva-kpi-grid">
    <div class="kpi-card">
        <span class="dashicons dashicons-calendar-alt"></span>
        <div class="kpi-data">
            <h3 id="kpi-total-reservas">--</h3>
            <small>Reservas Totales</small>
        </div>
    </div>
    <div class="kpi-card">
        <span class="dashicons dashicons-chart-pie"></span>
        <div class="kpi-data">
            <h3 id="kpi-ocupacion">--%</h3>
            <small>Ocupación</small>
        </div>
    </div>
    <div class="kpi-card">
        <span class="dashicons dashicons-money-alt"></span>
        <div class="kpi-data">
            <h3 id="kpi-ingresos-brutos">--</h3>
            <small>Ingresos Brutos</small>
        </div>
    </div>
    <div class="kpi-card">
        <span class="dashicons dashicons-chart-bar"></span>
        <div class="kpi-data">
            <h3 id="kpi-adr">--</h3>
            <small>ADR (Tarifa Prom.)</small>
        </div>
    </div>
</div>

<div class="tureserva-charts-row">
    <div class="chart-container large">
        <h3>Evolución de Reservas</h3>
        <canvas id="reservasLineChart"></canvas>
    </div>
</div>

<div class="tureserva-charts-row">
    <div class="chart-container half">
        <h3>Canales de Venta</h3>
        <canvas id="canalesDoughnutChart"></canvas>
    </div>
    <div class="chart-container half">
        <h3>Países de Huéspedes</h3>
        <canvas id="paisesBarChart"></canvas>
    </div>
</div>
