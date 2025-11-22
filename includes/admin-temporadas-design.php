<?php
/**
 * Admin Design: Temporadas (CPT)
 * - Interfaz mejorada para el listado
 * - Panel "Añadir nueva temporada" en la misma pantalla (Toggle)
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 1. 🎨 CSS Y JS PARA LA PANTALLA DE TEMPORADAS
// ==========================================================
add_action('admin_head', 'tureserva_temporadas_admin_assets');
function tureserva_temporadas_admin_assets() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'temporada') return;
    ?>
    <style>
        /* Ocultar elementos innecesarios */
        .subsubsub { display: none; }
        
        /* Botón Añadir Nuevo */
        .page-title-action {
            background: #2271b1 !important;
            color: #fff !important;
            border: none !important;
            padding: 4px 12px !important;
            border-radius: 4px !important;
            transition: all 0.2s !important;
        }
        .page-title-action:hover {
            background: #135e96 !important;
        }

        /* Tabla Limpia */
        .wp-list-table.posts {
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
        }

        .wp-list-table.posts thead th {
            background: #f0f0f1;
            font-weight: 600;
            color: #1d2327;
            border-bottom: 1px solid #c3c4c7;
        }

        /* Panel de Creación (Oculto por defecto) */
        #tureserva-add-season-panel {
            display: none;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #2271b1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            max-width: 800px;
        }

        #tureserva-add-season-panel h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f1;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tureserva-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        .tureserva-form-full {
            grid-column: span 2;
        }

        .tureserva-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1d2327;
        }

        .tureserva-field input[type="text"],
        .tureserva-field input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
        }

        /* Días Checkboxes */
        .tureserva-days-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .tureserva-days-grid label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        /* Botones del panel */
        .tureserva-panel-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // Interceptar clic en "Añadir nueva"
        $('.page-title-action').on('click', function(e) {
            e.preventDefault();
            $('#tureserva-add-season-panel').slideToggle();
            $('html, body').animate({
                scrollTop: $("#tureserva-add-season-panel").offset().top - 50
            }, 500);
        });

        // Cancelar
        $('#tureserva-cancel-season').on('click', function() {
            $('#tureserva-add-season-panel').slideUp();
        });

        // Guardar AJAX
        $('#tureserva-save-season').on('click', function() {
            const btn = $(this);
            const title = $('#season_title').val();
            const start = $('#season_start').val();
            const end = $('#season_end').val();
            
            let days = [];
            $('input[name="season_days[]"]:checked').each(function() {
                days.push($(this).val());
            });

            if(!title || !start || !end) {
                alert('Por favor complete los campos obligatorios.');
                return;
            }

            btn.prop('disabled', true).text('Guardando...');

            $.post(ajaxurl, {
                action: 'tureserva_save_season_ajax',
                title: title,
                start: start,
                end: end,
                days: days,
                nonce: '<?php echo wp_create_nonce("tureserva_season_nonce"); ?>'
            }, function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                    btn.prop('disabled', false).text('Guardar Temporada');
                }
            });
        });
    });
    </script>
    <?php
}

// ==========================================================
// 2. 📝 FORMULARIO INYECTADO EN EL FOOTER
// ==========================================================
add_action('admin_notices', 'tureserva_inject_season_form'); // Usamos admin_notices para que salga arriba de la tabla
function tureserva_inject_season_form() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'temporada') return;
    ?>
    <div id="tureserva-add-season-panel">
        <h2>
            Nueva Temporada
            <button type="button" id="tureserva-cancel-season" class="button-link" style="text-decoration:none;">&times;</button>
        </h2>
        
        <div class="tureserva-form-grid">
            <div class="tureserva-field tureserva-form-full">
                <label>Título de la temporada</label>
                <input type="text" id="season_title" placeholder="Ej: Verano 2025">
            </div>

            <div class="tureserva-field">
                <label>Fecha de Inicio</label>
                <input type="date" id="season_start">
            </div>

            <div class="tureserva-field">
                <label>Fecha Final</label>
                <input type="date" id="season_end">
            </div>

            <div class="tureserva-field tureserva-form-full">
                <label>Días aplicados</label>
                <div class="tureserva-days-grid">
                    <?php 
                    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach($dias as $dia) {
                        echo '<label><input type="checkbox" name="season_days[]" value="'.$dia.'" checked> '.$dia.'</label>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="tureserva-panel-actions">
            <button type="button" id="tureserva-cancel-season" class="button">Cancelar</button>
            <button type="button" id="tureserva-save-season" class="button button-primary">Guardar Temporada</button>
        </div>
    </div>
    <?php
}

// ==========================================================
// 3. ⚡ AJAX HANDLER
// ==========================================================
add_action('wp_ajax_tureserva_save_season_ajax', 'tureserva_save_season_ajax');
function tureserva_save_season_ajax() {
    check_ajax_referer('tureserva_season_nonce', 'nonce');

    if (!current_user_can('edit_posts')) wp_send_json_error('Permisos insuficientes');

    $title = sanitize_text_field($_POST['title']);
    $start = sanitize_text_field($_POST['start']);
    $end = sanitize_text_field($_POST['end']);
    $days = isset($_POST['days']) ? array_map('sanitize_text_field', $_POST['days']) : [];

    $post_id = wp_insert_post([
        'post_title'  => $title,
        'post_type'   => 'temporada',
        'post_status' => 'publish'
    ]);

    if (is_wp_error($post_id)) wp_send_json_error($post_id->get_error_message());

    update_post_meta($post_id, '_tureserva_fecha_inicio', $start);
    update_post_meta($post_id, '_tureserva_fecha_fin', $end);
    update_post_meta($post_id, '_tureserva_dias_aplicados', $days);

    wp_send_json_success();
}
