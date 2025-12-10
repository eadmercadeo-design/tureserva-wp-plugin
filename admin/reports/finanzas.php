<?php
/**
 * Pestaña: Reportes Financieros
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="tureserva-filters-bar">
    <h3>Resumen Financiero</h3>
    <div class="date-controls">
         <select id="fin-period">
            <option value="this_month">Este Mes</option>
            <option value="last_month">Mes Pasado</option>
            <option value="this_year">Este Año</option>
        </select>
    </div>
</div>

<div class="tureserva-kpi-grid financial">
    <div class="kpi-card success">
        <h4>Ingresos Brutos</h4>
        <h2 id="fin-gross">--</h2>
    </div>
    <div class="kpi-card primary">
        <h4>Ingresos Netos</h4>
        <h2 id="fin-net">--</h2>
    </div>
    <div class="kpi-card warning">
        <h4>Impuestos</h4>
        <h2 id="fin-taxes">--</h2>
    </div>
    <div class="kpi-card info">
        <h4>Limpieza y Extras</h4>
        <h2 id="fin-fees">--</h2>
    </div>
</div>

<div class="tureserva-charts-row">
    <div class="chart-container large">
        <h3>Ingresos Mensuales</h3>
        <canvas id="incomeLineChart"></canvas>
    </div>
</div>

<div class="tureserva-charts-row">
    <div class="chart-container half">
        <h3>Distribución de Cargos</h3>
        <canvas id="chargesDoughnutChart"></canvas>
    </div>
    <div class="chart-container half">
        <h3>Ranking Alojamiento (Ingresos)</h3>
        <canvas id="roomRevenueChart"></canvas>
    </div>
</div>
