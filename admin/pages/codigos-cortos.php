<?php
if (!defined('ABSPATH')) exit;

function tureserva_render_codigos_cortos_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Códigos Cortos (Shortcodes)', 'tureserva'); ?></h1>
        <p><?php _e('Aquí encontrarás la lista de shortcodes disponibles para usar en tu sitio.', 'tureserva'); ?></p>
        
        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
            <h2><?php _e('1. Buscador de Alojamiento (Widget)', 'tureserva'); ?></h2>
            <p><code>[tureserva_buscador]</code></p>
            <p><?php _e('Muestra un pequeño formulario de búsqueda (Check-in, Check-out, Adultos, Niños). Ideal para colocar en la página de inicio o en un sidebar.', 'tureserva'); ?></p>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
            <h2><?php _e('2. Página Completa de Búsqueda', 'tureserva'); ?></h2>
            <p><code>[tureserva_search_page]</code></p>
            <p><?php _e('Renderiza la interfaz completa de búsqueda de disponibilidad con filtros y resultados. Se recomienda usar este shortcode en una página dedicada (ej: /buscar-disponibilidad).', 'tureserva'); ?></p>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
            <h2><?php _e('3. Formulario de Pago (Stripe)', 'tureserva'); ?></h2>
            <p><code>[tureserva_pago]</code></p>
            <p><?php _e('Muestra el formulario de pago seguro con tarjeta de crédito conectado a Stripe.', 'tureserva'); ?></p>
            
            <h3><?php _e('Parámetros:', 'tureserva'); ?></h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>reserva_id:</strong> <?php _e('ID de la reserva asociada.', 'tureserva'); ?></li>
                <li><strong>monto:</strong> <?php _e('Monto total a cobrar.', 'tureserva'); ?></li>
                <li><strong>moneda:</strong> <?php _e('Código de la moneda (ej: usd, eur).', 'tureserva'); ?></li>
            </ul>

            <h3><?php _e('Ejemplo de uso:', 'tureserva'); ?></h3>
            <p><code>[tureserva_pago reserva_id="123" monto="150.00" moneda="usd"]</code></p>
        </div>
    </div>
    <?php
}
