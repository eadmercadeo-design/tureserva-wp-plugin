<?php
/**
 * ==========================================================
 * CPT: Atributos — TuReserva (versión corregida y comentada)
 * ==========================================================
 * Este CPT se usa para crear “atributos” de alojamientos:
 * - Ej: Vista al mar, Baño privado, Aire acondicionado, etc.
 *
 * Se ubica dentro del menú:
 *    Alojamientos → Atributos
 *
 * Cambios importantes:
 * ----------------------------------------------------------
 * ✔ Nombre del CPT corregido: tureserva_atributo
 * ✔ Labels completos (mejor experiencia en admin)
 * ✔ Seguridad agregada
 * ✔ show_in_rest documentado (Gutenberg opcional)
 * ✔ Código estandarizado con el resto del plugin
 * ==========================================================
 */

if (!defined('ABSPATH')) exit; // Seguridad

// =======================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "Atributo"
// =======================================================
function tureserva_register_atributo_cpt()
{
    // -----------------------------------------------
    // 🏷️ Labels completos para una mejor UX en admin
    // -----------------------------------------------
    $labels = array(
        'name'               => __('Atributos', 'tureserva'),
        'singular_name'      => __('Atributo', 'tureserva'),
        'menu_name'          => __('Atributos', 'tureserva'),
        'add_new'            => __('Agregar nuevo', 'tureserva'),
        'add_new_item'       => __('Agregar nuevo atributo', 'tureserva'),
        'edit_item'          => __('Editar atributo', 'tureserva'),
        'new_item'           => __('Nuevo atributo', 'tureserva'),
        'view_item'          => __('Ver atributo', 'tureserva'),
        'search_items'       => __('Buscar atributos', 'tureserva'),
        'not_found'          => __('No se encontraron atributos', 'tureserva'),
        'not_found_in_trash' => __('No hay atributos en la papelera', 'tureserva'),
        'all_items'          => __('Todos los atributos', 'tureserva'),
    );

    // -----------------------------------------------
    // ⚙️ Configuración principal
    // -----------------------------------------------
    $args = array(
        'labels'             => $labels,
        'public'             => false,       // Solo panel admin
        'show_ui'            => true,        // Mostrar editor básico
        'show_in_menu'       => 'edit.php?post_type=trs_alojamiento',
        /**
         * Esto inserta el CPT dentro del menú:
         *
         * Alojamientos
         *    - Todos los alojamientos
         *    - Agregar nuevo
         *    - Atributos  ← aquí
         *
         * (Muy útil para mantener orden visual del módulo)
         */
        'menu_icon'          => 'dashicons-filter',
        'supports'           => array('title'),
        
        /**
         * Gutenberg / REST API:
         * Si NO necesitas editor de bloques para atributos,
         * se recomienda desactivarlo (show_in_rest = false)
         */
        'show_in_rest'       => false,

        'publicly_queryable' => false,
        'has_archive'        => false,
        'rewrite'            => false
    );

    // -----------------------------------------------
    // 🧩 Registro final del CPT corregido
    // -----------------------------------------------
    register_post_type('tureserva_atributo', $args);
}
add_action('init', 'tureserva_register_atributo_cpt');
