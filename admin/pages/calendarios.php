<?php
/**
 * ==========================================================
 * PÁGINA: Sincronización de Calendarios (iCal) — TuReserva
 * ==========================================================
 * Gestión de importación/exportación de calendarios iCal.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function trs_ical_admin_render() {
    // 🕵️ VISTA DE EDICIÓN
    if (isset($_GET['view']) && $_GET['view'] === 'edit' && !empty($_GET['id'])) {
        trs_ical_admin_render_edit(intval($_GET['id']));
        return;
    }
    ?>
    <style>
            .ts-sync-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                box-shadow: 0 1px 1px rgba(0,0,0,0.04);
                margin-top: 20px;
            }
            .ts-sync-table th {
                text-align: left;
                padding: 15px;
                background: #f0f0f1;
                border-bottom: 1px solid #c3c4c7;
                font-weight: 600;
                color: #1d2327;
            }
            .ts-sync-table td {
                padding: 15px;
                border-bottom: 1px solid #f0f0f1;
                vertical-align: middle;
            }
            .ts-sync-table tr:last-child td { border-bottom: none; }
            .ts-sync-table tr:hover { background: #fcfcfc; }
            
            .ts-ical-link-box {
                display: flex;
                align-items: center;
                gap: 5px;
                background: #f6f7f7;
                padding: 5px 10px;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                font-family: monospace;
                font-size: 11px;
                max-width: 300px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
            .ts-status-indicator {
                display: inline-block;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin-right: 5px;
            }
            .status-success { background: #46b450; }
            .status-warning { background: #f0b849; }
            .status-error { background: #d63638; }
            .status-pending { background: #dcdcde; }

            .ts-progress-bar {
                height: 4px;
                background: #f0f0f1;
                width: 100%;
                margin-top: 5px;
                border-radius: 2px;
                overflow: hidden;
                display: none;
            }
            .ts-progress-fill {
                height: 100%;
                background: #2271b1;
                width: 0%;
                transition: width 0.3s;
            }
        </style>

        <div style="margin-bottom: 15px; text-align: right;">
            <button class="button button-secondary" id="ts-sync-all">🔄 Sincronizar todos los calendarios</button>
        </div>

        <table class="ts-sync-table">
            <thead>
                <tr>
                    <th>Alojamiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener alojamientos
                $alojamientos = get_posts(array(
                    'post_type' => 'trs_alojamiento',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));

                if (empty($alojamientos)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:20px;">No hay alojamientos creados.</td></tr>
                <?php else: 
                    foreach ($alojamientos as $aloj):
                        // Datos dummy para demo
                        $export_url = home_url('/?feed=tureserva.ics&ical_id=' . $aloj->ID);
                        
                        // Obtener estado real de la DB
                        // Por ahora simulado o obtenido del repo
                        $sync_status = 'pending'; 
                        $last_sync = '';
                        
                        // Consultar DB para estado real
                        global $wpdb;
                        $repo = new TuReserva_Sync_Urls_Repository();
                        $row = $repo->get_sync_status( $aloj->ID );
                        
                        if ($row) {
                            $sync_status = $row->sync_status;
                            $last_sync = $row->last_sync;
                        }

                        switch ($sync_status) {
                            case 'success':
                                $status_class = 'status-success';
                                break;
                            case 'warning':
                                $status_class = 'status-warning';
                                break;
                            case 'error':
                                $status_class = 'status-error';
                                break;
                            default:
                                $status_class = 'status-pending';
                        }
                    ?>
                    <tr id="row-<?php echo $aloj->ID; ?>">
                        <td>
                            <strong><?php echo esc_html($aloj->post_title); ?></strong><br>
                            <span style="font-size:12px; color:#666;">ID: <?php echo $aloj->ID; ?></span>
                        </td>
                        <td>
                            <div class="ts-ical-link-box" title="<?php echo esc_attr($export_url); ?>">
                                <?php echo esc_html($export_url); ?>
                            </div>
                            <div style="margin-top:5px;">
                                <button class="button button-small ts-copy-btn" data-url="<?php echo esc_attr($export_url); ?>">Copiar</button>
                                <a href="<?php echo esc_url($export_url); ?>" class="button button-small">Descargar</a>
                            </div>
                        </td>
                        <td>
                            <?php 
                            // 1. Obtener URLs del repositorio
                            $repo = new TuReserva_Sync_Urls_Repository();
                            $imports = $repo->get_urls( $aloj->ID );
                            
                            if ( empty($imports) ): ?>
                                <span style="color:#999;">— Ninguno —</span>
                            <?php else: ?>
                                <?php foreach ($imports as $sync_id => $url): ?>
                                    <div style="margin-bottom:3px;">
                                        <span class="dashicons dashicons-calendar-alt" style="font-size:14px; width:14px; height:14px; vertical-align:middle;"></span>
                                        <?php echo esc_html( $url ); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <span class="ts-status-indicator <?php echo $status_class; ?>"></span>
                                <?php echo ucfirst($sync_status); ?>
                            </div>
                            <?php if ($last_sync): ?>
                                <div style="font-size:11px; color:#666; margin-top:3px;">
                                    <?php echo date_i18n('d/m/Y H:i', strtotime($last_sync)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="ts-progress-bar"><div class="ts-progress-fill"></div></div>
                        </td>
                        <td style="text-align:right;">
                            <button class="button button-secondary ts-sync-btn" data-id="<?php echo $aloj->ID; ?>">Sincronizar</button>
                            <a href="?post_type=tureserva_reserva&page=tureserva-calendarios&view=edit&id=<?php echo $aloj->ID; ?>" class="button button-primary">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <script>
            jQuery(document).ready(function($) {
                // Copiar al portapapeles
                $('.ts-copy-btn').on('click', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    navigator.clipboard.writeText(url).then(function() {
                        alert('Enlace copiado al portapapeles');
                    });
                });

                // Sincronización Manual
                $('.ts-sync-btn').on('click', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var id = btn.data('id');
                    var row = $('#row-' + id);
                    var bar = row.find('.ts-progress-bar');
                    var fill = row.find('.ts-progress-fill');

                    btn.prop('disabled', true).text('Sincronizando...');
                    bar.show();
                    fill.css('width', '50%');

                    $.post(ajaxurl, {
                        action: 'tureserva_manual_sync',
                        id: id,
                        nonce: '<?php echo wp_create_nonce('tureserva_sync_nonce'); ?>'
                    }, function(response) {
                        fill.css('width', '100%');
                        setTimeout(function() {
                            bar.hide();
                            fill.css('width', '0%');
                            btn.prop('disabled', false).text('Sincronizar');
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        }, 500);
                    });
                });
            });
        </script>
    </div>
    <?php
}

// =======================================================
// 📝 VISTA DE EDICIÓN
// =======================================================
function trs_ical_admin_render_edit($post_id) {
    $alojamiento = get_post($post_id);
    
    $repo = new TuReserva_Sync_Urls_Repository();

    // Guardar cambios
    if (isset($_POST['ts_save_calendars']) && check_admin_referer('ts_save_cals_' . $post_id)) {
        $new_urls = [];
        if (isset($_POST['ical_url']) && is_array($_POST['ical_url'])) {
            foreach ($_POST['ical_url'] as $key => $url) {
                if (!empty($url)) {
                    $new_urls[] = esc_url_raw($url);
                }
            }
        }
        
        // Actualizar usando repositorio
        $repo->update_urls($post_id, $new_urls);
        
        echo '<div class="notice notice-success is-dismissible"><p>Calendarios actualizados correctamente.</p></div>';
    }

    // Obtener URLs actuales
    $imports = $repo->get_urls($post_id);
    // Convertir a formato array simple para la vista si es necesario, 
    // pero get_urls devuelve [sync_id => url], así que iteramos eso.
    if (!is_array($imports)) $imports = [];
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">📅 Editar Calendarios: <?php echo esc_html($alojamiento->post_title); ?></h1>
        <a href="?post_type=tureserva_reserva&page=tureserva-calendarios" class="page-title-action">← Volver al listado</a>
        <hr class="wp-header-end">

        <div style="max-width: 800px; margin-top: 20px;">
            <div style="background:#fff; padding:20px; border:1px solid #c3c4c7; border-radius:4px;">
                <h3>Importar Calendarios (Airbnb, Booking, Vrbo)</h3>
                <p class="description">Pega aquí las URLs iCal (.ics) de las plataformas externas para importar sus bloqueos y reservas.</p>
                
                <form method="post">
                    <?php wp_nonce_field('ts_save_cals_' . $post_id); ?>
                    
                    <div id="ts-ical-list">
                        <?php foreach ($imports as $sync_id => $url): ?>
                            <div class="ts-ical-row" style="display:flex; gap:10px; margin-bottom:15px; align-items:flex-end;">
                                <div style="flex:2;">
                                    <label style="display:block; font-weight:600; margin-bottom:5px;">URL del Calendario</label>
                                    <input type="url" name="ical_url[]" value="<?php echo esc_attr($url); ?>" class="widefat" placeholder="https://...">
                                </div>
                                <div style="flex:1;">
                                    <label style="display:block; font-weight:600; margin-bottom:5px;">Nombre / Origen</label>
                                    <input type="text" name="ical_source[]" value="Calendario Externo" class="widefat" placeholder="Ej: Airbnb">
                                </div>
                                <div>
                                    <button type="button" class="button ts-remove-row">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <button type="button" class="button" id="ts-add-row">+ Agregar otro calendario</button>
                    </div>

                    <hr>
                    <button type="submit" name="ts_save_calendars" class="button button-primary button-large">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#ts-add-row').on('click', function() {
                    var row = `
                        <div class="ts-ical-row" style="display:flex; gap:10px; margin-bottom:15px; align-items:flex-end;">
                            <div style="flex:2;">
                                <label style="display:block; font-weight:600; margin-bottom:5px;">URL del Calendario</label>
                                <input type="url" name="ical_url[]" class="widefat" placeholder="https://...">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-weight:600; margin-bottom:5px;">Nombre / Origen</label>
                                <input type="text" name="ical_source[]" class="widefat" placeholder="Ej: Airbnb">
                            </div>
                            <div>
                                <button type="button" class="button ts-remove-row">Eliminar</button>
                            </div>
                        </div>
                    `;
                    $('#ts-ical-list').append(row);
                });

                $(document).on('click', '.ts-remove-row', function() {
                    $(this).closest('.ts-ical-row').remove();
                });

                // Auto-detect source
                $(document).on('input', 'input[name="ical_url[]"]', function() {
                    var url = $(this).val().toLowerCase();
                    var row = $(this).closest('.ts-ical-row');
                    var sourceInput = row.find('input[name="ical_source[]"]');
                    
                    if (sourceInput.val() === '') {
                        if (url.includes('airbnb')) sourceInput.val('Airbnb');
                        else if (url.includes('booking')) sourceInput.val('Booking.com');
                        else if (url.includes('vrbo') || url.includes('homeaway')) sourceInput.val('Vrbo');
                        else if (url.includes('tripadvisor')) sourceInput.val('TripAdvisor');
                    }
                });
            });
        </script>
    </div>
    <?php
}
