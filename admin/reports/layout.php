<?php
/**
 * Layout principal del Módulo de Reportes
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap tureserva-reports-wrap">
    <h1 class="wp-heading-inline">📊 Reportes y Estadísticas</h1>
    <p class="description">Analítica completa de tu negocio hotelero: ocupación, ingresos y rendimiento.</p>

    <div class="nav-tab-wrapper tureserva-reports-nav">
        <a href="#dashboard" class="nav-tab nav-tab-active" data-tab="dashboard">Dashboard</a>
        <a href="#reservas" class="nav-tab" data-tab="reservas">Reservas</a>
        <a href="#finanzas" class="nav-tab" data-tab="finanzas">Finanzas</a>
        <a href="#exportables" class="nav-tab" data-tab="exportables">Exportar y Logs</a>
    </div>

    <div class="tureserva-reports-content">
        <div id="tab-dashboard" class="report-tab-content active">
            <?php include_once 'dashboard.php'; ?>
        </div>
        <div id="tab-reservas" class="report-tab-content" style="display:none;">
            <?php include_once 'reservas.php'; ?>
        </div>
        <div id="tab-finanzas" class="report-tab-content" style="display:none;">
            <?php include_once 'finanzas.php'; ?>
        </div>
        <div id="tab-exportables" class="report-tab-content" style="display:none;">
            <?php include_once 'exportables.php'; ?>
        </div>
    </div>
</div>
