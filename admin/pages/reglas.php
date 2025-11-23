<?php
/**
 * ==========================================================
 * PÁGINA: Reglas de Reserva — TuReserva
 * ==========================================================
 * Muestra todas las reglas configuradas organizadas por secciones.
 * Estilo visual similar a MotoPress / WooCommerce Settings.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_reglas_page_render() {
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">📏 Reglas de Reserva</h1>
        <p class="description">Configura restricciones, estancias mínimas y bloqueos para tus alojamientos.</p>
        <hr class="wp-header-end">

        <style>
            .ts-rules-container { max-width: 1200px; margin-top: 20px; }
            .ts-rule-section {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                padding: 20px;
            }
            .ts-rule-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            .ts-rule-title {
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                margin: 0;
            }
            .ts-rule-desc {
                color: #646970;
                font-size: 13px;
                margin: 5px 0 0 0;
            }
            .ts-rule-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            .ts-rule-table th {
                text-align: left;
                padding: 10px;
                background: #f6f7f7;
                border-bottom: 1px solid #dcdcde;
                font-weight: 600;
                font-size: 13px;
            }
            .ts-rule-table td {
                padding: 10px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 13px;
                color: #3c434a;
            }
            .ts-rule-table tr:last-child td { border-bottom: none; }
            .ts-rule-table tr:hover { background: #fcfcfc; }
            .ts-empty-state {
                padding: 20px;
                text-align: center;
                background: #f9f9f9;
                color: #646970;
                border-radius: 4px;
                font-style: italic;
            }
            .ts-badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                background: #e5e5e5;
                font-size: 11px;
                margin-right: 4px;
            }
        </style>

        <div class="ts-rules-container">

            <?php
            // Definición de secciones
            $sections = [
                'arrival_days' => [
                    'title' => 'Días de llegada',
                    'desc'  => 'Restringe en qué días de la semana pueden comenzar las reservas.',
                    'btn'   => 'Agregar regla de llegada'
                ],
                'departure_days' => [
                    'title' => 'Días de salida',
                    'desc'  => 'Restringe en qué días de la semana pueden finalizar las reservas.',
                    'btn'   => 'Agregar regla de salida'
                ],
                'min_stay' => [
                    'title' => 'Estancia mínima',
                    'desc'  => 'Define el número mínimo de noches permitido.',
                    'btn'   => 'Agregar estancia mínima'
                ],
                'max_stay' => [
                    'title' => 'Estancia máxima',
                    'desc'  => 'Define el número máximo de noches permitido.',
                    'btn'   => 'Agregar estancia máxima'
                ],
                'block' => [
                    'title' => 'Bloquear alojamiento',
                    'desc'  => 'Cierra la disponibilidad de alojamientos por mantenimiento u otras razones.',
                    'btn'   => 'Bloquear alojamiento'
                ],
                'min_advance' => [
                    'title' => 'Antelación mínima',
                    'desc'  => '¿Con cuántos días de antelación se debe reservar como mínimo?',
                    'btn'   => 'Agregar antelación mínima'
                ],
                'max_advance' => [
                    'title' => 'Antelación máxima',
                    'desc'  => '¿Con cuánta antelación máxima se permite reservar?',
                    'btn'   => 'Agregar antelación máxima'
                ],
                'buffer' => [
                    'title' => 'Búfer de reservas',
                    'desc'  => 'Tiempo de bloqueo automático entre reservas (limpieza/preparación).',
                    'btn'   => 'Agregar búfer'
                ]
            ];

            foreach ($sections as $type => $info) {
                // Consultar reglas de este tipo
                $rules = get_posts([
                    'post_type'   => 'tureserva_regla',
                    'numberposts' => -1,
                    'meta_key'    => '_tureserva_rule_type',
                    'meta_value'  => $type
                ]);
                ?>
                <div class="ts-rule-section">
                    <div class="ts-rule-header">
                        <div>
                            <h2 class="ts-rule-title"><?php echo esc_html($info['title']); ?></h2>
                            <p class="ts-rule-desc"><?php echo esc_html($info['desc']); ?></p>
                        </div>
                        <a href="<?php echo admin_url('post-new.php?post_type=tureserva_regla&rule_type=' . $type); ?>" class="button button-primary">
                            <?php echo esc_html($info['btn']); ?>
                        </a>
                    </div>

                    <?php if (empty($rules)): ?>
                        <div class="ts-empty-state">No hay reglas configuradas.</div>
                    <?php else: ?>
                        <table class="ts-rule-table">
                            <thead>
                                <tr>
                                    <th>Regla / Valor</th>
                                    <th>Aplica a</th>
                                    <th style="width:100px; text-align:right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rules as $rule): 
                                    $alojamientos = get_post_meta($rule->ID, '_tureserva_alojamientos', true);
                                    $temporadas   = get_post_meta($rule->ID, '_tureserva_temporadas', true);
                                    
                                    // Generar resumen del valor
                                    $valor_txt = '—';
                                    if ($type == 'min_stay' || $type == 'max_stay') {
                                        $valor_txt = get_post_meta($rule->ID, '_tureserva_nights', true) . ' noches';
                                    } elseif ($type == 'arrival_days' || $type == 'departure_days') {
                                        $days = get_post_meta($rule->ID, '_tureserva_days', true) ?: [];
                                        $valor_txt = implode(', ', array_map('ucfirst', $days));
                                    } elseif ($type == 'block') {
                                        $from = get_post_meta($rule->ID, '_tureserva_date_from', true);
                                        $to = get_post_meta($rule->ID, '_tureserva_date_to', true);
                                        $valor_txt = "$from ➝ $to";
                                    } elseif ($type == 'min_advance' || $type == 'max_advance') {
                                        $valor_txt = get_post_meta($rule->ID, '_tureserva_days_advance', true) . ' días';
                                    } elseif ($type == 'buffer') {
                                        $valor_txt = get_post_meta($rule->ID, '_tureserva_buffer_days', true) . ' días';
                                    }

                                    // Generar resumen de aplicación
                                    $aplica_txt = '';
                                    if (empty($alojamientos)) {
                                        $aplica_txt .= '<span class="ts-badge">Todos los alojamientos</span>';
                                    } else {
                                        $aplica_txt .= '<span class="ts-badge">' . count($alojamientos) . ' alojamientos</span>';
                                    }
                                    if (!empty($temporadas)) {
                                        $aplica_txt .= ' <span class="ts-badge">Temporadas específicas</span>';
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($valor_txt); ?></strong></td>
                                    <td><?php echo $aplica_txt; ?></td>
                                    <td style="text-align:right;">
                                        <a href="<?php echo get_edit_post_link($rule->ID); ?>" class="dashicons dashicons-edit" style="text-decoration:none; color:#2271b1;" title="Editar"></a>
                                        <a href="<?php echo get_delete_post_link($rule->ID); ?>" class="dashicons dashicons-trash" style="text-decoration:none; color:#d63638; margin-left:8px;" title="Eliminar" onclick="return confirm('¿Estás seguro?');"></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php } ?>

        </div>
    </div>
    <?php
}
