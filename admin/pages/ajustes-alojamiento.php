<?php
/**
 * ==========================================================
 * ADMIN PAGE: Ajustes de Alojamiento — TuReserva
 * ==========================================================
 * Pestañas internas:
 * - General
 * - Emails del admin
 * - Emails del cliente
 * - Ajustes de email (plantillas globales)
 * - Pasarelas de pago
 * - Extensiones
 * - Avanzado
 * - Licencia
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 💾 GUARDAR OPCIONES
// =======================================================
if ( isset( $_POST['tureserva_save_settings'] ) && check_admin_referer( 'tureserva_save_settings_nonce' ) ) {
    foreach ( $_POST as $key => $value ) {
        if ( strpos( $key, 'tureserva_' ) === 0 ) {
            update_option( sanitize_text_field( $key ), sanitize_text_field( $value ) );
        }
    }
    echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados correctamente.', 'tureserva' ) . '</p></div>';
}

// =======================================================
// 🧭 DEFINIR PESTAÑAS DISPONIBLES
// =======================================================
$tabs = array(
    'general'          => __( 'General', 'tureserva' ),
    'emails-admin'     => __( 'Emails del admin', 'tureserva' ),
    'emails-cliente'   => __( 'Emails del cliente', 'tureserva' ),
    'ajustes-email'    => __( 'Ajustes de email', 'tureserva' ),
    'pagos'            => __( 'Pasarelas de pago', 'tureserva' ),
    'extensiones'      => __( 'Extensiones', 'tureserva' ),
    'avanzado'         => __( 'Avanzado', 'tureserva' ),
    'licencia'         => __( 'Licencia', 'tureserva' ),
);

// Pestaña activa actual
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

// =======================================================
// 🎨 ESTILOS BÁSICOS
// =======================================================
echo '<style>
    .tureserva-settings-tabs { margin-top: 20px; }
    .tureserva-settings-tabs a {
        display:inline-block; padding:8px 16px; text-decoration:none;
        border:1px solid #ccc; border-bottom:none; margin-right:4px; background:#f1f1f1;
        font-weight:600; color:#333; border-radius:4px 4px 0 0;
    }
    .tureserva-settings-tabs a.active { background:#fff; border-bottom:1px solid #fff; }
    .tureserva-settings-content {
        background:#fff; padding:20px; border:1px solid #ccc;
        border-radius:0 4px 4px 4px;
    }
    .tureserva-settings-content h2 { margin-top:0; }
</style>';

// =======================================================
// 🧱 RENDER: ENCABEZADO Y PESTAÑAS
// =======================================================
echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Ajustes de Alojamiento', 'tureserva' ) . '</h1>';

echo '<div class="tureserva-settings-tabs">';
foreach ( $tabs as $tab => $label ) {
    $active = ( $current_tab === $tab ) ? 'active' : '';
    $url = admin_url( 'edit.php?post_type=alojamiento&page=tureserva-ajustes-alojamiento&tab=' . $tab );
    echo '<a class="' . esc_attr( $active ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}
echo '</div>';

echo '<div class="tureserva-settings-content">';

// =======================================================
// 📄 CARGAR CONTENIDO DE CADA PESTAÑA
// =======================================================
switch ( $current_tab ) {

    case 'general':
        echo '<h2>' . esc_html__( 'Ajustes Generales', 'tureserva' ) . '</h2>';
        echo '<form method="post">';
        wp_nonce_field( 'tureserva_save_settings_nonce' );
        echo '<label>' . esc_html__( 'Nombre del Hotel', 'tureserva' ) . '</label><br>';
        echo '<input type="text" name="tureserva_nombre_hotel" value="' . esc_attr( get_option( 'tureserva_nombre_hotel' ) ) . '" class="regular-text"><br><br>';
        submit_button( __( 'Guardar cambios', 'tureserva' ), 'primary', 'tureserva_save_settings' );
        echo '</form>';
        break;

    case 'emails-admin':
        include TURESERVA_PATH . 'admin/pages/partials/emails/admin.php';
        break;

    case 'emails-cliente':
        include TURESERVA_PATH . 'admin/pages/partials/emails/cliente.php';
        break;

    case 'ajustes-email':
        include TURESERVA_PATH . 'admin/pages/partials/emails/campañas.php';
        break;

    case 'pagos':
        echo '<h2>' . esc_html__( 'Pasarelas de Pago', 'tureserva' ) . '</h2>';
        include TURESERVA_PATH . 'admin/pages/partials/pagos/stripe.php';
        include TURESERVA_PATH . 'admin/pages/partials/pagos/paypal.php';
        include TURESERVA_PATH . 'admin/pages/partials/pagos/transferencia.php';
        include TURESERVA_PATH . 'admin/pages/partials/pagos/manual.php';
        break;

    case 'extensiones':
        echo '<h2>' . esc_html__( 'Extensiones y Add-ons', 'tureserva' ) . '</h2>';
        echo '<p>' . esc_html__( 'Aquí se mostrarán las extensiones instaladas o disponibles.', 'tureserva' ) . '</p>';
        break;

    case 'avanzado':
        echo '<h2>' . esc_html__( 'Opciones Avanzadas', 'tureserva' ) . '</h2>';
        echo '<p>' . esc_html__( 'Herramientas de depuración, sincronización y registros del sistema.', 'tureserva' ) . '</p>';
        break;

    case 'licencia':
        echo '<h2>' . esc_html__( 'Licencia', 'tureserva' ) . '</h2>';
        echo '<p>' . esc_html__( 'Ingrese su clave de licencia para habilitar actualizaciones automáticas y soporte.', 'tureserva' ) . 
             ' <a href="#" target="_blank">' . esc_html__( 'Más info', 'tureserva' ) . '</a></p>';

        $license_key = get_option( 'tureserva_license_key', '' );

        echo '<form method="post">';
        wp_nonce_field( 'tureserva_save_settings_nonce' );
        echo '<label for="tureserva_license_key">' . esc_html__( 'Clave de licencia', 'tureserva' ) . '</label><br>';
        echo '<input type="text" name="tureserva_license_key" id="tureserva_license_key" value="' . esc_attr( $license_key ) . '" class="regular-text"><br><br>';
        submit_button( __( 'Guardar cambios', 'tureserva' ), 'primary', 'tureserva_save_settings' );
        echo '</form>';
        break;

    default:
        echo '<p>' . esc_html__( 'Seleccione una pestaña para configurar los ajustes.', 'tureserva' ) . '</p>';
        break;
}

echo '</div></div>';
