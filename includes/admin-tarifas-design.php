<?php
/**
 * Admin Design: Tarifas (CPT)
 * - Interfaz moderna para el listado de tarifas
 * - Panel lateral "Añadir nueva tarifa"
 * - Filtros y Búsqueda visuales
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 1. 🎨 CSS Y JS PARA LA PANTALLA DE TARIFAS
// ==========================================================
add_action('admin_head', 'tureserva_tarifas_admin_assets');
function tureserva_tarifas_admin_assets() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'tureserva_tarifa') return;
    ?>
    <style>
        /* Ocultar elementos innecesarios */
        .subsubsub, .search-box, .tablenav.top { display: none !important; }
        
        /* Contenedor Principal */
        .wrap { max-width: 100%; margin-right: 20px; }

        /* --------------------------------------------------
           NAV BAR & FILTERS
           -------------------------------------------------- */
        .tureserva-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .tureserva-filters {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .tureserva-filters select, 
        .tureserva-filters input {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 6px 12px;
            color: #555;
            background: #f9f9f9;
            min-width: 140px;
        }

        .tureserva-search-wrapper {
            position: relative;
        }
        .tureserva-search-wrapper input {
            padding-left: 30px;
        }
        .tureserva-search-wrapper::before {
            content: "\f179"; /* Dashicon search */
            font-family: dashicons;
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .tureserva-add-btn {
            background: #2271b1 !important;
            color: #fff !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            cursor: pointer;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.2s;
        }
        .tureserva-add-btn:hover {
            background: #135e96 !important;
        }

        /* --------------------------------------------------
           TABLE STYLES
           -------------------------------------------------- */
        .wp-list-table.posts {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .wp-list-table.posts thead th {
            background: #fff;
            font-weight: 600;
            color: #646970;
            border-bottom: 1px solid #f0f0f1;
            padding: 15px 10px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .wp-list-table.posts tbody tr:nth-child(odd) { background: #fcfcfc; }
        .wp-list-table.posts tbody tr:hover { background: #f0f6fc; }

        .wp-list-table.posts td {
            padding: 15px 10px;
            vertical-align: middle;
            color: #1d2327;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f1;
        }

        .column-title strong { font-size: 15px; color: #2271b1; }
        .row-actions { visibility: hidden; }
        tr:hover .row-actions { visibility: visible; }

        /* Status Badge */
        .tureserva-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-active { background: #e6fffa; color: #2c7a7b; }
        .status-inactive { background: #fff5f5; color: #c53030; }

        /* --------------------------------------------------
           SIDEBAR PANEL
           -------------------------------------------------- */
        .tureserva-sidebar-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99998;
            display: none;
            backdrop-filter: blur(2px);
        }

        .tureserva-sidebar {
            position: fixed;
            top: 0; right: -500px; bottom: 0;
            width: 450px;
            background: #fff;
            z-index: 99999;
            box-shadow: -5px 0 25px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .tureserva-sidebar.open { right: 0; }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fcfcfc;
        }
        .sidebar-header h2 { margin: 0; font-size: 18px; }
        .close-sidebar { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }

        .sidebar-content {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fcfcfc;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Form Fields */
        .ts-field { margin-bottom: 20px; }
        .ts-field label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; }
        .ts-field input, .ts-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .ts-row { display: flex; gap: 15px; }
        .ts-col { flex: 1; }

    </style>
    <script>
    jQuery(document).ready(function($) {
        // Mover filtros al top
        $('.wp-header-end').after($('.tureserva-top-bar'));

        // Abrir Sidebar
        $('#tureserva-add-rate-btn').on('click', function(e) {
            e.preventDefault();
            $('.tureserva-sidebar-overlay').fadeIn(200);
            $('.tureserva-sidebar').addClass('open');
        });

        // Cerrar Sidebar
        $('.close-sidebar, .tureserva-sidebar-overlay, #cancel-rate').on('click', function() {
            $('.tureserva-sidebar').removeClass('open');
            $('.tureserva-sidebar-overlay').fadeOut(200);
        });

        // Guardar AJAX
        $('#save-rate').on('click', function() {
            const btn = $(this);
            const data = {
                action: 'tureserva_save_rate_ajax',
                nonce: '<?php echo wp_create_nonce("tureserva_rate_nonce"); ?>',
                title: $('#rate_title').val(),
                price: $('#rate_price').val(),
                accommodation: $('#rate_accommodation').val(),
                season: $('#rate_season').val(),
                start: $('#rate_start').val(),
                end: $('#rate_end').val()
            };

            if(!data.title || !data.price) {
                alert('Nombre y Precio son obligatorios');
                return;
            }

            btn.prop('disabled', true).text('Guardando...');

            $.post(ajaxurl, data, function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                    btn.prop('disabled', false).text('Guardar Tarifa');
                }
            });
        });
    });
    </script>
    <?php
}

// ==========================================================
// 2. 🏗️ HTML DE LA BARRA SUPERIOR Y SIDEBAR
// ==========================================================
add_action('admin_notices', 'tureserva_render_rate_ui');
function tureserva_render_rate_ui() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'tureserva_tarifa') return;
    ?>
    
    <!-- Barra Superior de Filtros -->
    <div class="tureserva-top-bar">
        <div class="tureserva-filters">
            <div class="tureserva-search-wrapper">
                <input type="text" placeholder="Buscar tarifa...">
            </div>
            
            <select>
                <option>Todas las temporadas</option>
                <?php 
                $temporadas = get_posts(['post_type'=>'temporada', 'posts_per_page'=>-1]);
                foreach($temporadas as $t) echo "<option>{$t->post_title}</option>";
                ?>
            </select>

            <select>
                <option>Todos los alojamientos</option>
                <?php 
                $alojamientos = get_posts(['post_type'=>'trs_alojamiento', 'posts_per_page'=>-1]);
                foreach($alojamientos as $a) echo "<option>{$a->post_title}</option>";
                ?>
            </select>

            <select>
                <option>Estado: Todos</option>
                <option>Activa</option>
                <option>Inactiva</option>
            </select>
        </div>

        <button id="tureserva-add-rate-btn" class="tureserva-add-btn">
            <span class="dashicons dashicons-plus-alt2"></span> Añadir nueva tarifa
        </button>
    </div>

    <!-- Sidebar Panel -->
    <div class="tureserva-sidebar-overlay"></div>
    <div class="tureserva-sidebar">
        <div class="sidebar-header">
            <h2>Nueva Tarifa</h2>
            <button class="close-sidebar">&times;</button>
        </div>
        
        <div class="sidebar-content">
            <div class="ts-field">
                <label>Nombre de la tarifa</label>
                <input type="text" id="rate_title" placeholder="Ej: Tarifa Estándar 2025">
            </div>

            <div class="ts-row">
                <div class="ts-col ts-field">
                    <label>Precio Base (Noche)</label>
                    <input type="number" id="rate_price" placeholder="0.00">
                </div>
                <div class="ts-col ts-field">
                    <label>Estado</label>
                    <select id="rate_status">
                        <option value="publish">Activa</option>
                        <option value="draft">Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="ts-field">
                <label>Alojamiento Asociado</label>
                <select id="rate_accommodation">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($alojamientos as $a) echo "<option value='{$a->ID}'>{$a->post_title}</option>"; ?>
                </select>
            </div>

            <div class="ts-field">
                <label>Temporada Asociada</label>
                <select id="rate_season">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($temporadas as $t) echo "<option value='{$t->ID}'>{$t->post_title}</option>"; ?>
                </select>
            </div>

            <div class="ts-row">
                <div class="ts-col ts-field">
                    <label>Fecha Inicio</label>
                    <input type="date" id="rate_start">
                </div>
                <div class="ts-col ts-field">
                    <label>Fecha Fin</label>
                    <input type="date" id="rate_end">
                </div>
            </div>
            
            <div class="ts-field">
                <label>Opciones Adicionales</label>
                <div style="background:#f9f9f9; padding:10px; border-radius:4px; font-size:13px; color:#666;">
                    Configuración avanzada de noches mínimas y máximas disponible en la edición completa.
                </div>
            </div>

        </div>

        <div class="sidebar-footer">
            <button id="cancel-rate" class="button">Cancelar</button>
            <button id="save-rate" class="button button-primary button-large">Guardar Tarifa</button>
        </div>
    </div>
    <?php
}

// ==========================================================
// 3. 📊 COLUMNAS PERSONALIZADAS
// ==========================================================
add_filter('manage_tureserva_tarifa_posts_columns', function($columns) {
    return [
        'cb' => $columns['cb'],
        'title' => 'Nombre de la Tarifa',
        'price' => 'Precio Base',
        'accommodation' => 'Alojamiento',
        'season' => 'Temporada',
        'dates' => 'Rango de Fechas',
        'status_custom' => 'Estado'
    ];
});

add_action('manage_tureserva_tarifa_posts_custom_column', function($column, $post_id) {
    switch($column) {
        case 'price':
            $price = get_post_meta($post_id, '_tureserva_precio_base', true);
            echo $price ? '$' . number_format($price, 2) : '—';
            break;
        case 'accommodation':
            $id = get_post_meta($post_id, '_tureserva_alojamiento_id', true);
            echo $id ? get_the_title($id) : '—';
            break;
        case 'season':
            $id = get_post_meta($post_id, '_tureserva_temporada_id', true);
            echo $id ? get_the_title($id) : '—';
            break;
        case 'dates':
            $start = get_post_meta($post_id, '_tureserva_fecha_inicio', true);
            $end = get_post_meta($post_id, '_tureserva_fecha_fin', true);
            echo ($start && $end) ? "{$start} / {$end}" : '—';
            break;
        case 'status_custom':
            $status = get_post_status($post_id);
            $label = $status === 'publish' ? 'Activa' : 'Inactiva';
            $class = $status === 'publish' ? 'status-active' : 'status-inactive';
            echo "<span class='tureserva-status {$class}'>{$label}</span>";
            break;
    }
}, 10, 2);

// ==========================================================
// 4. ⚡ AJAX HANDLER
// ==========================================================
add_action('wp_ajax_tureserva_save_rate_ajax', 'tureserva_save_rate_ajax');
function tureserva_save_rate_ajax() {
    check_ajax_referer('tureserva_rate_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error('Permisos insuficientes');

    $title = sanitize_text_field($_POST['title']);
    
    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_type' => 'tureserva_tarifa',
        'post_status' => 'publish'
    ]);

    if (is_wp_error($post_id)) wp_send_json_error($post_id->get_error_message());

    update_post_meta($post_id, '_tureserva_precio_base', sanitize_text_field($_POST['price']));
    update_post_meta($post_id, '_tureserva_alojamiento_id', sanitize_text_field($_POST['accommodation']));
    update_post_meta($post_id, '_tureserva_temporada_id', sanitize_text_field($_POST['season']));
    update_post_meta($post_id, '_tureserva_fecha_inicio', sanitize_text_field($_POST['start']));
    update_post_meta($post_id, '_tureserva_fecha_fin', sanitize_text_field($_POST['end']));

    wp_send_json_success();
}
