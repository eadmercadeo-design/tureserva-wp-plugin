<?php
/**
 * ==========================================================
 * ADMIN PARTIAL: Licencia — TuReserva
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// 💾 Guardar licencia
if (isset($_POST['tureserva_guardar_licencia']) && check_admin_referer('tureserva_guardar_licencia_nonce')) {
    $license_key = sanitize_text_field($_POST['tureserva_license_key']);
    update_option('tureserva_license_key', $license_key);

    // 🔑 Licencias válidas para pruebas
    $valid_licenses = ['TURESERVA-2025-FREE', 'TURESERVA-2025-PRO', 'TURESERVA-2025-ULTIMATE'];

    if (in_array($license_key, $valid_licenses)) {
        update_option('tureserva_license_status', 'active');
        echo '<div class="updated notice"><p>✅ Licencia validada correctamente.</p></div>';
    } else {
        update_option('tureserva_license_status', 'invalid');
        echo '<div class="error notice"><p>❌ La clave de licencia no es válida.</p></div>';
    }
}

$license_key = get_option('tureserva_license_key', '');
$status = get_option('tureserva_license_status', 'inactive');
?>

<h2>🔑 Licencia del plugin TuReserva</h2>
<p>Ingrese su clave de licencia para habilitar actualizaciones y soporte técnico.</p>

<form method="post">
    <?php wp_nonce_field('tureserva_guardar_licencia_nonce'); ?>

    <table class="form-table">
        <tr>
            <th><label for="tureserva_license_key">Clave de licencia</label></th>
            <td>
                <input type="text" name="tureserva_license_key" id="tureserva_license_key"
                       value="<?php echo esc_attr($license_key); ?>"
                       style="width:300px;" placeholder="TURESERVA-2025-XXXX">
            </td>
        </tr>
        <tr>
            <th>Estado actual</th>
            <td>
                <?php
                if ($status === 'active') {
                    echo '<span style="color:green;font-weight:600;">✅ Activa</span>';
                } elseif ($status === 'invalid') {
                    echo '<span style="color:red;font-weight:600;">❌ Inválida</span>';
                } else {
                    echo '<span style="color:gray;font-weight:600;">⚪ Sin activar</span>';
                }
                ?>
            </td>
        </tr>
    </table>

    <?php submit_button('Guardar cambios', 'primary', 'tureserva_guardar_licencia'); ?>
</form>

<hr>
<p><strong>Licencias de prueba:</strong></p>
<ul>
    <li><code>TURESERVA-2025-FREE</code></li>
    <li><code>TURESERVA-2025-PRO</code></li>
    <li><code>TURESERVA-2025-ULTIMATE</code></li>
</ul>