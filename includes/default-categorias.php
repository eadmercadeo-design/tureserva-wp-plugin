<?php
// Crear categorías predeterminadas de alojamiento
function tureserva_insert_default_categorias() {
    $categorias = [
        ['Cabaña', 'cabana', 'Alojamiento rústico construido en madera, ideal para familias o parejas.'],
        ['Glamping', 'glamping', 'Experiencia de camping con lujo, comodidad y diseño exclusivo.'],
        ['Habitación Estándar', 'habitacion-estandar', 'Opción básica con todas las comodidades esenciales.'],
        ['Suite Premium', 'suite-premium', 'Espacio de lujo con servicios adicionales y vista panorámica.'],
        ['Apartamento Familiar', 'apartamento-familiar', 'Unidad espaciosa ideal para grupos o familias numerosas.'],
        ['Villa Privada', 'villa-privada', 'Alojamiento exclusivo con piscina, cocina y áreas privadas.'],
        ['Bungalow', 'bungalow', 'Alojamiento independiente rodeado de naturaleza.'],
        ['Casa de Campo', 'casa-de-campo', 'Espacio amplio con estilo tradicional y ambiente rural.'],
        ['Eco Lodge', 'eco-lodge', 'Alojamiento ecológico diseñado para la sostenibilidad.'],
        ['Hostel Compartido', 'hostel-compartido', 'Espacio compartido ideal para viajeros y mochileros.']
    ];

    foreach ($categorias as $cat) {
        if (!term_exists($cat[0], 'categoria_alojamiento')) {
            wp_insert_term($cat[0], 'categoria_alojamiento', [
                'slug' => $cat[1],
                'description' => $cat[2]
            ]);
        }
    }
}
