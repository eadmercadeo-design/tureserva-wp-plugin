<?php
/**
 * Admin Design: Servicios de Alojamiento (CPT)
 * - Servicios predefinidos
 * - Iconos SVG
 * - Estilo "Card" en la tabla de posts
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 1. 🛠️ CONFIGURACIÓN DE SERVICIOS Y ICONOS
// ==========================================================
function tureserva_get_default_services() {
    return [
        'wifi-gratis' => [
            'title' => 'Wifi Gratis',
            'desc' => 'Conexión a internet de alta velocidad.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>'
        ],
        'aire-acondicionado' => [
            'title' => 'Aire Acondicionado',
            'desc' => 'Control de clima en la habitación.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/><path d="M12 2v4"/><path d="M12 18v4"/><path d="M4 12H2"/><path d="M22 12h-2"/><path d="M19.07 4.93l-1.41 1.41"/><path d="M6.34 17.66l-1.41 1.41"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/></svg>'
        ],
        'jacuzzi' => [
            'title' => 'Jacuzzi / Hidromasaje',
            'desc' => 'Tina de hidromasaje privada o compartida.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M2 12h20"/><path d="M7 4v4"/><path d="M12 4v4"/><path d="M17 4v4"/></svg>'
        ],
        'piscina' => [
            'title' => 'Piscina',
            'desc' => 'Acceso a piscina.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M2 16c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M12 2v8"/><path d="M8 6h8"/></svg>'
        ],
        'tv-pantalla-plana' => [
            'title' => 'TV Pantalla Plana',
            'desc' => 'Televisión con canales por cable/satélite.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>'
        ],
        'desayuno-incluido' => [
            'title' => 'Desayuno Incluido',
            'desc' => 'Desayuno continental o buffet diario.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 6 0v-4"/><path d="M10 12a4 4 0 0 1 6 0"/><path d="M16 8h-6a2 2 0 0 1-2-2V3h10v3a2 2 0 0 1-2 2z"/><path d="M2 21h20"/></svg>'
        ],
        'parqueadero-gratis' => [
            'title' => 'Parqueadero Gratis',
            'desc' => 'Estacionamiento privado sin costo.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M7 15h2"/><path d="M15 15h2"/></svg>'
        ],
        'cafetera' => [
            'title' => 'Cafetera',
            'desc' => 'Máquina de café en la habitación.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>'
        ],
        'caja-fuerte' => [
            'title' => 'Caja Fuerte',
            'desc' => 'Caja de seguridad para objetos de valor.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="8" x2="12" y2="9"/><line x1="12" y1="15" x2="12" y2="16"/><line x1="15.5" y1="12" x2="16.5" y2="12"/><line x1="7.5" y1="12" x2="8.5" y2="12"/></svg>'
        ],
        'pet-friendly' => [
            'title' => 'Pet Friendly',
            'desc' => 'Se admiten mascotas bajo petición.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 14c2-2 2-5-2-5s-4 3-2 5"/><path d="M9 10c-2-2-5-2-5 2s4 4 5 2"/><path d="M15 10c2-2 5-2 5 2s-4 4-5 2"/><path d="M12 18c-2 2-5 2-5-2s3-4 5-2"/><path d="M12 18c2 2 5 2 5-2s-3-4-5-2"/></svg>'
        ],
        'calefaccion' => [
            'title' => 'Calefacción',
            'desc' => 'Sistema de calefacción central o individual.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 5 5v3a5 5 0 0 1-10 0V7a5 5 0 0 1 5-5z"/><path d="M8 16a4 4 0 0 0 8 0"/><path d="M12 12v8"/></svg>'
        ],
        'vista-panoramica' => [
            'title' => 'Vista Panorámica',
            'desc' => 'Vistas espectaculares desde la habitación.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18L14.5 4 10 14l-2.5-4L3 20z"/></svg>'
        ],
        'servicio-habitacion' => [
            'title' => 'Servicio a la Habitación',
            'desc' => 'Atención de alimentos y bebidas en la habitación.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h16"/><path d="M2 14h20"/><path d="M6 14v6"/><path d="M18 14v6"/><path d="M12 4v6"/></svg>'
        ],
        'kit-bano' => [
            'title' => 'Kit de Baño',
            'desc' => 'Artículos de aseo personal incluidos.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 10v8a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-8"/><path d="M7 10a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2"/><path d="M12 2v6"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>'
        ],
        'check-in-24h' => [
            'title' => 'Check-in 24h',
            'desc' => 'Recepción disponible las 24 horas.',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
        ]
    ];
}

// ==========================================================
// 2. 🚀 INICIALIZAR SERVICIOS (CPT) POR DEFECTO
// ==========================================================
function tureserva_init_default_services() {
    $defaults = tureserva_get_default_services();
    
    foreach ($defaults as $slug => $data) {
        // Verificar si existe por slug
        $existing = get_page_by_path($slug, OBJECT, 'tureserva_servicio');
        
        if (!$existing) {
            wp_insert_post([
                'post_title'    => $data['title'],
                'post_content'  => $data['desc'],
                'post_status'   => 'publish',
                'post_type'     => 'tureserva_servicio',
                'post_name'     => $slug
            ]);
        }
    }
}
add_action('admin_init', 'tureserva_init_default_services');

// ==========================================================
// 3. 🎨 PERSONALIZAR COLUMNAS (Añadir Icono)
// ==========================================================
add_filter('manage_tureserva_servicio_posts_columns', function($columns) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb'];
    $new_columns['icon'] = ''; // Columna de icono
    $new_columns['title'] = $columns['title'];
    $new_columns['date'] = $columns['date'];
    return $new_columns;
});

add_action('manage_tureserva_servicio_posts_custom_column', function($column, $post_id) {
    if ($column !== 'icon') return;

    $post = get_post($post_id);
    $defaults = tureserva_get_default_services();
    $slug = $post->post_name;
    
    // Buscar icono por slug
    if (isset($defaults[$slug])) {
        echo '<div class="tureserva-srv-icon">' . $defaults[$slug]['icon'] . '</div>';
    } else {
        echo '<div class="tureserva-srv-icon"><span class="dashicons dashicons-hammer"></span></div>';
    }
}, 10, 2);

// ==========================================================
// 4. 💅 CSS PARA ESTILO "CARD" EN EDIT.PHP
// ==========================================================
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'tureserva_servicio') return;
    ?>
    <style>
        /* Ocultar elementos innecesarios */
        .tablenav.top, .subsubsub { display: none; }
        
        /* Tabla como Grid */
        .wp-list-table.posts {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .wp-list-table.posts thead, 
        .wp-list-table.posts tfoot {
            display: none;
        }

        .wp-list-table.posts tbody {
            display: contents;
        }

        /* Estilo de la "Card" (TR) */
        .wp-list-table.posts tr {
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

        .wp-list-table.posts tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-color: #2271b1;
        }

        /* Checkbox */
        .wp-list-table.posts th.check-column {
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

        .tureserva-srv-icon {
            width: 48px;
            height: 48px;
            background: #f0f6fc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2271b1;
        }
        
        .tureserva-srv-icon svg {
            width: 24px;
            height: 24px;
        }

        /* Título */
        .column-title {
            width: 100% !important;
            padding: 0 !important;
            margin-bottom: 5px;
        }

        .column-title strong {
            display: block;
            font-size: 16px;
            color: #1d2327;
            margin-bottom: 5px;
        }

        .row-actions {
            position: static;
            margin-top: 10px;
            visibility: visible;
            font-size: 12px;
        }

        /* Fecha (Ocultar o mostrar discreta) */
        .column-date {
            display: none !important;
        }
        
    </style>
    <?php
});
