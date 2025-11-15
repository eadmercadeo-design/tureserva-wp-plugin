<?php
/**
 * ==========================================================
 * CPT: Reservas — TuReserva (versión corregida y comentada)
 * ==========================================================
 * Este archivo registra el Custom Post Type principal del 
 * sistema de reservas. Está diseñado para:
 *
 * ✔ Mantener la UI del CPT en WordPress (show_ui = true)
 * ✔ Evitar que el CPT cree su propio menú (show_in_menu = false)
 * ✔ Usar MENÚ PERSONALIZADO desde menu-reservas.php
 * ✔ Tener columnas personalizadas claras y funcionales
 * ✔ Redirigir “Añadir nueva” hacia la interfaz optimizada
 *
 * IMPORTANTE:
 * El nombre del CPT debe ser único y consistente.
 * Aquí lo estandarizamos como: tureserva_reserva (singular)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🔧 1. REGISTRO DEL CUSTOM POST TYPE "Reserva"
// =======================================================
/**
 * Se registra el CPT principal del sistema.
 * - No aparece en el menú nativo porque su menú real está 
 *   en /includes/menu-reservas.php.
 * - Gutenberg desactivado porque usas pantalla personalizada.
 */
function tureserva_register_cpt_reservas() {

    // Etiquetas que ve el administrador
    $labels = array(
        'name'               => __('Reservas', 'tureserva'),
        'singular_name'      => __('Reserva', 'tureserva'),
        'menu_name'          => __('Reservas', 'tureserva'),
        'all_items'          => __('Todas las reservas', 'tureserva'),
        'add_new_item'       => __('Añadir nueva reserva', 'tureserva'),
    );

    // Configuración interna del CPT
    $args = array(
        'labels'            => $labels,
        'public'            => false,            // No accesible desde frontend
        'show_ui'           => true,             // Visible en administrador WP
        'show_in_menu'      => false,            // Menú personalizado
        'supports'          => array('title'),   // Solo necesita título
        'capability_type'   => 'post',
        'show_in_rest'      => false,            // Gutenberg OFF
        'rewrite'           => false             // Sin URL pública
    );

    // Registro del CPT (singular — muy importante)
    register_post_type('tureserva_reserva', $args);
}
add_action('init', 'tureserva_register_cpt_reservas');


// =======================================================
// 🚀 2. REDIRECCIÓN DE “Añadir nueva”
// =======================================================
/**
 * Cuando alguien intenta crear una reserva desde:
 *
 * /wp-admin/post-new.php?post_type=tureserva_reserva
 *
 * Redirigimos a nuestra interfaz personalizada:
 *
 * /wp-admin/edit.php?post_type=tureserva_reserva&page=tureserva-add-reserva
 *
 * Esta pantalla vive en:
 * /admin/reservas/add-new.php
 */
add_action('load-post-new.php', function () {

    global $typenow;

    // Corrección: Antes usabas "tureserva_reservas" (plural — no existe)
    if ($typenow === 'tureserva_reserva') {

        wp_redirect(
            admin_url('edit.php?post_type=tureserva_reserva&page=tureserva-add-reserva')
        );
        exit;
    }
});


// =======================================================
// 🧾 3. DEFINICIÓN DE COLUMNAS PERSONALIZADAS
// =======================================================
/**
 * Reemplaza las columnas estándar por columnas útiles
 * para un sistema real de reservas.
 */
add_filter('manage_edit-tureserva_reserva_columns', 'tureserva_reservas_columns');

function tureserva_reservas_columns($columns)
{
    return array(
        'cb'          => '<input type="checkbox" />',
        'title'       => __('Identidad', 'tureserva'),     // Nombre / ID reserva
        'estado'      => __('Estado', 'tureserva'),
        'fechas'      => __('Check-in / Check-out', 'tureserva'),
        'invitados'   => __('Invitados', 'tureserva'),
        'cliente'     => __('Cliente', 'tureserva'),
        'precio'      => __('Precio', 'tureserva'),
        'alojamiento' => __('Alojamiento', 'tureserva'),
        'date'        => __('Fecha', 'tureserva'),
    );
}


// =======================================================
// 🧮 4. RENDERIZADO DE DATOS EN CADA COLUMNA
// =======================================================
/**
 * Imprime los datos reales guardados en la reserva.
 * Cada metadato debe existir desde tus procesos de creación.
 */
add_action(
    'manage_tureserva_reserva_posts_custom_column',
    'tureserva_render_reservas_columns',
    10,
    2
);

function tureserva_render_reservas_columns($column, $post_id)
{
    switch ($column) {

        // --------------------------------------
        // 🟧 Estado de la reserva
        // --------------------------------------
        case 'estado':
            $estado = get_post_meta($post_id, '_tureserva_estado', true) ?: 'pendiente';
            $color = match ($estado) {
                'confirmada' => 'green',
                'cancelada'  => 'red',
                default      => 'orange',
            };
            echo '<strong style="color:' . esc_attr($color) . ';">'
                 . esc_html(ucfirst($estado))
                 . '</strong>';
            break;

        // --------------------------------------
        // 📆 Fechas de reserva
        // --------------------------------------
        case 'fechas':
            $checkin  = get_post_meta($post_id, '_tureserva_checkin', true);
            $checkout = get_post_meta($post_id, '_tureserva_checkout', true);
            echo esc_html($checkin && $checkout ? "$checkin — $checkout" : '—');
            break;

        // --------------------------------------
        // 👤 Invitados
        // --------------------------------------
        case 'invitados':
            $adultos = get_post_meta($post_id, '_tureserva_adultos', true) ?: 0;
            $ninos   = get_post_meta($post_id, '_tureserva_ninos', true) ?: 0;
            echo esc_html("Adultos: $adultos / Niños: $ninos");
            break;

        // --------------------------------------
        // 📧 Cliente
        // --------------------------------------
        case 'cliente':
            $nombre = get_post_meta($post_id, '_tureserva_cliente_nombre', true);
            $email  = get_post_meta($post_id, '_tureserva_cliente_email', true);
            echo esc_html($nombre ? "$nombre ($email)" : '—');
            break;

        // --------------------------------------
        // 💵 Precio total cobrado
        // --------------------------------------
        case 'precio':
            $precio = floatval(get_post_meta($post_id, '_tureserva_precio_total', true));
            echo esc_html($precio ? '$' . number_format($precio, 2) : '—');
            break;

        // --------------------------------------
        // 🏨 Alojamiento asignado
        // --------------------------------------
        case 'alojamiento':
            $id = get_post_meta($post_id, '_tureserva_alojamiento_id', true);
            if ($id) {
                echo '<a href="' . esc_url(get_edit_post_link($id)) . '">'
                     . esc_html(get_the_title($id))
                     . '</a>';
            } else {
                echo '—';
            }
            break;
    }
}