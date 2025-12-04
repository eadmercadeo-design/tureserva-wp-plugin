<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: API Tokens — TuReserva
 * ==========================================================
 * Permite al administrador:
 *  - Crear nuevos tokens API
 *  - Ver tokens existentes
 *  - Revocar tokens inactivos
 * Interfaz AJAX conectada con core-auth.php
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "API Tokens"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_tokens' );
function tureserva_menu_tokens() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reserva',
        'API Tokens',
        'API Tokens',
        'manage_options',
        'tureserva_tokens',
        'tureserva_vista_tokens'
    );
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS
// =======================================================
add_action( 'admin_enqueue_scripts', 'tureserva_tokens_assets' );
function tureserva_tokens_assets( $hook ) {
    if ( strpos( $hook, 'tureserva_tokens' ) === false ) return;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'tureserva-tokens-js', TURESERVA_URL . 'assets/js/tureserva-tokens.js', array( 'jquery' ), TURESERVA_VERSION, true );
    wp_localize_script( 'tureserva-tokens-js', 'tureservaTokens', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tureserva_tokens_nonce' ),
    ));

    wp_enqueue_style( 'tureserva-tokens-css', TURESERVA_URL . 'assets/css/tureserva-tokens.css', array(), TURESERVA_VERSION );
}

// =======================================================
// 🧩 INTERFAZ DE ADMINISTRACIÓN DE TOKENS
// =======================================================
function tureserva_vista_tokens() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $tokens = get_option( TURE_SERVA_AUTH_OPTION, array() );
    ?>
    <div class="wrap">
        <h1>🔑 API Tokens — TuReserva</h1>
        <p>Administra las claves de acceso para integraciones externas (Netlify, Supabase, apps móviles, etc.).</p>

        <div class="tureserva-tokens-actions">
            <input type="text" id="tureserva-nombre-token" placeholder="Nombre del token (ej. Netlify Frontend)" class="regular-text">
            <button type="button" id="tureserva-crear-token" class="button button-primary">➕ Generar nuevo token</button>
        </div>

        <table class="widefat fixed striped" style="margin-top:20px;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Token</th>
                    <th>Creado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tureserva-tabla-tokens">
                <?php if ( empty( $tokens ) ) : ?>
                    <tr><td colspan="5" style="text-align:center;">No hay tokens registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ( $tokens as $id => $t ) : ?>
                        <tr data-key="<?php echo esc_attr( $t['key'] ); ?>">
                            <td><?php echo esc_html( $t['nombre'] ); ?></td>
                            <td><code class="token-code"><?php echo esc_html( substr( $t['key'], 0, 10 ) . '...' ); ?></code> <button class="button button-small copiar-token" data-token="<?php echo esc_attr( $t['key'] ); ?>">Copiar</button></td>
                            <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $t['creado'] ) ) ); ?></td>
                            <td>
                                <?php if ( ! empty( $t['activo'] ) ) : ?>
                                    <span class="status activo">Activo</span>
                                <?php else : ?>
                                    <span class="status inactivo">Revocado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ( ! empty( $t['activo'] ) ) : ?>
                                    <button class="button button-secondary revocar-token" data-token="<?php echo esc_attr( $t['key'] ); ?>">Revocar</button>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="tureserva-msg-token" style="margin-top:15px;display:none;"></div>
    </div>
    <?php
}
