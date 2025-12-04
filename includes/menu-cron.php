<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Frecuencia de Sincronización — TuReserva
 * ==========================================================
 * Permite elegir intervalo de sincronización con Supabase.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_cron' );
function tureserva_menu_cron() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        'Frecuencia de Sincronización',
        'Frecuencia de Sincronización',
        'manage_options',
        'tureserva_cron',
        'tureserva_vista_cron'
    );
}

// =======================================================
// ⚙️ INTERFAZ DE AJUSTE DE CRON
// =======================================================
function tureserva_vista_cron() {
    $intervalo = get_option( 'tureserva_sync_interval', 'manual' );
    $ultimo = get_option( 'tureserva_ultima_sync', '—' );

    ?>
    <div class="wrap">
        <h1>⏱️ Frecuencia de Sincronización — TuReserva</h1>
        <p>Define cada cuánto tiempo el sistema enviará datos automáticamente a Supabase.  
        También puedes ejecutar una sincronización manual en cualquier momento.</p>

        <form id="tureserva-form-cron">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="tureserva_sync_interval">Intervalo</label></th>
                    <td>
                        <select id="tureserva_sync_interval" name="tureserva_sync_interval">
                            <option value="manual" <?php selected( $intervalo, 'manual' ); ?>>Manual (solo cuando lo indique)</option>
                            <option value="5min" <?php selected( $intervalo, '5min' ); ?>>Cada 5 minutos</option>
                            <option value="1h" <?php selected( $intervalo, '1h' ); ?>>Cada 1 hora</option>
                            <option value="3h" <?php selected( $intervalo, '3h' ); ?>>Cada 3 horas</option>
                            <option value="6h" <?php selected( $intervalo, '6h' ); ?>>Cada 6 horas</option>
                            <option value="12h" <?php selected( $intervalo, '12h' ); ?>>Cada 12 horas</option>
                            <option value="24h" <?php selected( $intervalo, '24h' ); ?>>Cada 24 horas</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Última sincronización</th>
                    <td><strong><?php echo esc_html( $ultimo ); ?></strong></td>
                </tr>
            </table>

            <p class="submit">
                <button type="button" id="tureserva-guardar-cron" class="button button-primary">💾 Guardar configuración</button>
                <button type="button" id="tureserva-ejecutar-cron" class="button button-secondary">☁️ Ejecutar ahora</button>
            </p>
        </form>

        <div id="tureserva-cron-resultado" style="display:none;margin-top:15px;"></div>
    </div>
    <?php
}

// =======================================================
// 🧩 AJAX: Guardar intervalo
// =======================================================
add_action( 'wp_ajax_tureserva_guardar_cron', 'tureserva_guardar_cron' );
function tureserva_guardar_cron() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'No autorizado' );

    $nuevo = sanitize_text_field( $_POST['intervalo'] ?? 'manual' );
    update_option( 'tureserva_sync_interval', $nuevo );
    tureserva_update_cron_schedule();

    wp_send_json_success( array( 'mensaje' => '✅ Frecuencia actualizada correctamente.' ) );
}
