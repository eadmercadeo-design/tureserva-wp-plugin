<?php
/**
 * ==========================================================
 * BLOQUES: Email Pre-checkin + Campañas Especiales
 * ==========================================================
 * Este archivo se incluye desde ajustes-alojamiento.php
 * dentro de la pestaña "Email (Cliente)".
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;
?>

<!-- =======================================================
     EMAIL AUTOMÁTICO — PRE-CHECKIN (1 hora antes)
======================================================= -->
<hr>
<h3 style="margin-top:35px;"><?php _e('Email automático — 1 hora antes del check-in', 'tureserva'); ?></h3>
<p><?php _e('Este correo se enviará automáticamente una hora antes del horario de check-in, para proporcionar al huésped la clave de acceso, instrucciones y datos del WiFi.', 'tureserva'); ?></p>

<?php
$precheck_activo = get_option('tureserva_cliente_email_precheckin_activo', true);
$precheck_tema   = get_option('tureserva_cliente_email_precheckin_tema', __('Tu estancia comienza pronto – Detalles importantes', 'tureserva'));
$precheck_cuerpo = get_option('tureserva_cliente_email_precheckin_cuerpo',
"Hola %customer_name%,\n\nTu llegada está programada para las %check_in_time%.\n\n🔑 Clave de acceso: %room_access_code%\n📶 WiFi: %wifi_ssid% / %wifi_password%\n🏠 Instrucciones de ingreso: %entry_instructions%\n📞 Contacto: %host_phone%\n\n¡Te esperamos pronto en %hotel_name%!");
?>

<table class="form-table">
    <tr>
        <th><?php _e('Activar notificación', 'tureserva'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="cliente_email_precheckin_activo" value="1" <?php checked($precheck_activo, true); ?>>
                <?php _e('Enviar automáticamente una hora antes del check-in.', 'tureserva'); ?>
            </label>
        </td>
    </tr>

    <tr>
        <th><label for="cliente_email_precheckin_tema"><?php _e('Tema del correo', 'tureserva'); ?></label></th>
        <td><input type="text" name="cliente_email_precheckin_tema" id="cliente_email_precheckin_tema" value="<?php echo esc_attr($precheck_tema); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label><?php _e('Plantilla del correo', 'tureserva'); ?></label></th>
        <td>
            <?php
            wp_editor(
                $precheck_cuerpo,
                'cliente_email_precheckin_cuerpo',
                [
                    'textarea_name' => 'cliente_email_precheckin_cuerpo',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny'         => true,
                ]
            );
            ?>
            <p class="description"><?php _e('Puede usar variables dinámicas: %customer_name%, %check_in_time%, %room_access_code%, %wifi_ssid%, %wifi_password%, %entry_instructions%, %host_phone%, %hotel_name%.', 'tureserva'); ?></p>
        </td>
    </tr>
</table>


<!-- =======================================================
     EMAIL DE CAMPAÑAS ESPECIALES — ENVÍO MASIVO
======================================================= -->
<hr>
<h3 style="margin-top:35px;"><?php _e('Campañas especiales — Envío a huéspedes anteriores', 'tureserva'); ?></h3>
<p><?php _e('Permite enviar manualmente una campaña de marketing o información general a todos los huéspedes que se han alojado anteriormente.', 'tureserva'); ?></p>

<?php
$campania_tema   = get_option('tureserva_cliente_email_campania_tema', __('Promociones y novedades de %hotel_name%', 'tureserva'));
$campania_cuerpo = get_option('tureserva_cliente_email_campania_cuerpo',
"Hola %customer_name%,\n\nQueremos agradecerte por hospedarte con nosotros. 🎉\nAprovecha nuestras nuevas promociones y descuentos para tu próxima visita.\n\n🌴 %promotion_details%\n\n¡Te esperamos pronto!\nEl equipo de %hotel_name%");
?>

<table class="form-table">
    <tr>
        <th><label for="cliente_email_campania_tema"><?php _e('Asunto del correo', 'tureserva'); ?></label></th>
        <td><input type="text" name="cliente_email_campania_tema" id="cliente_email_campania_tema" value="<?php echo esc_attr($campania_tema); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label><?php _e('Contenido del correo', 'tureserva'); ?></label></th>
        <td>
            <?php
            wp_editor(
                $campania_cuerpo,
                'cliente_email_campania_cuerpo',
                [
                    'textarea_name' => 'cliente_email_campania_cuerpo',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny'         => true,
                ]
            );
            ?>
            <p class="description"><?php _e('Etiquetas disponibles: %customer_name%, %promotion_details%, %hotel_name%.', 'tureserva'); ?></p>
        </td>
    </tr>

    <tr>
        <th><?php _e('Enviar campaña ahora', 'tureserva'); ?></th>
        <td>
            <button type="button" class="button button-primary" id="enviar_campania"><?php _e('Enviar correo masivo', 'tureserva'); ?></button>
            <span id="campania_status" style="margin-left:10px;font-weight:500;"></span>
            <p class="description"><?php _e('Este botón enviará el correo a todos los huéspedes registrados en reservas anteriores. Se recomienda usar con precaución.', 'tureserva'); ?></p>
        </td>
    </tr>
</table>

<!-- =======================================================
     SCRIPT AJAX — ENVÍO MASIVO
======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const boton = document.getElementById('enviar_campania');
    const status = document.getElementById('campania_status');

    if(boton){
        boton.addEventListener('click', function(){
            if(!confirm('<?php _e('¿Desea enviar esta campaña a todos los huéspedes registrados?', 'tureserva'); ?>')){
                return;
            }

            status.innerHTML = '<?php _e('📨 Enviando correos... por favor espere', 'tureserva'); ?>';

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'tureserva_enviar_campania' })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    status.innerHTML = '✅ <?php _e('Campaña enviada correctamente.', 'tureserva'); ?>';
                } else {
                    status.innerHTML = '⚠️ ' + (data.data || '<?php _e('Error al enviar la campaña.', 'tureserva'); ?>');
                }
            })
            .catch(() => {
                status.innerHTML = '❌ <?php _e('Error de conexión con el servidor.', 'tureserva'); ?>';
            });
        });
    }
});
</script>
