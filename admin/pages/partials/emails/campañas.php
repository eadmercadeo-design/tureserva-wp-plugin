<?php
/**
 * ==========================================================
 * EMAILS CAMPAÑAS — Ajustes de campañas automáticas
 * ==========================================================
 */
if ( ! defined( 'ABSPATH' ) ) exit;

echo '<h2>' . __( 'Campañas Automáticas', 'tureserva' ) . '</h2>';
echo '<p>' . __( 'Configure los mensajes automáticos de recordatorio antes del check-in o promociones después de la estancia.', 'tureserva' ) . '</p>';

if ( isset($_POST['tureserva_save_settings']) && check_admin_referer('tureserva_save_settings_nonce') ) {
    update_option( 'tureserva_campania_checkin_dias', intval($_POST['tureserva_campania_checkin_dias']) );
    update_option( 'tureserva_campania_checkin_mensaje', wp_kses_post($_POST['tureserva_campania_checkin_mensaje']) );
    echo '<div class="updated"><p>' . __( 'Configuración guardada correctamente.', 'tureserva' ) . '</p></div>';
}

$dias = get_option('tureserva_campania_checkin_dias', 2);
$mensaje = get_option('tureserva_campania_checkin_mensaje', __( '¡Le esperamos pronto! Este es un recordatorio de su próxima estancia.', 'tureserva' ));
?>

<form method="post">
    <?php wp_nonce_field('tureserva_save_settings_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Días antes del check-in', 'tureserva'); ?></th>
            <td><input type="number" name="tureserva_campania_checki
