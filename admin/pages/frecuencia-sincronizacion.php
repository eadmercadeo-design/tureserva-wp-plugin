<?php
/**
 * Admin Page: Frecuencia de Sincronización
 * 
 * Permite configurar los intervalos de cron y ver el estado de la sincronización.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Verificar permisos
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'No tienes permisos suficientes para acceder a esta página.', 'tureserva' ) );
}

// Procesar guardado (si se envió el formulario)
if ( isset( $_POST['tureserva_sync_save_settings'] ) && check_admin_referer( 'tureserva_sync_settings_action', 'tureserva_sync_nonce' ) ) {
    $new_freq = sanitize_text_field( $_POST['sync_frequency'] );
    
    $scheduler = new TuReserva_Sync_Scheduler();
    $scheduler->set_frequency( $new_freq );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Configuración guardada.', 'tureserva' ) . '</p></div>';
}

// Obtener datos actuales
$scheduler = new TuReserva_Sync_Scheduler();
$current_freq = $scheduler->get_frequency();
$next_run = $scheduler->get_next_run();
$last_run = get_option( 'tureserva_ical_last_cron_run', 'Nunca' );

// Opciones de frecuencia
$frequencies = array(
    '15min'  => 'Cada 15 minutos',
    '30min'  => 'Cada 30 minutos (Recomendado)',
    'hourly' => 'Cada 1 hora',
    '6hours' => 'Cada 6 horas',
    'manual' => 'Solo manual (Desactivar automático)'
);

?>

<div class="wrap tureserva-admin-wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Frecuencia de Sincronización (iCal)', 'tureserva' ); ?></h1>
    <hr class="wp-header-end">

    <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
        <form method="post" action="">
            <?php wp_nonce_field( 'tureserva_sync_settings_action', 'tureserva_sync_nonce' ); ?>
            
            <table class="form-table">
                <!-- Frecuencia -->
                <tr>
                    <th scope="row"><label for="sync_frequency"><?php _e( 'Frecuencia Automática', 'tureserva' ); ?></label></th>
                    <td>
                        <select name="sync_frequency" id="sync_frequency">
                            <?php foreach ( $frequencies as $val => $label ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_freq, $val ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e( 'Define con qué frecuencia el sistema buscará actualizaciones en los calendarios externos.', 'tureserva' ); ?>
                        </p>
                    </td>
                </tr>

                <!-- Estado -->
                <tr>
                    <th scope="row"><?php _e( 'Estado del Cron', 'tureserva' ); ?></th>
                    <td>
                        <?php if ( $next_run ) : ?>
                            <span class="dashicons dashicons-clock" style="color: #46b450; vertical-align: text-bottom;"></span> 
                            <strong><?php _e( 'Activo', 'tureserva' ); ?></strong>
                            <br>
                            <span class="description">
                                <?php printf( __( 'Próxima ejecución: %s', 'tureserva' ), date_i18n( 'd F Y H:i:s', $next_run ) ); ?>
                                <?php 
                                    $time_diff = $next_run - time();
                                    if ($time_diff > 0) {
                                        echo ' (' . human_time_diff( time(), $next_run ) . ')';
                                    }
                                ?>
                            </span>
                        <?php else : ?>
                            <span class="dashicons dashicons-warning" style="color: #f0b849; vertical-align: text-bottom;"></span>
                            <strong><?php _e( 'Inactivo (Manual)', 'tureserva' ); ?></strong>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Última Ejecución -->
                <tr>
                    <th scope="row"><?php _e( 'Última Ejecución', 'tureserva' ); ?></th>
                    <td>
                        <code><?php echo esc_html( $last_run ); ?></code>
                    </td>
                </tr>
                
                <?php 
                // Verificar último error crítico (el log más reciente con error)
                $last_error_log = '';
                if ( class_exists('TuReserva_Sync_Logger') ) {
                   // Buscamos manualmente en los logs recientes si hay un error
                   // Esto es una implementación visual rápida.
                   $recent_logs = $scheduler->get_frequency() !== 'manual' ? (new TuReserva_Sync_Logger())->get_global_recent_logs(20) : [];
                   foreach($recent_logs as $l) {
                       if ($l['result'] === 'error') {
                           $last_error_log = $l;
                           break;
                       }
                   }
                }
                
                if ( ! empty($last_error_log) ) : ?>
                <tr style="background: #fdf2f2;">
                    <th scope="row" style="color: #d63638;"><?php _e( 'Último Error Crítico', 'tureserva' ); ?></th>
                    <td>
                        <strong style="color: #d63638;"><?php echo esc_html( $last_error_log['message'] ); ?></strong>
                        <p class="description">
                            <?php printf( __('Ocurrido en: %s (%s)', 'tureserva'), esc_html($last_error_log['room_title']), esc_html($last_error_log['created_at']) ); ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <p class="submit">
                <input type="submit" name="tureserva_sync_save_settings" id="submit" class="button button-primary" value="<?php _e( 'Guardar Cambios', 'tureserva' ); ?>">
                
                <button type="button" id="btn-manual-sync" class="button button-secondary" style="margin-left: 10px;">
                    <span class="dashicons dashicons-update" style="vertical-align: text-bottom;"></span>    
                    <?php _e( 'Sincronizar Ahora', 'tureserva' ); ?>
                </button>
            </p>
        </form>
    </div>

    <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
        <h2><?php _e( 'Logs Recientes', 'tureserva' ); ?></h2>
        
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php _e('Fecha', 'tureserva'); ?></th>
                    <th><?php _e('Alojamiento', 'tureserva'); ?></th>
                    <th><?php _e('Canal', 'tureserva'); ?></th>
                    <th><?php _e('Resultado', 'tureserva'); ?></th>
                    <th><?php _e('Mensaje', 'tureserva'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ( class_exists('TuReserva_Sync_Logger') ) {
                    $logger = new TuReserva_Sync_Logger();
                    $logs = $logger->get_global_recent_logs( 10 );
                    
                    if ( empty($logs) ) {
                        echo '<tr><td colspan="5">' . __('No hay logs recientes.', 'tureserva') . '</td></tr>';
                    } else {
                        foreach ( $logs as $log ) {
                            $res_color = ($log['result'] === 'success') ? '#46b450' : '#d63638';
                            echo '<tr>';
                            echo '<td>' . esc_html($log['created_at']) . '</td>';
                            echo '<td>' . esc_html($log['room_title'] ?? $log['room_id']) . '</td>';
                            echo '<td title="'.esc_attr($log['channel']).'">' . esc_html( parse_url($log['channel'], PHP_URL_HOST) ?: 'Url' ) . '</td>';
                            echo '<td style="color:'. $res_color .'; font-weight:bold;">' . ucfirst($log['result']) . '</td>';
                            echo '<td>' . esc_html($log['message']) . ' (' . round($log['duration'], 2) . 's)</td>';
                            echo '</tr>';
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#btn-manual-sync').on('click', function() {
        var btn = $(this);
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> <?php _e( "En progreso...", "tureserva" ); ?>');
        
        $.post(ajaxurl, {
            action: 'tureserva_manual_global_sync',
            nonce: '<?php echo wp_create_nonce("tureserva_sync_settings_action"); ?>'
        }, function(response) {
            if (response.success) {
                // Éxito
                 btn.html('<span class="dashicons dashicons-yes"></span> <?php _e( "Iniciado", "tureserva" ); ?>');
                 location.reload(); 
            } else {
                // Error
                btn.prop('disabled', false).html(originalText);
                alert('Error: ' + response.data.message);
            }
        }).fail(function() {
            btn.prop('disabled', false).html(originalText);
            alert('Error de conexión.');
        });
    });
});
</script>
