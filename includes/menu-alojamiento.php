<?php
/**
 * ==========================================================
 * MENÚ PRINCIPAL: Alojamientos — TuReserva
 * ==========================================================
 * Este archivo crea el menú principal "Alojamiento" y organiza
 * todos sus submenús según el orden solicitado.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🧭 REGISTRO DEL MENÚ PRINCIPAL "ALOJAMIENTO"
// ==========================================================
add_action('admin_menu', 'tureserva_admin_menu_alojamiento', 100); // Prioridad alta para reordenar al final

function tureserva_admin_menu_alojamiento()
{
    // ✔ Verificar existencia del CPT principal
    if (!post_type_exists('trs_alojamiento')) return;

    // ======================================================
    // 1. Menú principal (Top Level)
    // ======================================================
    add_menu_page(
        __('Alojamiento', 'tureserva'),
        __('Alojamiento', 'tureserva'),
        'manage_options',
        'edit.php?post_type=trs_alojamiento',
        '',
        'dashicons-building',
        5
    );

    // ======================================================
    // 2. Submenús Manuales (Sobrescribiendo/Añadiendo)
    // ======================================================

    // [1] Tipos de alojamientos (Renombrando "Todos los items")
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Tipos de alojamientos', 'tureserva'),
        __('Tipos de alojamientos', 'tureserva'),
        'manage_options',
        'edit.php?post_type=trs_alojamiento'
    );

    // [2] Añadir nuevo tipo de alojamiento
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Añadir nuevo tipo de alojamiento', 'tureserva'),
        __('Añadir nuevo tipo de alojamiento', 'tureserva'),
        'manage_options',
        'post-new.php?post_type=trs_alojamiento'
    );

    // [3] Categorías
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Categorías', 'tureserva'),
        __('Categorías', 'tureserva'),
        'manage_options',
        'edit-tags.php?taxonomy=categoria_alojamiento&post_type=trs_alojamiento'
    );

    // [4] Etiquetas
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Etiquetas', 'tureserva'),
        __('Etiquetas', 'tureserva'),
        'manage_options',
        'edit-tags.php?taxonomy=tureserva_etiqueta&post_type=trs_alojamiento'
    );

    // [10] Generar Alojamientos
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Generar alojamientos', 'tureserva'),
        __('Generar alojamientos', 'tureserva'),
        'manage_options',
        'tureserva-generar-alojamientos',
        'tureserva_render_generar_alojamientos_page'
    );

    // [11] Ajustes
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Ajustes de Alojamiento', 'tureserva'),
        __('Ajustes', 'tureserva'),
        'manage_options',
        'tureserva-ajustes-alojamiento',
        'tureserva_render_ajustes_alojamiento_page'
    );

    // [12] Idioma (Placeholder si no existe la función)
    $idioma_cb = function_exists('tureserva_render_idioma_alojamiento_page') ? 'tureserva_render_idioma_alojamiento_page' : function(){ echo '<h1>Idioma</h1>'; };
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Idioma', 'tureserva'),
        __('Idioma', 'tureserva'),
        'manage_options',
        'tureserva-idioma-alojamiento',
        $idioma_cb
    );

    // [13] Códigos cortos
    require_once TURESERVA_PATH . 'admin/pages/codigos-cortos.php';
    add_submenu_page(
        'edit.php?post_type=trs_alojamiento',
        __('Códigos cortos', 'tureserva'),
        __('Códigos cortos', 'tureserva'),
        'manage_options',
        'tureserva-codigos-cortos',
        'tureserva_render_codigos_cortos_page'
    );

    // ======================================================
    // 🔄 REORDENAMIENTO FORZADO DEL MENÚ
    // ======================================================
    global $submenu;
    $parent_slug = 'edit.php?post_type=trs_alojamiento';

    if (isset($submenu[$parent_slug])) {
        $items = $submenu[$parent_slug];
        $ordered_items = [];

        // Definir el orden deseado por SLUG
        $order_slugs = [
            'edit.php?post_type=trs_alojamiento',                                         // 1. Tipos
            'post-new.php?post_type=trs_alojamiento',                                     // 2. Añadir nuevo
            'edit-tags.php?taxonomy=categoria_alojamiento&post_type=trs_alojamiento',     // 3. Categorías
            'edit-tags.php?taxonomy=tureserva_etiqueta&post_type=trs_alojamiento',        // 4. Etiquetas
            'edit.php?post_type=tureserva_servicio',                                      // 5. Servicios
            'edit.php?post_type=tureserva_atributo',                                      // 6. Atributos
            'edit.php?post_type=temporada',                                               // 7. Temporadas
            'edit.php?post_type=tureserva_tarifa',                                        // 8. Tarifas
            'tureserva-comodidades',                                                      // 9. Comodidades
            'tureserva-generar-alojamientos',                                             // 10. Generar
            'tureserva-ajustes-alojamiento',                                              // 11. Ajustes
            'tureserva-idioma-alojamiento',                                               // 12. Idioma
            'tureserva-codigos-cortos'                                                    // 13. Códigos cortos
        ];

        // 1. Extraer y ordenar los que coinciden
        foreach ($order_slugs as $slug) {
            foreach ($items as $key => $item) {
                if ($item[2] === $slug) {
                    $ordered_items[] = $item;
                    unset($items[$key]); // Quitar de la lista original
                    break;
                }
            }
        }

        // 2. Añadir cualquier sobrante al final (por si acaso)
        if (!empty($items)) {
            foreach ($items as $item) {
                $ordered_items[] = $item;
            }
        }

        // 3. Aplicar el nuevo orden
        $submenu[$parent_slug] = $ordered_items;
    }
}
