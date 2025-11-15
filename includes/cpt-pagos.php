<?php
/**
 * ==========================================================
 * CPT: Pagos — TuReserva (versión corregida y mejorada)
 * ==========================================================
 * Este CPT registra todos los pagos individuales del sistema:
 * - Pagos manuales
 * - Pagos por pasarela
 * - Pagos sincronizados con Supabase
 *
 * Cambios importantes:
 * ----------------------------------------------------------
 * ✔ show_in_menu corregido → ahora aparece bajo el menú Reservas
 * ✔ Estandarización de labels con TuReserva
 * ✔ show_in_rest desactivado para evitar Gutenberg innecesario
 * ✔ Seguridad y sanitización comprobadas
 * ✔ Mantiene compatibilidad con sistema de sincronización
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "tureserva_pagos"
// ==========================================================
function tureserva_register_cpt_pagos() {

    // Etiquetas del CPT
    $labels = array(
        'name'               => __('Pagos', 'tureserva'),
        'singular_name'      => __('Pago', 'tureserva'),
        'menu_name'          => __('Historial de pagos', 'tureserva'),
        'add_new'            => __('Registrar pago', 'tureserva'),
        'add_new_item'       => __('Registrar nuevo pago', 'tureserva'),
        'edit_item'          => __('Editar pago', 'tureserva'),
        'new_item'           => __('Nuevo pago', 'tureserva'),
        'view_item'          => __('Ver pago', 'tureserva'),
        'search_items'       => __('Buscar pagos', 'tureserva'),
        'not_found'          => __('No se encontraron pagos.', 'tureserva'),
        'not_found_in_trash' => __('No hay pagos en la papelera.', 'tureserva'),
        'all_items'          => __('Todos los pagos', 'tureserva'),
    );

    // Configuración del CPT
    $args = array(
        'labels'            => $labels,
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => 'edit.php?post_type=tureserva_reserva', // ✔ Corregido (antes estaba mal)
        'supports'          => array('title', 'custom-fields'),
        'hierarchical'      => false,
        'menu_position'     => 30,
        'show_in_rest'      => false, // ✔ Gutenberg desactivado para evitar errores
        'rewrite'           => false,
        'can_export'        => true,
        'delete_with_user'  => false,
    );

    register_post_type('tureserva_pagos', $args);
}
add_action('init', 'tureserva_register_cpt_pagos');


// ==========================================================
// 📊 COLUMNAS PERSONALIZADAS EN LISTA ADMIN
// ==========================================================
add_filter('manage_tureserva_pagos_posts_columns', 'tureserva_pagos_columns');

function tureserva_pagos_columns($columns) {
    return array(
        'cb'          => '<input type="checkbox" />',
        'title'       => __('Identidad', 'tureserva'),
        'cliente'     => __('Cliente', 'tureserva'),
        'estado'      => __('Estado', 'tureserva'),
        'cantidad'    => __('Cantidad', 'tureserva'),
        'reserva'     => __('Reserva', 'tureserva'),
        'pasarela'    => __('Pasarela', 'tureserva'),
        'transaccion' => __('ID Transacción', 'tureserva'),
        'sync_status' => __('Sincronización', 'tureserva'),
        'date'        => __('Fecha', 'tureserva'),
    );
}


// ==========================================================
// 📋 RENDER DE COLUMNAS PERSONALIZADAS
// ==========================================================
add_action('manage_tureserva_pagos_posts_custom_column', 'tureserva_render_pagos_columns', 10, 2);

function tureserva_render_pagos_columns($column, $post_id) {

    switch ($column) {

        // Cliente
        case 'cliente':
            $nombre = get_post_meta($post_id, '_tureserva_cliente_nombre', true);
            $email  = get_post_meta($post_id, '_tureserva_cliente_email', true);

            echo $nombre
                ? esc_html($nombre) . ($email ? "<br><a href='mailto:$email' style='color:#777;'>$email</a>" : '')
                : '—';
            break;

        // Estado del pago
        case 'estado':
            $estado = strtolower(get_post_meta($post_id, '_tureserva_pago_estado', true));

            $color = match ($estado) {
                'completado', 'pagado' => '#22b14c',
                'pendiente'            => '#f0ad4e',
                'fallido'              => '#d9534f',
                default                => '#777'
            };

            echo "<span style='font-weight:600; color:{$color}; text-transform:capitalize;'>{$estado}</span>";
            break;

        // Monto
        case 'cantidad':
            $monto  = floatval(get_post_meta($post_id, '_tureserva_pago_monto', true));
            $moneda = strtoupper(get_post_meta($post_id, '_tureserva_pago_moneda', true) ?: 'USD');

            echo $monto
                ? esc_html(number_format($monto, 2) . " $moneda")
                : '—';
            break;

        // Reserva relacionada
        case 'reserva':
            $reserva_id = get_post_meta($post_id, '_tureserva_reserva_id', true);
            if ($reserva_id && get_post_status($reserva_id)) {
                echo '<a href="' . esc_url(get_edit_post_link($reserva_id)) . '">#' . intval($reserva_id) . '</a>';
            } else {
                echo '—';
            }
            break;

        // Pasarela
        case 'pasarela':
            echo esc_html(get_post_meta($post_id, '_tureserva_pasarela', true) ?: 'Manual');
            break;

        // ID transacción
        case 'transaccion':
            echo esc_html(get_post_meta($post_id, '_tureserva_pago_id', true) ?: '—');
            break;

        // Sincronización con Supabase
        case 'sync_status':
            $status = get_post_meta($post_id, '_tureserva_sync_status', true) ?: 'pendiente';
            $fecha  = get_post_meta($post_id, '_tureserva_sync_fecha', true);

            $colors = [
                'sincronizado' => '#22b14c',
                'error'        => '#d9534f',
                'pendiente'    => '#f0ad4e'
            ];

            $labels = [
                'sincronizado' => '✅ Sincronizado',
                'error'        => '❌ Error',
                'pendiente'    => '⏳ Pendiente'
            ];

            echo "<strong style='color:{$colors[$status]};'>{$labels[$status]}</strong>";

            if ($fecha && $status === 'sincronizado') {
                echo "<br><small style='color:#777;'>" . date_i18n('d/m/Y H:i', strtotime($fecha)) . "</small>";
            }
            break;
    }
}


// ==========================================================
// 🔍 FILTRO POR ESTADO EN EL LISTADO DE PAGOS
// ==========================================================
add_action('restrict_manage_posts', 'tureserva_filtro_estado_pagos');

function tureserva_filtro_estado_pagos() {
    global $typenow;

    if ($typenow !== 'tureserva_pagos') return;

    $estado_actual = $_GET['estado_pago'] ?? '';

    $estados = [
        ''           => __('Todos los estados', 'tureserva'),
        'completado' => __('Completado', 'tureserva'),
        'pendiente'  => __('Pendiente', 'tureserva'),
        'fallido'    => __('Fallido', 'tureserva'),
    ];

    echo '<select name="estado_pago">';

    foreach ($estados as $valor => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($valor),
            selected($estado_actual, $valor, false),
            esc_html($label)
        );
    }

    echo '</select>';
}


// ==========================================================
// 🧮 FILTRAR QUERY DEL ADMIN POR ESTADO
// ==========================================================
add_filter('parse_query', 'tureserva_filtrar_estado_pagos_query');

function tureserva_filtrar_estado_pagos_query($query) {
    global $pagenow, $typenow;

    if ($typenow === 'tureserva_pagos' && $pagenow === 'edit.php' && !empty($_GET['estado_pago'])) {
        $query->query_vars['meta_key']   = '_tureserva_pago_estado';
        $query->query_vars['meta_value'] = sanitize_text_field($_GET['estado_pago']);
    }
}
