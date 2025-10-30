<?php
/**
 * Menús administrativos del módulo de Alojamiento
 * Incluye: Generar alojamientos, Ajustes, Idiomas y Códigos cortos
 */

if (!defined('ABSPATH')) exit;

// ============================
// Submenús del CPT Alojamiento
// ============================
if (!function_exists('tureserva_alojamiento_submenus')) {
    function tureserva_alojamiento_submenus() {

        $parent_slug = 'edit.php?post_type=alojamiento';

        // 🧮 Generar alojamientos
        add_submenu_page(
            $parent_slug,
            'Generar alojamientos',
            'Generar alojamientos',
            'manage_options',
            'tureserva-generar-alojamientos',
            'tureserva_generar_alojamientos_callback',
            10
        );

        // ⚙️ Ajustes generales
        add_submenu_page(
            $parent_slug,
            'Ajustes generales',
            'Ajustes',
            'manage_options',
            'tureserva-ajustes',
            'tureserva_ajustes_callback',
            20
        );

        // 🌐 Idiomas
        add_submenu_page(
            $parent_slug,
            'Idiomas del sistema',
            'Idiomas',
            'manage_options',
            'tureserva-idiomas',
            'tureserva_idiomas_callback',
            30
        );

        // 💬 Códigos cortos
        add_submenu_page(
            $parent_slug,
            'Códigos cortos',
            'Códigos cortos',
            'manage_options',
            'tureserva-shortcodes',
            'tureserva_shortcodes_callback',
            40
        );
    }
    add_action('admin_menu', 'tureserva_alojamiento_submenus', 30);
}

// ============================
// CALLBACKS DE CADA SUBMENÚ
// ============================

function tureserva_generar_alojamientos_callback() { ?>
    <div class="wrap">
        <h1>🧮 Generar alojamientos</h1>
        <p>Herramienta para crear múltiples alojamientos automáticamente según la cantidad definida en cada tipo.</p>
    </div>
<?php }

function tureserva_ajustes_callback() { ?>
    <div class="wrap">
        <h1>⚙️ Ajustes generales</h1>
        <p>Configura las opciones principales del sistema TuReserva: moneda, zona horaria, idioma por defecto, y API Keys.</p>
    </div>
<?php }

function tureserva_idiomas_callback() { ?>
    <div class="wrap">
        <h1>🌐 Idiomas</h1>
        <p>Administra los textos visibles al público, traducciones de botones, y mensajes automáticos.</p>
    </div>
<?php }

function tureserva_shortcodes_callback() { ?>
    <div class="wrap">
        <h1>💬 Códigos cortos disponibles</h1>
        <ul>
            <li><code>[tureserva_form]</code> → Formulario de reservas</li>
            <li><code>[tureserva_list]</code> → Listado de alojamientos</li>
            <li><code>[tureserva_calendar]</code> → Calendario de disponibilidad</li>
        </ul>
    </div>
<?php }
