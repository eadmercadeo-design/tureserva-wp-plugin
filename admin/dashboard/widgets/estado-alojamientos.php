<?php
/**
 * Widget: Estado de Alojamientos
 * Muestra una tabla con el estado actual (hoy) de cada alojamiento.
 */

if (!defined('ABSPATH')) exit;

function tureserva_widget_estado_alojamientos_render() {
    // 1. Obtener fecha de hoy (según zona horaria de WP)
    $hoy = current_time('Y-m-d');
    $hoy_timestamp = strtotime($hoy);

    // 2. Obtener todos los alojamientos
    $alojamientos = get_posts(array(
        'post_type'      => 'trs_alojamiento',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC'
    ));

    if (empty($alojamientos)) {
        echo '<p>No hay alojamientos registrados.</p>';
        return;
    }

    // 3. Preparar datos para la tabla
    $lista_estado = array();

    foreach ($alojamientos as $alojamiento) {
        $id = $alojamiento->ID;
        $nombre = $alojamiento->post_title;
        $estado = 'disponible'; // Default
        $detalle = '';

        // A. Verificar Reservas (Confirmadas o Pendientes) que incluyan hoy
        // Buscamos reservas donde:
        // checkin <= hoy AND checkout > hoy
        // (El checkout es el día de salida, así que si checkout == hoy, ya salió, está libre hoy... 
        //  depende de la lógica de negocio, pero usualmente checkout day is free for checkin.
        //  Pero si checkin == hoy, está ocupado desde hoy.
        //  Entonces: (checkin <= hoy) AND (checkout > hoy)
        
        $args_reserva = array(
            'post_type'   => 'tureserva_reserva',
            'post_status' => array('publish', 'pending', 'confirmed'),
            'meta_query'  => array(
                'relation' => 'AND',
                array(
                    'key'     => '_tureserva_alojamiento_id',
                    'value'   => $id,
                    'compare' => '='
                ),
                array(
                    'key'     => '_tureserva_estado',
                    'value'   => array('confirmada', 'pendiente'),
                    'compare' => 'IN'
                ),
                array(
                    'key'     => '_tureserva_checkin',
                    'value'   => $hoy,
                    'compare' => '<=',
                    'type'    => 'DATE'
                ),
                array(
                    'key'     => '_tureserva_checkout',
                    'value'   => $hoy,
                    'compare' => '>',
                    'type'    => 'DATE'
                )
            ),
            'posts_per_page' => 1 // Solo necesitamos saber si hay una
        );

        $reservas = get_posts($args_reserva);

        if (!empty($reservas)) {
            $reserva = $reservas[0];
            $estado_reserva = get_post_meta($reserva->ID, '_tureserva_estado', true);
            $cliente = get_post_meta($reserva->ID, '_tureserva_cliente_nombre', true); // Asumiendo meta
            if (!$cliente) $cliente = 'Cliente #' . $reserva->ID;

            if ($estado_reserva === 'pendiente') {
                $estado = 'reservado'; // Pendiente de pago/confirmación
                $detalle = 'Pendiente: ' . $cliente;
            } else {
                $estado = 'ocupado'; // Confirmada
                $detalle = 'Ocupado: ' . $cliente;
            }
        } else {
            // B. Verificar Bloqueos Manuales
            // Bloqueos se guardan en meta '_tureserva_bloqueos' como array de arrays
            $bloqueos = get_post_meta($id, '_tureserva_bloqueos', true);
            if (!empty($bloqueos) && is_array($bloqueos)) {
                foreach ($bloqueos as $bloqueo) {
                    $b_inicio = strtotime($bloqueo['inicio'] ?? '');
                    $b_fin    = strtotime($bloqueo['fin'] ?? '');
                    
                    // Lógica de solapamiento con "hoy"
                    // El bloqueo incluye hoy si: inicio <= hoy AND fin > hoy
                    // (Asumiendo que fin es exclusivo o inclusivo? En core-availability:
                    //  if ( $inicio < $bloqueo_fin && $fin > $bloqueo_inicio )
                    //  Para un solo día (hoy): inicio=hoy, fin=hoy+1 dia (o final del dia)
                    //  Simplificado: si hoy está entre inicio (inclusive) y fin (exclusive o inclusive según lógica)
                    //  Vamos a asumir lógica estándar: [inicio, fin) o [inicio, fin]
                    //  Si bloqueo va de 2025-12-01 a 2025-12-05.
                    //  Hoy 2025-12-02. 
                    //  Si b_inicio <= hoy_timestamp && b_fin >= hoy_timestamp ... 
                    //  Revisemos core-availability: $inicio < $bloqueo_fin && $fin > $bloqueo_inicio
                    //  Nuestro rango "hoy" es [hoy 00:00, hoy 23:59] o [hoy, mañana]
                    //  Usemos timestamps de fechas puras.
                    
                    if ($b_inicio && $b_fin) {
                        // Si el bloqueo cubre hoy.
                        // Bloqueo: 1 dic - 3 dic. Hoy: 2 dic.
                        // 1 <= 2 && 3 > 2 (si 3 es checkout date)
                        if ($b_inicio <= $hoy_timestamp && $b_fin > $hoy_timestamp) {
                            $estado = 'bloqueado';
                            $detalle = $bloqueo['motivo'] ?? 'Bloqueo manual';
                            break;
                        }
                    }
                }
            }
        }

        $lista_estado[] = array(
            'nombre'  => $nombre,
            'estado'  => $estado,
            'detalle' => $detalle,
            'edit_link' => get_edit_post_link($id)
        );
    }

    // 4. Renderizar HTML
    ?>
    <style>
        .trs-status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .trs-status-table th, .trs-status-table td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .trs-status-table th {
            color: #646970;
            font-weight: 600;
        }
        .trs-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .trs-status-disponible {
            background-color: #e6f6e6;
            color: #2e7d32;
        }
        .trs-status-ocupado {
            background-color: #ffebee;
            color: #c62828;
        }
        .trs-status-reservado {
            background-color: #fff8e1;
            color: #f57f17;
        }
        .trs-status-bloqueado {
            background-color: #eceff1;
            color: #455a64;
        }
        .trs-status-row:last-child td {
            border-bottom: none;
        }
        .trs-date-header {
            margin-bottom: 10px;
            font-size: 13px;
            color: #50575e;
        }
    </style>

    <div class="trs-date-header">
        <strong>Fecha:</strong> <?php echo date_i18n(get_option('date_format'), $hoy_timestamp); ?>
    </div>

    <table class="trs-status-table">
        <thead>
            <tr>
                <th>Alojamiento</th>
                <th>Estado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_estado as $item): ?>
                <tr class="trs-status-row">
                    <td>
                        <a href="<?php echo esc_url($item['edit_link']); ?>" style="text-decoration:none; font-weight:500;">
                            <?php echo esc_html($item['nombre']); ?>
                        </a>
                    </td>
                    <td>
                        <?php
                        $class = 'trs-status-' . $item['estado'];
                        $label = ucfirst($item['estado']);
                        ?>
                        <span class="trs-status-badge <?php echo esc_attr($class); ?>">
                            <?php echo esc_html($label); ?>
                        </span>
                    </td>
                    <td style="color: #646970; font-size: 12px;">
                        <?php echo esc_html($item['detalle']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
