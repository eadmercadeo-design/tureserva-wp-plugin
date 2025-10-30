<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Calendario — TuReserva
 * ==========================================================
 * Muestra un calendario interactivo con:
 *  - Reservas (pendientes, confirmadas, canceladas)
 *  - Bloqueos manuales
 *  - Filtros por año, alojamiento y estado
 * Usa FullCalendar.js y datos vía AJAX (core-calendar.php)
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Calendario"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_calendario' );
function tureserva_menu_calendario() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        'Calendario de Reservas',
        'Calendario',
        'manage_options',
        'tureserva_calendario',
        'tureserva_vista_calendario'
    );
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS SOLO EN ESTE PANEL
// =======================================================
add_action( 'admin_enqueue_scripts', 'tureserva_calendario_assets' );
function tureserva_calendario_assets( $hook ) {
    if ( strpos( $hook, 'tureserva_calendario' ) === false ) return;

    // FullCalendar desde CDN (versión ligera)
    wp_enqueue_style( 'fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css', array(), '6.1.8' );
    wp_enqueue_script( 'fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', array(), '6.1.8', true );

    // JS personalizado del plugin
    wp_enqueue_script( 'tureserva-calendar-js', TURESERVA_URL . 'assets/js/tureserva-calendar.js', array( 'jquery', 'fullcalendar-js' ), TURESERVA_VERSION, true );

    // Datos para AJAX
    wp_localize_script( 'tureserva-calendar-js', 'tureservaCalendar', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tureserva_calendar_nonce' ),
        'year'     => date( 'Y' ),
    ) );

    // CSS propio
    wp_enqueue_style( 'tureserva-calendar-css', TURESERVA_URL . 'assets/css/tureserva-calendar.css', array(), TURESERVA_VERSION );
}

// =======================================================
// 🗓️ INTERFAZ DEL PANEL DE CALENDARIO
// =======================================================
function tureserva_vista_calendario() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Filtros de búsqueda
    $alojamientos = get_posts( array(
        'post_type' => 'tureserva_alojamiento',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ) );

    ?>
    <div class="wrap">
        <h1>📅 Calendario de Reservas — TuReserva</h1>
        <p>Consulta las reservas y bloqueos activos. Los colores indican el estado:</p>
        <ul>
            <li><span style="background:#2ecc71;padding:3px 10px;color:#fff;border-radius:3px;">Confirmada</span></li>
            <li><span style="background:#f1c40f;padding:3px 10px;color:#000;border-radius:3px;">Pendiente</span></li>
            <li><span style="background:#e74c3c;padding:3px 10px;color:#fff;border-radius:3px;">Cancelada</span></li>
            <li><span style="background:#95a5a6;padding:3px 10px;color:#fff;border-radius:3px;">Bloqueo</span></li>
        </ul>

        <form id="tureserva-calendario-filtros" method="GET" style="margin-top:20px;">
            <input type="hidden" name="page" value="tureserva_calendario">
            <label><strong>Año:</strong></label>
            <input type="number" name="year" id="tureserva_year" value="<?php echo esc_attr( date( 'Y' ) ); ?>" min="2020" max="2050" style="width:80px;margin-right:10px;">

            <label><strong>Alojamiento:</strong></label>
            <select name="alojamiento" id="tureserva_alojamiento" style="min-width:200px;margin-right:10px;">
                <option value="0">Todos</option>
                <?php foreach ( $alojamientos as $aloj ) : ?>
                    <option value="<?php echo esc_attr( $aloj->ID ); ?>"><?php echo esc_html( $aloj->post_title ); ?></option>
                <?php endforeach; ?>
            </select>

            <label><strong>Estado:</strong></label>
            <select name="estado" id="tureserva_estado" style="min-width:160px;margin-right:10px;">
                <option value="">Todos</option>
                <option value="confirmada">Confirmada</option>
                <option value="pendiente">Pendiente</option>
                <option value="cancelada">Cancelada</option>
            </select>

            <button type="button" id="tureserva-filtrar" class="button button-primary">🔍 Filtrar</button>
        </form>

        <div id="tureserva-calendar" style="margin-top:30px;"></div>
    </div>
    <?php
}
