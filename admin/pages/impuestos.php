<?php
/**
 * ==========================================================
 * PÁGINA: Impuestos y Cargos — TuReserva
 * ==========================================================
 * Muestra 4 secciones (Cargos, Impuestos Alojamiento, 
 * Impuestos Servicios, Impuestos Gratis) en formato de tarjetas.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_impuestos_page_render() {
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">💰 Impuestos y Cargos</h1>
        <p class="description">Gestiona tasas, impuestos y cargos adicionales para tus reservas.</p>
        <hr class="wp-header-end">

        <style>
            .ts-taxes-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .ts-tax-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                padding: 0;
                overflow: hidden;
            }
            .ts-tax-card-header {
                padding: 15px 20px;
                border-bottom: 1px solid #f0f0f1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #fcfcfc;
            }
            .ts-tax-title {
                font-size: 15px;
                font-weight: 600;
                color: #1d2327;
                margin: 0;
            }
            .ts-tax-content {
                padding: 0;
            }
            .ts-tax-table {
                width: 100%;
                border-collapse: collapse;
            }
            .ts-tax-table th {
                text-align: left;
                padding: 12px 20px;
                background: #fff;
                border-bottom: 1px solid #f0f0f1;
                font-weight: 600;
                font-size: 12px;
                color: #646970;
                text-transform: uppercase;
            }
            .ts-tax-table td {
                padding: 12px 20px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 13px;
                color: #3c434a;
            }
            .ts-tax-table tr:last-child td { border-bottom: none; }
            .ts-tax-table tr:hover { background: #f9f9f9; }
            .ts-empty-msg {
                padding: 30px;
                text-align: center;
                color: #646970;
                font-style: italic;
            }
            .ts-badge-tax {
                background: #f0f0f1;
                color: #1d2327;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 500;
            }
        </style>

        <div class="ts-taxes-grid">

            <?php
            // Definición de las 4 secciones
            $sections = [
                'charge' => [
                    'title' => 'Cargos Adicionales',
                    'btn'   => 'Añadir Cargo',
                    'desc'  => 'Limpieza, seguros, etc.'
                ],
                'tax_accommodation' => [
                    'title' => 'Tarifas de Alojamiento',
                    'btn'   => 'Añadir Impuesto',
                    'desc'  => 'IVA, tasas turísticas, etc.'
                ],
                'tax_service' => [
                    'title' => 'Impuestos de Servicios',
                    'btn'   => 'Añadir Impuesto Servicio',
                    'desc'  => 'Aplicable a servicios extra.'
                ],
                'tax_free' => [
                    'title' => 'Impuestos Gratis (Exentos)',
                    'btn'   => 'Añadir Exento',
                    'desc'  => 'Tasas del 0%.'
                ]
            ];

            foreach ($sections as $type => $info) {
                // Consultar items
                $items = get_posts([
                    'post_type'   => 'tureserva_impuesto',
                    'numberposts' => -1,
                    'meta_key'    => '_tureserva_tax_type',
                    'meta_value'  => $type
                ]);
                ?>
                <div class="ts-tax-card">
                    <div class="ts-tax-card-header">
                        <div>
                            <h2 class="ts-tax-title"><?php echo esc_html($info['title']); ?></h2>
                            <span style="font-size:12px; color:#666;"><?php echo esc_html($info['desc']); ?></span>
                        </div>
                        <a href="<?php echo admin_url('post-new.php?post_type=tureserva_impuesto&tax_type=' . $type); ?>" class="button button-secondary">
                            <?php echo esc_html($info['btn']); ?>
                        </a>
                    </div>

                    <div class="ts-tax-content">
                        <?php if (empty($items)): ?>
                            <div class="ts-empty-msg">No se han creado todavía.</div>
                        <?php else: ?>
                            <table class="ts-tax-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th style="text-align:right;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): 
                                        $amount = get_post_meta($item->ID, '_tureserva_amount', true);
                                        $calc   = get_post_meta($item->ID, '_tureserva_calc_type', true);
                                        $val_txt = ($calc === 'percent') ? $amount . '%' : '$' . $amount;
                                        
                                        if ($type === 'tax_free') $val_txt = '0%';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($item->post_title); ?></strong>
                                        </td>
                                        <td>
                                            <span class="ts-badge-tax"><?php echo esc_html($val_txt); ?></span>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="<?php echo get_edit_post_link($item->ID); ?>" class="dashicons dashicons-edit" style="text-decoration:none; color:#2271b1;" title="Editar"></a>
                                            <a href="<?php echo get_delete_post_link($item->ID); ?>" class="dashicons dashicons-trash" style="text-decoration:none; color:#d63638; margin-left:8px;" title="Eliminar" onclick="return confirm('¿Estás seguro?');"></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
    <?php
}
