<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Reportes — TuReserva
 * ==========================================================
 * Muestra estadísticas de reservas:
 *  - Ocupación
 *  - Ingresos totales y promedio
 *  - Cantidad por estado
 *  - Filtros por fecha, alojamiento y estado
 * Usa datos del endpoint AJAX: tureserva_get_reporte
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Reportes"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_reportes' );
function tureserva_menu_reportes() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        'Reportes de Reservas',
        'Reportes',
        'manage_options',
        'tureserva_reportes',
        'tureserva_vista_reportes'
    );
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS
// =======================================================
add_action( 'admin_enqueue_scripts', 'tureserva_reportes_assets' );
function tureserva_reportes_assets( $hook ) {
    if ( strpos( $hook, 'tureserva_reportes' ) === false ) return;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true );
    wp_enqueue_script( 'tureserva-reportes-js', TURESERVA_URL . 'assets/js/tureserva-reportes.js', array( 'jquery', 'chartjs' ), TURESERVA_VERSION, true );

    wp_localize_script( 'tureserva-reportes-js', 'tureservaReportes', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tureserva_reporte_nonce' ),
    ));

    wp_enqueue_style( 'tureserva-reportes-css', TURESERVA_URL . 'assets/css/tureserva-reportes.css', array(), TURESERVA_VERSION );
}

// =======================================================
// 📊 INTERFAZ DEL PANEL DE REPORTES
// =======================================================
function tureserva_vista_reportes() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $alojamientos = get_posts( array(
        'post_type' => 'tureserva_alojamiento',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ) );

    ?>
    <div class="wrap">
        <h1>📈 Reportes — TuReserva</h1>
        <p>Consulta las estadísticas de ocupación e ingresos.</p>

        <form id="tureserva-filtros-reportes" style="margin-top:20px;">
            <label><strong>Desde:</strong></label>
            <input type="date" id="tureserva_inicio" value="<?php echo esc_attr( date('Y-m-01') ); ?>" style="margin-right:10px;">
            <label><strong>Hasta:</strong></label>
            <input type="date" id="tureserva_fin" value="<?php echo esc_attr( date('Y-m-t') ); ?>" style="margin-right:10px;">

            <label><strong>Alojamiento:</strong></label>
            <select id="tureserva_alojamiento" style="min-width:200px;margin-right:10px;">
                <option value="0">Todos</option>
                <?php foreach ( $alojamientos as $a ) : ?>
                    <option value="<?php echo esc_attr( $a->ID ); ?>"><?php echo esc_html( $a->post_title ); ?></option>
                <?php endforeach; ?>
            </select>

            <label><strong>Estado:</strong></label>
            <select id="tureserva_estado" style="min-width:160px;margin-right:10px;">
                <option value="">Todos</option>
                <option value="confirmada">Confirmada</option>
                <option value="pendiente">Pendiente</option>
                <option value="cancelada">Cancelada</option>
            </select>

            <button type="button" id="tureserva-boton-reporte" class="button button-primary">📊 Generar reporte</button>
        </form>

        <hr style="margin:20px 0;">

        <div id="tureserva-resultados" style="display:none;">
            <h2>📅 Resultados del periodo</h2>
            <div class="tureserva-stats">
                <div class="stat-box">
                    <h3 id="total-reservas">0</h3>
                    <p>Total de Reservas</p>
                </div>
                <div class="stat-box">
                    <h3 id="ingresos-totales">$0.00</h3>
                    <p>Ingresos Totales</p>
                </div>
                <div class="stat-box">
                    <h3 id="promedio-reserva">$0.00</h3>
                    <p>Promedio por Reserva</p>
                </div>
                <div class="stat-box">
                    <h3 id="ocupacion-dias">0</h3>
                    <p>Días Ocupados</p>
                </div>
            </div>

            <canvas id="tureserva-chart" width="400" height="160" style="margin-top:30px;"></canvas>
        </div>
    </div>
    <?php
}
