<?php
/**
 * ==========================================================
 * CPT: Alojamiento — TuReserva (VERSIÓN CORREGIDA)
 * ==========================================================
 * Este archivo registra el Custom Post Type de Alojamientos.
 *
 * 🔥 ATENCIÓN:
 * El antiguo nombre "tureserva_alojamiento" tenía 21 caracteres
 * y WordPress SOLO permite hasta 20. Eso generaba:
 *
 *  ❌ Errores diarios en debug.log
 *  ❌ Fallos al cargar el plugin
 *  ❌ Menús que desaparecían
 *  ❌ Hooks que no se ejecutaban
 *
 * Ahora se usa:
 *
 *      ✓ trs_alojamiento   (15 caracteres → VÁLIDO)
 *
 * IMPORTANTE:
 * Si ya tenías alojamientos creados, necesitarás migrarlos
 * al nuevo post_type (puedo darte el script si lo necesitas).
 * ==========================================================
 */

if (!defined('ABSPATH')) exit; // Seguridad básica

// =======================================================
// 🔧 REGISTRO DEL CUSTOM POST TYPE "Alojamiento"
// =======================================================
function tureserva_register_alojamiento_cpt()
{
    // ---------------------------------------------------
    // 🏷️ Labels visibles en WordPress
    // ---------------------------------------------------
    $labels = array(
        'name'               => __('Alojamientos', 'tureserva'),
        'singular_name'      => __('Alojamiento', 'tureserva'),
        'menu_name'          => __('Alojamiento', 'tureserva'),
        'name_admin_bar'     => __('Alojamiento', 'tureserva'),

        'add_new'            => __('Agregar nuevo', 'tureserva'),
        'add_new_item'       => __('Agregar nuevo alojamiento', 'tureserva'),

        'edit_item'          => __('Editar alojamiento', 'tureserva'),
        'new_item'           => __('Nuevo alojamiento', 'tureserva'),

        'view_item'          => __('Ver alojamiento', 'tureserva'),
        'search_items'       => __('Buscar alojamientos', 'tureserva'),

        'not_found'          => __('No se encontraron alojamientos', 'tureserva'),
        'not_found_in_trash' => __('No hay alojamientos en la papelera', 'tureserva'),

        'all_items'          => __('Todos los alojamientos', 'tureserva'),
    );

    // ---------------------------------------------------
    // ⚙️ Configuración del CPT
    // ---------------------------------------------------
    $args = array(
        'labels'             => $labels,

        // No aparece en frontend
        'public'             => false,

        // Sí aparece en el panel admin (WP-Admin)
        'show_ui'            => true,

        /**
         * Desactivamos su propio menú porque el menú REAL
         * se construye manualmente desde:
         *    includes/menu-alojamiento.php
         */
        'show_in_menu'       => false,

        // Ícono sugerido
        'menu_icon'          => 'dashicons-building',

        // Soportes del editor que necesitas
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),

        // No queremos archivo ni URLs públicas
        'has_archive'        => false,
        'rewrite'            => false,

        // Desactivar Gutenberg para evitar conflictos
        'show_in_rest'       => false,

        // Solo se usa internamente
        'publicly_queryable' => false,

        'capability_type'    => 'post',
    );

    // ---------------------------------------------------
    // 📝 REGISTRO DEL CPT — *versión válida*
    // ---------------------------------------------------
    register_post_type(
        'trs_alojamiento',   // ← Nombre corregido (máx 20 chars)
        $args
    );
}
add_action('init', 'tureserva_register_alojamiento_cpt');
