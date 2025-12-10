<?php
/**
 * Pestaña: Reportes de Reservas
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="tureserva-filters-bar">
    <input type="date" id="res-filter-start" placeholder="Desde">
    <input type="date" id="res-filter-end" placeholder="Hasta">
    
    <select id="res-filter-status">
        <option value="">Estado: Todos</option>
        <option value="confirmada">Confirmada</option>
        <option value="pendiente">Pendiente</option>
        <option value="cancelada">Cancelada</option>
    </select>
    
    <select id="res-filter-room">
        <option value="">Alojamiento: Todos</option>
        <!-- Populated via JS/PHP -->
    </select>
    
    <button id="apply-res-filters" class="button button-primary">Filtrar</button>
</div>

<div class="tureserva-charts-row">
    <div class="chart-container full">
        <h3>Estado de Reservas</h3>
        <canvas id="reservasStatusChart"></canvas>
    </div>
</div>

<div class="tureserva-table-container">
    <table id="reservas-table" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Huésped</th>
                <th>Check-in / Out</th>
                <th>Noches</th>
                <th>Canal</th>
                <th>País</th>
                <th>Impuestos</th>
                <th>Servicios</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="reservas-table-body">
            <!-- Data via AJAX -->
        </tbody>
    </table>
    <div id="reservas-table-pagination" class="tablenav bottom">
        <!-- Pagination controls -->
    </div>
</div>
