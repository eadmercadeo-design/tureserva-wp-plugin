<?php
/**
 * Pestaña: Exportables y Logs
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="export-section">
    <h3>📥 Exportar Datos</h3>
    <p>Descarga reportes completos en formato CSV o PDF para tu contabilidad.</p>
    
    <div class="export-controls">
        <select id="export-type">
            <option value="reservations">Reservas</option>
            <option value="financial">Financiero</option>
        </select>
        
        <input type="date" id="export-start">
        <input type="date" id="export-end">
        
        <button class="button button-secondary" id="btn-export-csv"><span class="dashicons dashicons-media-spreadsheet"></span> CSV</button>
        <button class="button button-secondary" id="btn-export-pdf"><span class="dashicons dashicons-pdf"></span> PDF</button>
    </div>
</div>

<hr>

<div class="logs-section">
    <h3>📜 Logs del Sistema</h3>
    <p>Registro de actividades: sincronización, errores, pagos, auditoría.</p>
    
    <table class="wp-list-table widefat fixed striped" id="logs-table">
        <thead>
            <tr>
                <th width="150">Fecha</th>
                <th width="100">Tipo</th>
                <th>Mensaje</th>
                <th width="100">Detalles</th>
            </tr>
        </thead>
        <tbody id="logs-table-body">
            <!-- AJAX -->
        </tbody>
    </table>
    <br>
    <button id="refresh-logs" class="button button-small">Refrescar Logs</button>
</div>
