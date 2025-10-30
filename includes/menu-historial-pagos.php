<?php
/**
 * ==========================================================
 * ADMIN: Historial de Pagos — TuReserva
 * ==========================================================
 * Muestra los pagos registrados, ya sean automáticos
 * (Stripe, PayU, Yappy) o manuales.
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧭 REGISTRO DEL SUBMENÚ
// =======================================================
add_action( 'admin_menu', 'tureserva_menu_historial_pagos' );
function tureserva_menu_historial_pagos() {
    add_submenu_page(
        'edit.php?post_type=tureserva_reservas',
        'Historial de Pagos',
        'Historia de pagos',
        'manage_options',
        'tureserva_historial_pagos',
        'tureserva_vista_historial_pagos'
    );
}

// =======================================================
// 💳 VISTA PRINCIPAL DEL HISTORIAL DE PAGOS
// =======================================================
function tureserva_vista_historial_pagos() {
    // Buscar pagos creados en el CPT de reservas o pagos
    $pagos = get_posts(array(
        'post_type'      => array('tureserva_reservas', 'tureserva_pagos'),
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'meta_query'     => array(
            array(
                'key'     => '_tureserva_pago_estado',
                'compare' => 'EXISTS'
            )
        )
    ));
    ?>
    <div class="wrap">
        <h1>💳 Historia de pagos</h1>
        <p>Aquí podrás revisar los pagos realizados, pendientes y cancelados.</p>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Reserva</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Pasarela</th>
                    <th>ID de transacción</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $pagos ) : ?>
                    <?php foreach ( $pagos as $post ) :
                        $cliente = get_post_meta( $post->ID, '_tureserva_cliente_nombre', true );
                        $reserva = get_post_meta( $post->ID, '_tureserva_reserva_id', true );
                        $monto   = get_post_meta( $post->ID, '_tureserva_pago_monto', true );
                        $moneda  = strtoupper( get_post_meta( $post->ID, '_tureserva_pago_moneda', true ) ?: 'USD' );
                        $estado  = get_post_meta( $post->ID, '_tureserva_pago_estado', true );
                        $trans   = get_post_meta( $post->ID, '_tureserva_pago_id', true );
                        $pasarela= get_post_meta( $post->ID, '_tureserva_pasarela', true ) ?: 'Manual';
                        $fecha   = get_the_date( 'Y-m-d H:i', $post->ID );

                        $color = match ( strtolower($estado) ) {
                            'pagado' => 'green',
                            'pendiente' => 'orange',
                            'fallido', 'cancelado' => 'red',
                            default => '#555'
                        };
                    ?>
                    <tr>
                        <td><strong>#<?php echo esc_html( $post->ID ); ?></strong></td>
                        <td><?php echo esc_html( $cliente ?: '—' ); ?></td>
                        <td><?php echo $reserva ? '<a href="' . get_edit_post_link($reserva) . '">#' . $reserva . '</a>' : '—'; ?></td>
                        <td><?php echo esc_html( number_format( $monto, 2 ) . ' ' . $moneda ); ?></td>
                        <td><span style="color:<?php echo $color; ?>;font-weight:600;"><?php echo ucfirst( $estado ?: '—' ); ?></span></td>
                        <td><?php echo esc_html( $pasarela ); ?></td>
                        <td><?php echo esc_html( $trans ?: '—' ); ?></td>
                        <td><?php echo esc_html( $fecha ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:20px;">
                            🕓 No se encontraron pagos registrados aún.
                            <br><small>Los pagos confirmados desde Stripe o agregados manualmente aparecerán aquí.</small>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p style="margin-top:20px;color:#777;">
            En el futuro se integrará con múltiples pasarelas (Stripe, PayU, Yappy, etc.) y exportación a CSV.
        </p>
    </div>
    <?php
}

