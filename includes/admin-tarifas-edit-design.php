<?php
/**
 * Admin Design: Editar Tarifa (Post Edit Screen)
 * - Estilo MotoPress para los bloques de precios
 * - Cards, Sombras, Botones modernos
 * - Lógica JS para filas dinámicas
 */

if (!defined('ABSPATH')) exit;

add_action('admin_head', 'tureserva_tarifas_edit_assets');
function tureserva_tarifas_edit_assets() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'tureserva_tarifa') return;
    ?>
    <style>
        /* Contenedor Principal */
        #tureserva_tarifas_metabox .inside {
            padding: 0;
            margin: 0;
            background: #f0f0f1;
        }

        .tureserva-metabox-wrapper {
            padding: 20px;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* --------------------------------------------------
           SECCIÓN GLOBAL (Alojamiento)
           -------------------------------------------------- */
        .tureserva-global-settings {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .tureserva-global-settings label {
            font-weight: 600;
            color: #1d2327;
            font-size: 14px;
        }

        .tureserva-global-settings select {
            min-width: 300px;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #8c8f94;
        }

        /* --------------------------------------------------
           BLOQUES DE TEMPORADA (CARDS)
           -------------------------------------------------- */
        .tureserva-season-block {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .tureserva-season-block:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Header del Bloque */
        .tureserva-season-header {
            background: #f6f7f7;
            padding: 15px 20px;
            border-bottom: 1px solid #eaecf0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tureserva-season-title {
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tureserva-season-actions {
            display: flex;
            gap: 10px;
        }

        .btn-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            color: #646970;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s, background 0.2s;
        }
        .btn-icon:hover {
            color: #d63638;
            background: #f0f0f1;
        }

        /* Contenido del Bloque */
        .tureserva-season-content {
            padding: 20px;
        }

        /* Grid de Campos Principales */
        .tureserva-main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f1;
        }

        .ts-form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #646970;
            margin-bottom: 6px;
        }

        .ts-form-group input, .ts-form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3338;
        }

        /* --------------------------------------------------
           PRECIOS VARIABLES (ACORDEÓN)
           -------------------------------------------------- */
        .tureserva-variable-prices {
            background: #fcfcfc;
            border: 1px solid #f0f0f1;
            border-radius: 6px;
            padding: 15px;
        }

        .tureserva-variable-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .tureserva-variable-header h4 {
            margin: 0;
            font-size: 14px;
            color: #2271b1;
            font-weight: 600;
        }

        .tureserva-variable-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 40px;
            gap: 15px;
            align-items: end;
            margin-bottom: 10px;
            padding: 10px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .tureserva-variable-row:last-child { margin-bottom: 0; }

        .btn-add-variable {
            background: #fff !important;
            color: #2271b1 !important;
            border: 1px solid #2271b1 !important;
            padding: 6px 12px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-add-variable:hover {
            background: #f0f6fc !important;
        }

        /* Botón Principal "Añadir Temporada" */
        .tureserva-add-season-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #f6f7f7;
            border: 2px dashed #c3c4c7;
            border-radius: 8px;
            color: #50575e;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tureserva-add-season-btn:hover {
            border-color: #2271b1;
            color: #2271b1;
            background: #f0f6fc;
        }

    </style>
    <script>
    jQuery(document).ready(function($) {
        
        // 1. Añadir nueva fila de precio variable
        $(document).on('click', '.btn-add-variable', function(e) {
            e.preventDefault();
            const container = $(this).closest('.tureserva-variable-prices').find('.tureserva-variable-rows');
            const index = $(this).data('index'); // Índice del bloque padre
            const rowCount = container.find('.tureserva-variable-row').length;
            
            const newRow = `
                <div class="tureserva-variable-row">
                    <div class="ts-form-group">
                        <label>Desde (Noches)</label>
                        <input type="number" name="tureserva_precios[${index}][variables][${rowCount}][min]" placeholder="Ej: 3">
                    </div>
                    <div class="ts-form-group">
                        <label>Hasta (Noches)</label>
                        <input type="number" name="tureserva_precios[${index}][variables][${rowCount}][max]" placeholder="Ej: 7">
                    </div>
                    <div class="ts-form-group">
                        <label>Precio por noche</label>
                        <input type="number" name="tureserva_precios[${index}][variables][${rowCount}][price]" placeholder="0.00">
                    </div>
                    <button type="button" class="btn-icon remove-variable" title="Eliminar">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            `;
            
            container.append(newRow);
        });

        // 2. Eliminar fila variable
        $(document).on('click', '.remove-variable', function(e) {
            e.preventDefault();
            if(confirm('¿Eliminar esta fila de precio?')) {
                $(this).closest('.tureserva-variable-row').remove();
            }
        });

        // 3. Eliminar bloque de temporada
        $(document).on('click', '.remove-season-block', function(e) {
            e.preventDefault();
            if(confirm('¿Eliminar este bloque de temporada completo?')) {
                $(this).closest('.tureserva-season-block').remove();
            }
        });

        // 4. Añadir nuevo bloque de temporada (Clonación simple por ahora)
        // Nota: En una implementación real idealmente usaríamos un template oculto.
        $('#add-season-block-btn').on('click', function() {
            // Recargar la página o hacer submit para guardar y añadir uno nuevo es lo más seguro en WP simple.
            // Para UX "MotoPress", clonaremos el último bloque y limpiaremos valores.
            
            const lastBlock = $('.tureserva-season-block').last();
            if(lastBlock.length > 0) {
                const newBlock = lastBlock.clone();
                const newIndex = $('.tureserva-season-block').length;
                
                // Actualizar índices en names (básico regex replace)
                newBlock.html(function(i, html) {
                    return html.replace(/\[\d+\]/g, '[' + newIndex + ']');
                });
                
                newBlock.find('input').val('');
                newBlock.find('.tureserva-variable-rows').empty(); // Limpiar variables
                newBlock.attr('data-index', newIndex);
                newBlock.find('.btn-add-variable').attr('data-index', newIndex);
                
                lastBlock.after(newBlock);
            } else {
                location.reload(); // Fallback si no hay bloques
            }
        });

    });
    </script>
    <?php
}
