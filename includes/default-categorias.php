<?php
/**
 * ==========================================================
 * CATEGORÍAS PREDETERMINADAS — TuReserva
 * ==========================================================
 * Inserta categorías por defecto para la taxonomía
 * categoria_alojamiento.
 *
 * Cambios:
 * ✔ Validación de taxonomía existente
 * ✔ Verificación por SLUG (correcto)
 * ✔ Idempotente (no duplica)
 * ✔ Registra categorías avanzadas
 * ✔ Preparado para ejecución en activación del plugin
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🏷️ CREAR CATEGORÍAS PREDETERMINADAS DE ALOJAMIENTO
// ==========================================================
function tureserva_insert_default_categorias() {

    // Evitar ejecución si la taxonomía aún no existe
    if (!taxonomy_exists('categoria_alojamiento')) {
        error_log('⚠️ TuReserva: categoria_alojamiento no existe aún. No se insertaron categorías.');
        return;
    }

    // Lista de categorías por defecto
    $categorias = [
        ['Cabaña',               'cabana',              'Alojamiento rústico construido en madera, ideal para familias o parejas.'],
        ['Glamping',             'glamping',            'Experiencia de camping con lujo, comodidad y diseño exclusivo.'],
        ['Habitación Estándar',  'habitacion-estandar', 'Opción básica con todas las comodidades esenciales.'],
        ['Suite Premium',        'suite-premium',       'Espacio de lujo con servicios adicionales y vista panorámica.'],
        ['Apartamento Familiar', 'apartamento-familiar','Unidad espaciosa ideal para grupos o familias numerosas.'],
        ['Villa Privada',        'villa-privada',       'Alojamiento exclusivo con piscina, cocina y áreas privadas.'],
        ['Bungalow',             'bungalow',            'Alojamiento independiente rodeado de naturaleza.'],
        ['Casa de Campo',        'casa-de-campo',       'Espacio amplio con estilo tradicional y ambiente rural.'],
        ['Eco Lodge',            'eco-lodge',           'Alojamiento ecológico diseñado para la sostenibilidad.'],
        ['Hostel Compartido',    'hostel-compartido',   'Espacio compartido ideal para viajeros y mochileros.'],
    ];

    // Inserción segura
    foreach ($categorias as $cat) {

        list($nombre, $slug, $descripcion) = $cat;

        if (!term_exists($slug, 'categoria_alojamiento')) {

            $resultado = wp_insert_term($nombre, 'categoria_alojamiento', [
                'slug'        => sanitize_title($slug),
                'description' => sanitize_textarea_field($descripcion)
            ]);

            if (is_wp_error($resultado)) {
                error_log('❌ Error insertando categoría "' . $nombre . '": ' . $resultado->get_error_message());
            }
        }
    }

    error_log('✔ TuReserva: categorías predeterminadas insertadas correctamente.');
}


// ==========================================================
// 🧩 HOOK PARA EJECUTAR ESTA FUNCIÓN SOLO AL ACTIVAR EL PLUGIN
// ==========================================================
register_activation_hook( TURESERVA_MAIN_FILE, 'tureserva_insert_default_categorias' );