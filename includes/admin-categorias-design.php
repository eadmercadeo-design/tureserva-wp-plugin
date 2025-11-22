<?php
/**
 * Admin Design: Categorías de Alojamiento
 * - Categorías predefinidas
 * - Iconos SVG
 * - Estilo "Card" en la tabla
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 1. 🛠️ CONFIGURACIÓN DE CATEGORÍAS Y ICONOS
// ==========================================================
function tureserva_get_default_categories() {
    return [
        'cabana' => [
            'name' => 'Cabaña',
            'desc' => 'Alojamientos rústicos en madera.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 10a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v11H9V10z"/></svg>'
        ],
        'glamping' => [
            'name' => 'Glamping',
            'desc' => 'Camping con comodidades de lujo.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L2 21h20L12 3z"/><path d="M12 10v11"/></svg>'
        ],
        'suite' => [
            'name' => 'Suite',
            'desc' => 'Habitaciones de lujo y espacio extra.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>'
        ],
        'habitacion-doble' => [
            'name' => 'Habitación Doble',
            'desc' => 'Ideal para parejas.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M12 4v6"/><path d="M2 18h20"/></svg>'
        ],
        'habitacion-familiar' => [
            'name' => 'Habitación Familiar',
            'desc' => 'Espacio para toda la familia.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="5" r="2"/><path d="M9 19H5v-2a3 3 0 0 1 6 0v2"/><circle cx="17" cy="5" r="2"/><path d="M19 19h-4v-2a3 3 0 0 1 6 0v2"/></svg>'
        ],
        'bungalow' => [
            'name' => 'Bungalow',
            'desc' => 'Casa pequeña de una planta.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20"/><path d="M12 2L2 12h3v8h6v-6h2v6h6v-8h3L12 2z"/></svg>'
        ],
        'camping' => [
            'name' => 'Carpa / Camping',
            'desc' => 'Zona para acampar al aire libre.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21l8-16 8 16H4z"/><path d="M12 5v16"/></svg>'
        ],
        'apartamento' => [
            'name' => 'Apartamento Turístico',
            'desc' => 'Alojamiento completo con cocina.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>'
        ]
    ];
}

// ==========================================================
// 2. 🚀 INICIALIZAR CATEGORÍAS POR DEFECTO
// ==========================================================
function tureserva_init_default_categories() {
    $defaults = tureserva_get_default_categories();
    
    foreach ($defaults as $slug => $data) {
        if (!term_exists($slug, 'categoria_alojamiento')) {
            wp_insert_term($data['name'], 'categoria_alojamiento', [
                'slug' => $slug,
                'description' => $data['desc']
            ]);
        }
    }
}
add_action('admin_init', 'tureserva_init_default_categories');

// ==========================================================
// 3. 🎨 PERSONALIZAR COLUMNAS (Añadir Icono)
// ==========================================================
add_filter('manage_edit-categoria_alojamiento_columns', function($columns) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb'];
    $new_columns['icon'] = ''; // Columna de icono vacía (solo visual)
    $new_columns['name'] = $columns['name'];
    $new_columns['description'] = $columns['description'];
    $new_columns['slug'] = $columns['slug'];
    $new_columns['posts'] = $columns['posts'];
    return $new_columns;
});

add_action('manage_categoria_alojamiento_custom_column', function($content, $column_name, $term_id) {
    if ($column_name !== 'icon') return $content;

    $term = get_term($term_id);
    $defaults = tureserva_get_default_categories();
    
    // Buscar icono por slug
    if (isset($defaults[$term->slug])) {
        return '<div class="tureserva-cat-icon">' . $defaults[$term->slug]['icon'] . '</div>';
    }
    
    // Icono por defecto
    return '<div class="tureserva-cat-icon"><span class="dashicons dashicons-admin-home"></span></div>';
}, 10, 3);

// ==========================================================
// 4. 💅 CSS PARA ESTILO "CARD"
// ==========================================================
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'categoria_alojamiento') return;
    ?>
    <style>
        /* Ocultar elementos innecesarios */
        .tablenav.top, .subsubsub { display: none; }
        
        /* Tabla como Grid */
        .wp-list-table.tags {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .wp-list-table.tags thead, 
        .wp-list-table.tags tfoot {
            display: none;
        }

        .wp-list-table.tags tbody {
            display: contents; /* Permite que los TR sean hijos directos del Grid */
        }

        /* Estilo de la "Card" (TR) */
        .wp-list-table.tags tr {
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            box-sizing: border-box;
        }

        .wp-list-table.tags tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-color: #2271b1;
        }

        /* Checkbox (oculto o posicionado) */
        .wp-list-table.tags th.check-column {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0;
            height: auto;
            width: auto;
        }

        /* Icono */
        .column-icon {
            width: 100% !important;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .tureserva-cat-icon {
            width: 48px;
            height: 48px;
            background: #f0f6fc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2271b1;
        }
        
        .tureserva-cat-icon svg {
            width: 24px;
            height: 24px;
        }

        /* Nombre */
        .column-name {
            width: 100% !important;
            padding: 0 !important;
            margin-bottom: 5px;
        }

        .column-name strong {
            font-size: 16px;
            color: #1d2327;
        }

        .row-actions {
            position: static;
            margin-top: 10px;
            visibility: visible;
            font-size: 12px;
        }

        /* Descripción */
        .column-description {
            display: block !important;
            padding: 0 !important;
            font-size: 13px;
            color: #646970;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        /* Slug */
        .column-slug {
            display: block !important;
            padding: 0 !important;
            font-family: monospace;
            font-size: 11px;
            color: #a7aaad;
            background: #f6f7f7;
            padding: 2px 6px !important;
            border-radius: 3px;
            align-self: flex-start;
            margin-bottom: 10px;
        }

        /* Posts Count */
        .column-posts {
            position: absolute;
            top: 20px;
            right: 20px;
            width: auto !important;
            padding: 0 !important;
            font-size: 18px;
            font-weight: bold;
            color: #d63638; /* Color destacado */
        }

        /* Ajustes Mobile */
        @media (max-width: 782px) {
            .wp-list-table.tags {
                display: block;
            }
            .wp-list-table.tags tr {
                margin-bottom: 15px;
            }
        }
    </style>
    <?php
});
