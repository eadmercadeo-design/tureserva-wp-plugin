<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Calendario — TuReserva
 * ==========================================================
 * Muestra un calendario interactivo con:
 *  - Reservas (pendientes, confirmadas, canceladas)
 *  - Bloqueos manuales
 *  - Filtros por año, alojamiento y estado
 *  - Vista mensual y vista por alojamiento (timeline)
 * Usa FullCalendar.js y datos vía AJAX (core-calendar.php)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Calendario"
// =======================================================
// NOTA: El submenú del calendario se registra en menu-reservas.php
// para mantener la coherencia con el resto de submenús del plugin.
// Esta función ya no registra el menú, solo contiene la lógica de visualización.

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS SOLO EN ESTE PANEL
// =======================================================
add_action('admin_enqueue_scripts', 'tureserva_calendario_assets');
function tureserva_calendario_assets($hook) {
    // Verificar si estamos en la página del calendario
    // El hook para submenús bajo CPTs puede ser: tureserva_reservas_page_tureserva_calendario
    // También verificamos por GET parameter como respaldo
    $is_calendar_page = (
        strpos($hook, 'tureserva_calendario') !== false ||
        (isset($_GET['page']) && $_GET['page'] === 'tureserva_calendario') ||
        (isset($_GET['post_type']) && $_GET['post_type'] === 'tureserva_reserva' && isset($_GET['page']) && $_GET['page'] === 'tureserva_calendario')
    );
    
    if (!$is_calendar_page) {
        return;
    }

    // 🎨 FullCalendar base
    wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css', [], '6.1.8');
    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', ['jquery'], '6.1.8', true);

    // 🧱 Resource Timeline (vista horizontal tipo MotoPress)
    wp_enqueue_script(
        'fullcalendar-timeline',
        'https://cdn.jsdelivr.net/npm/@fullcalendar/resource-timeline@6.1.8/index.global.min.js',
        ['fullcalendar-js'],
        '6.1.8',
        true
    );

    // 💬 Tooltips (Tippy.js)
    wp_enqueue_script('tippy-core', 'https://unpkg.com/@popperjs/core@2', [], null, true);
    wp_enqueue_script('tippy-js', 'https://unpkg.com/tippy.js@6', ['tippy-core'], null, true);

    // 📜 JS personalizado del plugin (FullCalendar mensual)
    wp_enqueue_script(
        'tureserva-calendar-js',
        TURESERVA_URL . 'assets/js/tureserva-calendar.js',
        ['jquery', 'fullcalendar-js', 'tippy-js'],
        TURESERVA_VERSION,
        true
    );

    // 🔒 Variables para AJAX
    wp_localize_script('tureserva-calendar-js', 'tureservaCalendar', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_calendar_nonce'),
        'year'     => date('Y'),
    ]);

    // 🎨 CSS propio
    wp_enqueue_style(
        'tureserva-calendar-css',
        TURESERVA_URL . 'assets/css/tureserva-calendar.css',
        [],
        TURESERVA_VERSION
    );

    // 🔧 Cargar script de timeline si existe y se necesita
    $view = isset($_GET['view']) ? $_GET['view'] : 'month';
    if ($view === 'timeline' && file_exists(TURESERVA_PATH . 'assets/js/tureserva-calendar-timeline.js')) {
        wp_enqueue_script(
            'tureserva-calendar-timeline-js',
            TURESERVA_URL . 'assets/js/tureserva-calendar-timeline.js',
            ['jquery', 'fullcalendar-js', 'fullcalendar-timeline'],
            TURESERVA_VERSION,
            true
        );
        
        // Variables para timeline
        wp_localize_script('tureserva-calendar-timeline-js', 'tureservaTimeline', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tureserva_calendar_nonce'),
        ]);
    }
}

// =======================================================
// 🗓️ INTERFAZ DEL PANEL DE CALENDARIO
// =======================================================
function tureserva_vista_calendario() {
    if (!current_user_can('manage_options')) return;

    // 🔍 Cargar alojamientos para filtro
    $alojamientos = get_posts([
        'post_type'      => 'trs_alojamiento',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);
    ?>
    <div class="wrap">
        <h1>📅 <?php _e('Calendario de Reservas — TuReserva', 'tureserva'); ?></h1>

        <!-- 🧭 Pestañas de navegación -->
        <h2 class="nav-tab-wrapper" style="margin-bottom:20px;">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=tureserva_reserva&page=tureserva_calendario&view=month')); ?>"
               class="nav-tab <?php echo (!isset($_GET['view']) || $_GET['view'] == 'month') ? 'nav-tab-active' : ''; ?>">
               Vista mensual
            </a>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=tureserva_reserva&page=tureserva_calendario&view=timeline')); ?>"
               class="nav-tab <?php echo (isset($_GET['view']) && $_GET['view'] == 'timeline') ? 'nav-tab-active' : ''; ?>">
               Vista por alojamiento
            </a>
        </h2>

        <?php if (!isset($_GET['view']) || $_GET['view'] == 'month'): ?>
            <!-- 🗓️ Vista mensual -->
            <p><?php _e('Consulta las reservas y bloqueos activos. Los colores indican el estado:', 'tureserva'); ?></p>

            <ul style="display:flex;gap:10px;flex-wrap:wrap;">
                <li><span style="background:#2ecc71;padding:3px 10px;color:#fff;border-radius:3px;">Confirmada</span></li>
                <li><span style="background:#f1c40f;padding:3px 10px;color:#000;border-radius:3px;">Pendiente</span></li>
                <li><span style="background:#e74c3c;padding:3px 10px;color:#fff;border-radius:3px;">Cancelada</span></li>
                <li><span style="background:#95a5a6;padding:3px 10px;color:#fff;border-radius:3px;">Bloqueo</span></li>
            </ul>

            <form id="tureserva-calendario-filtros" method="GET" style="margin-top:20px;">
                <input type="hidden" name="page" value="tureserva_calendario">

                <label><strong><?php _e('Año:', 'tureserva'); ?></strong></label>
                <input type="number" name="year" id="tureserva_year" value="<?php echo esc_attr(date('Y')); ?>" min="2020" max="2050" style="width:80px;margin-right:10px;">

                <label><strong><?php _e('Alojamiento:', 'tureserva'); ?></strong></label>
                <select name="alojamiento" id="tureserva_alojamiento" style="min-width:200px;margin-right:10px;">
                    <option value="0"><?php _e('Todos', 'tureserva'); ?></option>
                    <?php foreach ($alojamientos as $aloj) : ?>
                        <option value="<?php echo esc_attr($aloj->ID); ?>"><?php echo esc_html($aloj->post_title); ?></option>
                    <?php endforeach; ?>
                </select>

                <label><strong><?php _e('Estado:', 'tureserva'); ?></strong></label>
                <select name="estado" id="tureserva_estado" style="min-width:160px;margin-right:10px;">
                    <option value=""><?php _e('Todos', 'tureserva'); ?></option>
                    <option value="confirmada"><?php _e('Confirmada', 'tureserva'); ?></option>
                    <option value="pendiente"><?php _e('Pendiente', 'tureserva'); ?></option>
                    <option value="cancelada"><?php _e('Cancelada', 'tureserva'); ?></option>
                </select>

                <button type="button" id="tureserva-filtrar" class="button button-primary">🔍 <?php _e('Filtrar', 'tureserva'); ?></button>
            </form>

            <div id="tureserva-calendar" style="margin-top:30px;min-height:650px;background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:15px;"></div>

        <?php else: ?>
            <!-- 🧱 Vista tipo timeline -->
            <p><?php _e('Visualiza la ocupación por alojamiento en formato horizontal. Ideal para gestión de disponibilidad.', 'tureserva'); ?></p>

            <div id="tureserva-calendar-timeline" style="margin-top:20px;min-height:700px;background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:15px;"></div>
        <?php endif; ?>
    </div>
    <?php
}
