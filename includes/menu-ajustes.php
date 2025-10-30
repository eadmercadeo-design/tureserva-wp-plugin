<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: Ajustes Globales — TuReserva
 * ==========================================================
 * Permite configurar:
 *  - Moneda y símbolo
 *  - Impuesto general
 *  - Formato de fecha
 *  - Idioma del sistema
 * Los datos se guardan vía AJAX al endpoint de core-settings.php
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "Ajustes"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_ajustes' );
function tureserva_menu_ajustes() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        'Ajustes del Sistema',
        'Ajustes',
        'manage_options',
        'tureserva_ajustes',
        'tureserva_vista_ajustes'
    );
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS
// =======================================================
add_action( 'admin_enqueue_scripts', 'tureserva_ajustes_assets' );
function tureserva_ajustes_assets( $hook ) {
    if ( strpos( $hook, 'tureserva_ajustes' ) === false ) return;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'tureserva-ajustes-js', TURESERVA_URL . 'assets/js/tureserva-ajustes.js', array( 'jquery' ), TURESERVA_VERSION, true );
    wp_localize_script( 'tureserva-ajustes-js', 'tureservaAjustes', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tureserva_settings_nonce' ),
    ));

    wp_enqueue_style( 'tureserva-ajustes-css', TURESERVA_URL . 'assets/css/tureserva-ajustes.css', array(), TURESERVA_VERSION );
}

// =======================================================
// ⚙️ INTERFAZ DEL PANEL DE AJUSTES
// =======================================================
function tureserva_vista_ajustes() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Obtener valores actuales
    $moneda   = get_option( 'tureserva_moneda', 'USD' );
    $simbolo  = get_option( 'tureserva_simbolo_moneda', '$' );
    $impuesto = get_option( 'tureserva_impuesto', 0.07 );
    $fecha    = get_option( 'tureserva_formato_fecha', 'd/m/Y' );
    $idioma   = get_option( 'tureserva_idioma', 'es_ES' );
    ?>

    <div class="wrap">
        <h1>⚙️ Ajustes Globales — TuReserva</h1>
        <p>Configura los parámetros generales del sistema. Estos valores afectan los cálculos de precios, reportes y correos automáticos.</p>

        <form id="tureserva-form-ajustes" method="post">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="tureserva_moneda">Moneda base</label></th>
                    <td>
                        <select name="tureserva_moneda" id="tureserva_moneda">
                            <?php
                            $opciones = array( 'USD' => 'USD - Dólar', 'EUR' => 'EUR - Euro', 'PAB' => 'PAB - Balboa', 'COP' => 'COP - Peso Colombiano' );
                            foreach ( $opciones as $k => $v ) {
                                printf( '<option value="%s" %s>%s</option>', esc_attr($k), selected($moneda, $k, false), esc_html($v) );
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_simbolo_moneda">Símbolo de moneda</label></th>
                    <td><input type="text" name="tureserva_simbolo_moneda" id="tureserva_simbolo_moneda" value="<?php echo esc_attr( $simbolo ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_impuesto">Impuesto (%)</label></th>
                    <td><input type="number" step="0.01" name="tureserva_impuesto" id="tureserva_impuesto" value="<?php echo esc_attr( $impuesto ); ?>" class="small-text"> <span>%</span></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_formato_fecha">Formato de fecha</label></th>
                    <td>
                        <select name="tureserva_formato_fecha" id="tureserva_formato_fecha">
                            <option value="d/m/Y" <?php selected( $fecha, 'd/m/Y' ); ?>>d/m/Y (31/12/2025)</option>
                            <option value="Y-m-d" <?php selected( $fecha, 'Y-m-d' ); ?>>Y-m-d (2025-12-31)</option>
                            <option value="m/d/Y" <?php selected( $fecha, 'm/d/Y' ); ?>>m/d/Y (12/31/2025)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tureserva_idioma">Idioma del sistema</label></th>
                    <td>
                        <select name="tureserva_idioma" id="tureserva_idioma">
                            <option value="es_ES" <?php selected( $idioma, 'es_ES' ); ?>>Español</option>
                            <option value="en_US" <?php selected( $idioma, 'en_US' ); ?>>Inglés</option>
                            <option value="pt_BR" <?php selected( $idioma, 'pt_BR' ); ?>>Portugués</option>
                            <option value="fr_FR" <?php selected( $idioma, 'fr_FR' ); ?>>Francés</option>
                        </select>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="button" id="tureserva-guardar-ajustes" class="button button-primary button-large">💾 Guardar ajustes</button>
            </p>
        </form>

        <div id="tureserva-resultado-ajax" style="display:none;margin-top:15px;"></div>
    </div>
    <?php
}
