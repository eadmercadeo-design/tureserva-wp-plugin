<?php
/**
 * Admin Design: Ajustes Generales
 * - Estilo WooCommerce / MotoPress
 * - Pestañas modernas
 * - Cards y Campos limpios
 */

if (!defined('ABSPATH')) exit;

add_action('admin_head', 'tureserva_ajustes_admin_assets');
function tureserva_ajustes_admin_assets() {
    $screen = get_current_screen();
    if (strpos($screen->id, 'tureserva-ajustes-generales') === false) return;
    ?>
    <style>
        /* Contenedor Principal */
        .tureserva-settings-wrap {
            max-width: 1000px;
            margin: 20px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        /* Header */
        .tureserva-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .tureserva-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1d2327;
            margin: 0;
        }

        /* Tabs Navegación */
        .tureserva-tabs-nav {
            display: flex;
            border-bottom: 1px solid #c3c4c7;
            margin-bottom: 20px;
            background: #fff;
            padding: 0 20px;
            border-radius: 4px 4px 0 0; /* Opcional si se quiere encajonar */
        }
        
        .tureserva-tab-link {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #50575e;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .tureserva-tab-link:hover {
            color: #2271b1;
        }
        
        .tureserva-tab-link.active {
            color: #2271b1;
            border-bottom-color: #2271b1;
            font-weight: 600;
        }

        /* Contenido de Tabs */
        .tureserva-tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .tureserva-tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cards / Secciones */
        .tureserva-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 6px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .tureserva-card h3 {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f1;
            font-size: 16px;
            color: #1d2327;
            font-weight: 600;
        }

        /* Grid de Campos */
        .tureserva-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .ts-form-group {
            margin-bottom: 20px;
        }
        .ts-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #3c434a;
            font-size: 13px;
        }
        
        .ts-form-group input[type="text"],
        .ts-form-group input[type="email"],
        .ts-form-group input[type="number"],
        .ts-form-group input[type="time"],
        .ts-form-group select,
        .ts-form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3338;
            transition: border-color 0.2s;
        }

        .ts-form-group input:focus,
        .ts-form-group select:focus,
        .ts-form-group textarea:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }

        .ts-helper {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #646970;
            font-style: italic;
        }

        /* Tooltip (?) */
        .ts-tooltip {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: #dcdcde;
            color: #50575e;
            border-radius: 50%;
            text-align: center;
            line-height: 16px;
            font-size: 11px;
            cursor: help;
            margin-left: 5px;
        }

        /* Toggles (Switch) */
        .ts-toggle {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
        }
        .ts-toggle input { opacity: 0; width: 0; height: 0; }
        .ts-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .ts-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .ts-slider { background-color: #2271b1; }
        input:checked + .ts-slider:before { transform: translateX(16px); }

        /* Botones */
        .ts-btn-primary {
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        .ts-btn-primary:hover { background: #135e96; }

        /* Licencia Status */
        .license-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .license-valid { background: #edfaef; color: #1a7f37; }
        .license-invalid { background: #fbeaea; color: #cf222e; }

    </style>
    <script>
    jQuery(document).ready(function($) {
        // Tab Switching Logic
        $('.tureserva-tab-link').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class
            $('.tureserva-tab-link').removeClass('active');
            $('.tureserva-tab-content').removeClass('active');
            
            // Add active class
            $(this).addClass('active');
            const target = $(this).attr('href');
            $(target).addClass('active');
            
            // Persist tab (optional, basic)
            // window.location.hash = target;
        });

        // Auto-open tab from hash
        if(window.location.hash) {
            const hash = window.location.hash;
            if($(hash).length) {
                $('.tureserva-tab-link[href="'+hash+'"]').click();
            }
        }
    });
    </script>
    <?php
}
