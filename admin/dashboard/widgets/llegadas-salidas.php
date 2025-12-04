<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * WIDGET: Próximas Llegadas y Salidas — TuReserva
 * ==========================================================
 * Muestra las reservas con fechas de check-in o check-out
 * más cercanas (dentro de los próximos 7 días).
 * ==========================================================
 */

function tureserva_widget_llegadas_salidas_render() {
    global $wpdb;

    $meta_checkin  = '_tureserva_checkin';
    $meta_checkout = '_tureserva_checkout';

    $hoy = date('Y-m-d');
    $limite = date('Y-m-d', strtotime('+7 days'));

    // Consultar próximas llegadas (check-in)
    $llegadas = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value AS fecha_checkin
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'tureserva_reserva'
        AND p.post_status = 'publish'
        AND pm.meta_key = %s
        AND pm.meta_value BETWEEN %s AND %s
        ORDER BY pm.meta_value ASC
        LIMIT 10
    ", $meta_checkin, $hoy, $limite));

    // Consultar próximas salidas (check-out)
    $salidas = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value AS fecha_checkout
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'tureserva_reserva'
        AND p.post_status = 'publish'
        AND pm.meta_key = %s
        AND pm.meta_value BETWEEN %s AND %s
        ORDER BY pm.meta_value ASC
        LIMIT 10
    ", $meta_checkout, $hoy, $limite));
    ?>

    <style>
    .tureserva-tabla {
        width: 100%;
        border-collapse: collapse;
        font-family: "Inter", system-ui, sans-serif;
        font-size: 14px;
    }
    .tureserva-tabla th {
        background-color: #f1f2f6;
        text-align: left;
        padding: 6px 8px;
        border-bottom: 1px solid #dfe6e9;
    }
    .tureserva-tabla td {
        padding: 6px 8px;
        border-bottom: 1px solid #f1f2f6;
    }
    .tureserva-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        color: #fff;
    }
    .tureserva-badge.in { background-color: #00b894; }
    .tureserva-badge.out { background-color: #0984e3; }
    </style>

    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
            <h3 style="margin-top:0;">🟢 Próximas llegadas (Check-in)</h3>
            <?php if ($llegadas) : ?>
                <table class="tureserva-tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Reserva</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($llegadas as $r) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n('d M', strtotime($r->fecha_checkin))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($r->ID)); ?>">
                                        <?php echo esc_html($r->post_title ?: 'Sin título'); ?>
                                    </a>
                                </td>
                                <td><span class="tureserva-badge in">Entrada</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No hay llegadas programadas en los próximos 7 días.</p>
            <?php endif; ?>
        </div>

        <div style="flex:1; min-width:300px;">
            <h3 style="margin-top:0;">🔵 Próximas salidas (Check-out)</h3>
            <?php if ($salidas) : ?>
                <table class="tureserva-tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Reserva</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salidas as $r) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n('d M', strtotime($r->fecha_checkout))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($r->ID)); ?>">
                                        <?php echo esc_html($r->post_title ?: 'Sin título'); ?>
                                    </a>
                                </td>
                                <td><span class="tureserva-badge out">Salida</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No hay salidas programadas en los próximos 7 días.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
