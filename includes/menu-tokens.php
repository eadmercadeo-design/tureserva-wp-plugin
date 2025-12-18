<?php
/**
 * ==========================================================
 * PANEL ADMINISTRATIVO: API Tokens — TuReserva
 * ==========================================================
 * Permite al administrador:
 *  - Crear nuevos tokens API con scopes
 *  - Ver tokens existentes
 *  - Revocar tokens
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ "API Tokens"
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_tokens', 30 );
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

    $manager = new TuReserva_API_Token_Manager();
    $tokens = $manager->get_tokens();
    ?>
    <div class="wrap tureserva-tokens-wrap">
        <h1 class="wp-heading-inline">🔑 API Tokens — TuReserva</h1>
        <button id="btn-open-create-token" class="page-title-action">Generar nuevo token</button>
        <hr class="wp-header-end">

        <p>Administra las claves de acceso para integraciones externas. Los tokens solo se muestran una vez al crearlos.</p>

        <table class="widefat fixed striped" style="margin-top:20px;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Token (Prefijo)</th>
                    <th>Permisos (Scopes)</th>
                    <th>Estado</th>
                    <th>Último Uso</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tureserva-tabla-tokens">
                <?php if ( empty( $tokens ) ) : ?>
                    <tr class="no-items"><td colspan="7" style="text-align:center;">No hay tokens registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ( $tokens as $t ) : 
                        $scopes = json_decode( $t->scopes, true );
                        $scopes_str = is_array( $scopes ) ? implode( ', ', $scopes ) : '—';
                    ?>
                        <tr data-id="<?php echo esc_attr( $t->id ); ?>">
                            <td><strong><?php echo esc_html( $t->name ); ?></strong></td>
                            <td><code><?php echo esc_html( $t->token_prefix ); ?>...</code></td>
                            <td><span class="scopes-badge"><?php echo esc_html( $scopes_str ); ?></span></td>
                            <td>
                                <?php if ( $t->status === 'active' ) : ?>
                                    <span class="status-badge status-active">Activo</span>
                                <?php elseif ( $t->status === 'revoked' ) : ?>
                                    <span class="status-badge status-revoked">Revocado</span>
                                <?php else: ?>
                                    <span class="status-badge status-expired">Expirado</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $t->last_used_at ? esc_html( $t->last_used_at ) : '—'; ?></td>
                            <td><?php echo esc_html( $t->created_at ); ?></td>
                            <td>
                                <?php if ( $t->status === 'active' ) : ?>
                                    <button class="button button-small button-link-delete revocar-token" data-id="<?php echo esc_attr( $t->id ); ?>">Revocar</button>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL CREAR TOKEN -->
    <div id="modal-create-token" class="tureserva-modal" style="display:none;">
        <div class="tureserva-modal-content">
            <span class="close-modal">&times;</span>
            <h2>Generar Nuevo API Token</h2>
            
            <div class="form-group">
                <label>Nombre del Token</label>
                <input type="text" id="new-token-name" class="regular-text" placeholder="Ej. Integración App Móvil">
            </div>

            <div class="form-group">
                <label>Permisos (Scopes)</label>
                <div class="scopes-list">
                    <label><input type="checkbox" name="scopes[]" value="read:reservas" checked> Leer Reservas (read:reservas)</label>
                    <label><input type="checkbox" name="scopes[]" value="write:reservas" checked> Crear/Modificar Reservas (write:reservas)</label>
                    <label><input type="checkbox" name="scopes[]" value="read:alojamientos" checked> Leer Alojamientos (read:alojamientos)</label>
                    <label><input type="checkbox" name="scopes[]" value="admin:*"> Admin Full Access (admin:*)</label>
                </div>
            </div>

            <button id="btn-generate-token" class="button button-primary button-large">Generar Token</button>
            
            <div id="token-result-container" style="display:none; margin-top:20px; background:#f0f0f1; padding:15px; border-left:4px solid #2271b1;">
                <p><strong>⚠️ Copia este token ahora. No volverás a verlo.</strong></p>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="generated-token-value" readonly class="large-text code" style="width:100%;">
                    <button id="btn-copy-token" class="button">Copiar</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// =======================================================
// 📡 AJAX HANDLERS
// =======================================================
add_action( 'wp_ajax_tureserva_create_token', 'tureserva_ajax_create_token' );
function tureserva_ajax_create_token() {
    check_ajax_referer( 'tureserva_tokens_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Sin permisos' );

    $name = sanitize_text_field( $_POST['name'] );
    $scopes = isset( $_POST['scopes'] ) ? array_map( 'sanitize_text_field', $_POST['scopes'] ) : [];

    if ( empty( $name ) ) wp_send_json_error( 'El nombre es obligatorio' );

    $manager = new TuReserva_API_Token_Manager();
    $token = $manager->create_token( $name, $scopes );

    if ( is_wp_error( $token ) ) {
        wp_send_json_error( $token->get_error_message() );
    }

    // Return the plain token just this once
    wp_send_json_success( array(
        'token' => $token,
        'name' => $name,
        'prefix' => substr( $token, 0, 6 ),
        'scopes' => implode( ', ', $scopes ),
        'created_at' => current_time( 'mysql' )
    ));
}

add_action( 'wp_ajax_tureserva_revoke_token', 'tureserva_ajax_revoke_token' );
function tureserva_ajax_revoke_token() {
    check_ajax_referer( 'tureserva_tokens_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Sin permisos' );

    $id = intval( $_POST['id'] );
    $manager = new TuReserva_API_Token_Manager();
    
    if ( $manager->revoke_token( $id ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error( 'No se pudo revocar el token' );
    }
}
