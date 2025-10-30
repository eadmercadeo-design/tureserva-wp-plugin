<?php
/**
 * ==========================================================
 * SHORTCODE: Buscador de Alojamiento — TuReserva
 * ==========================================================
 * Muestra un buscador de fechas conectado a la API REST.
 * - Consulta disponibilidad
 * - Permite enviar solicitud de reserva
 * ==========================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =======================================================
// 🧱 REGISTRO DEL SHORTCODE
// =======================================================
add_shortcode( 'tureserva_buscador', 'tureserva_shortcode_buscador' );

function tureserva_shortcode_buscador() {
    ob_start();
    ?>
    <div id="tureserva-buscador" class="tureserva-buscador">
        <h3>🔎 Busca tu alojamiento ideal</h3>
        <form id="tureserva-form-buscador">
            <label>Check-in:</label>
            <input type="date" id="tureserva_check_in" required>

            <label>Check-out:</label>
            <input type="date" id="tureserva_check_out" required>

            <label>Adultos:</label>
            <input type="number" id="tureserva_adultos" value="2" min="1">

            <label>Niños:</label>
            <input type="number" id="tureserva_ninos" value="0" min="0">

            <button type="submit" id="tureserva_btn_buscar">Buscar disponibilidad</button>
        </form>

        <div id="tureserva-resultados" style="display:none;margin-top:20px;">
            <h4>Resultados</h4>
            <div id="tureserva-lista-alojamientos"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// =======================================================
// 📦 CARGAR SCRIPTS Y ESTILOS EN FRONTEND
// =======================================================
add_action( 'wp_enqueue_scripts', 'tureserva_buscador_assets' );

function tureserva_buscador_assets() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'tureserva-buscador-js', TURESERVA_URL . 'assets/js/tureserva-buscador.js', array( 'jquery' ), TURESERVA_VERSION, true );
    wp_localize_script( 'tureserva-buscador-js', 'tureservaBuscador', array(
        'api_url' => site_url( '/wp-json/tureserva/v1' ),
        'token'   => '', // opcional si usas auth pública o token global
    ));
    wp_enqueue_style( 'tureserva-buscador-css', TURESERVA_URL . 'assets/css/tureserva-buscador.css', array(), TURESERVA_VERSION );
}
