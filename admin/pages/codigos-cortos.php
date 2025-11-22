<?php
if (!defined('ABSPATH')) exit;

function tureserva_render_codigos_cortos_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Códigos Cortos (Shortcodes)', 'tureserva'); ?></h1>
        <p><?php _e('Aquí encontrarás la lista de shortcodes disponibles para usar en tu sitio.', 'tureserva'); ?></p>
        
        <div class="card">
            <h2><?php _e('Buscador de Disponibilidad', 'tureserva'); ?></h2>
            <p><code>[tureserva_buscar_disponibilidad]</code></p>
            <p><?php _e('Muestra el formulario de búsqueda de alojamientos.', 'tureserva'); ?></p>
        </div>

        <div class="card">
            <h2><?php _e('Listado de Alojamientos', 'tureserva'); ?></h2>
            <p><code>[tureserva_alojamientos]</code></p>
            <p><?php _e('Muestra una rejilla con todos los alojamientos.', 'tureserva'); ?></p>
        </div>

        <div class="card">
            <h2><?php _e('Detalle de Alojamiento', 'tureserva'); ?></h2>
            <p><code>[tureserva_alojamiento id="123"]</code></p>
            <p><?php _e('Muestra los detalles de un alojamiento específico.', 'tureserva'); ?></p>
        </div>
    </div>
    <?php
}
