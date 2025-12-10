<?php
/**
 * ==========================================================
 * CORE: Reportes — TuReserva
 * ==========================================================
 * Manejo de endpoints AJAX y lógica de negocio para reportes.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 1. DASHBOARD DATA
// =======================================================
add_action( 'wp_ajax_tureserva_get_dashboard_data', 'tureserva_get_dashboard_data' );
function tureserva_get_dashboard_data() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Sin permisos' );

    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'this_month';
    $start  = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : '';
    $end    = isset($_POST['end']) ? sanitize_text_field($_POST['end']) : '';

    $dates = tureserva_get_date_range($period, $start, $end);
    
    // Simular datos si no hay suficientes reservas reales para visualizar gráficos bonitos
    // En producción esto debería ser 100% real.
    $kpis = tureserva_calc_kpis($dates['start'], $dates['end']);
    
    $data = array(
        'period' => $dates,
        'kpis'   => $kpis,
        'charts' => array(
            'timeline' => tureserva_get_timeline_data($dates['start'], $dates['end']),
            'canales'  => array(
                'labels' => ['Directo', 'Booking', 'Airbnb', 'Expedia'],
                'data'   => [rand(10,50), rand(5,20), rand(5,30), rand(1,10)] // Mock
            ),
            'paises'   => array(
                'labels' => ['USA', 'España', 'México', 'Francia', 'Alemania'],
                'data'   => [rand(20,50), rand(20,40), rand(10,30), rand(5,15), rand(5,15)] // Mock
            )
        )
    );

    wp_send_json_success( $data );
}

// =======================================================
// 2. RESERVATION REPORTS
// =======================================================
add_action( 'wp_ajax_tureserva_get_reservation_reports', 'tureserva_get_reservation_reports' );
function tureserva_get_reservation_reports() {
    // TODO: Implementar lógica real filtrada
    // Retornamos estructura mockeada por ahora para UI dev
    wp_send_json_success(array('rows' => [])); 
}

// =======================================================
// 3. FINANCIAL REPORTS
// =======================================================
add_action( 'wp_ajax_tureserva_get_financial_reports', 'tureserva_get_financial_reports' );
function tureserva_get_financial_reports() {
    // TODO: Implementar lógica financiera real
    wp_send_json_success(array());
}

// =======================================================
// 4. LOGS
// =======================================================
add_action( 'wp_ajax_tureserva_get_logs', 'tureserva_get_logs' );
function tureserva_get_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tureserva_logs';
    
    // Check table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        tureserva_create_logs_table();
    }

    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY fecha DESC LIMIT 50");
    wp_send_json_success($logs);
}

// =======================================================
// HELPERS
// =======================================================
function tureserva_get_date_range($period, $custom_start, $custom_end) {
    switch($period) {
        case 'last_month':
            return [
                'start' => date('Y-m-d', strtotime('first day of last month')),
                'end'   => date('Y-m-d', strtotime('last day of last month'))
            ];
        case 'this_year':
            return [
                'start' => date('Y-01-01'),
                'end'   => date('Y-12-31')
            ];
        case 'custom':
            return ['start' => $custom_start, 'end' => $custom_end];
        case 'this_month':
        default:
            return [
                'start' => date('Y-m-01'),
                'end'   => date('Y-m-t')
            ];
    }
}

function tureserva_calc_kpis($start, $end) {
    // Logic to query 'tureserva_reserva' CPT meta
    // Simplificado para demo
    
    $args = array(
        'post_type' => 'tureserva_reserva',
        'meta_query' => array(
            array('key' => '_tureserva_checkin', 'value' => $start, 'compare' => '>='),
            array('key' => '_tureserva_checkin', 'value' => $end, 'compare' => '<=')
        ),
        'posts_per_page' => -1
    );
    
    $query = new WP_Query($args);
    $total_reservas = $query->found_posts;
    $ingresos = 0;
    
    foreach($query->posts as $p) {
        $ingresos += (float) get_post_meta($p->ID, '_tureserva_precio_total', true);
    }
    
    // Mock values if empty to show UI
    if ($total_reservas == 0) {
        return array(
            'total_reservas' => 0,
            'ocupacion'      => 0,
            'ingresos'       => '$0',
            'adr'            => '$0'
        );
    }

    return array(
        'total_reservas' => $total_reservas,
        'ocupacion'      => rand(40, 95), // Mock calculation for now
        'ingresos'       => '$' . number_format($ingresos, 2),
        'adr'            => '$' . number_format($ingresos / $total_reservas, 2)
    );
}

function tureserva_get_timeline_data($start, $end) {
    // Generate dates between start and end
    $period = new DatePeriod(
         new DateTime($start),
         new DateInterval('P1D'),
         new DateTime($end)
    );
    
    $labels = [];
    $data = [];
    
    foreach($period as $date) {
        $labels[] = $date->format('d M');
        $data[] = rand(0, 5); // Mock daily bookings
    }
    
    return ['labels' => $labels, 'data' => $data];
}

function tureserva_create_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tureserva_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        fecha datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        tipo varchar(50) NOT NULL,
        mensaje text NOT NULL,
        meta_json text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

