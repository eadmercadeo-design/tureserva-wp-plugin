<?php
/**
 * ==========================================================
 * ADMIN: Añadir nueva reserva — TuReserva
 * ==========================================================
 * Interfaz mejorada basada en MotoPress.
 * Permite buscar alojamientos disponibles por fechas
 * y crear reservas manuales desde el panel.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔐 Verificación de permisos
// ==========================================================
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos para acceder a esta página.', 'tureserva'));
}

// ==========================================================
// 📦 Encolar estilos y scripts de la página
// ==========================================================
add_action('admin_enqueue_scripts', function ($hook) {
    // Carga siempre si la URL contiene el parámetro de nuestra página personalizada
    if (strpos($_SERVER['REQUEST_URI'], 'tureserva-add-reserva') === false) return;

    wp_enqueue_style(
        'tureserva-add-reserva',
        TURESERVA_URL . 'assets/css/admin-add-reserva.css?v=5',
        [],
        null
    );

    wp_enqueue_script(
        'tureserva-add-reserva',
        TURESERVA_URL . 'assets/js/admin-add-reserva.js?v=3',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('tureserva-add-reserva', 'TuReservaAddReserva', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_add_reserva_nonce'),
    ]);
});


// ==========================================================
// 🧭 Renderizado principal
// ==========================================================
?>
<div class="wrap tureserva-add-reserva">
    <h1><?php _e('Añadir nueva reserva', 'tureserva'); ?></h1>

    <form id="tureserva-buscar-form" class="tureserva-form">
        <p class="description">
            <?php _e('Complete los filtros para buscar alojamientos disponibles:', 'tureserva'); ?>
        </p>
        <hr style="margin: 20px 0;">

        <div class="tureserva-grid">
            <!-- Día de llegada -->
            <div class="tureserva-field">
                <label for="check_in">
                    <?php _e('Día de llegada', 'tureserva'); ?> <span class="required">*</span>
                </label>
                <input type="date" id="check_in" name="check_in" required>
            </div>

            <!-- Día de salida -->
            <div class="tureserva-field">
                <label for="check_out">
                    <?php _e('Día de salida', 'tureserva'); ?> <span class="required">*</span>
                </label>
                <input type="date" id="check_out" name="check_out" required>
            </div>

            <!-- Tipo de alojamiento -->
            <div class="tureserva-field">
                <label for="alojamiento_type"><?php _e('Tipo de alojamiento', 'tureserva'); ?></label>
                <select id="alojamiento_type" name="alojamiento_type">
                    <option value=""><?php _e('— Cualquiera —', 'tureserva'); ?></option>
                    <?php
                    $tipos = get_terms([
                        'taxonomy' => 'categoria_alojamiento',
                        'hide_empty' => false,
                    ]);
                    if (!is_wp_error($tipos) && !empty($tipos)) {
                        foreach ($tipos as $tipo) {
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr($tipo->term_id),
                                esc_html($tipo->name)
                            );
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Adultos -->
            <div class="tureserva-field">
                <label for="adults"><?php _e('Adultos', 'tureserva'); ?></label>
                <select id="adults" name="adults">
                    <option value=""><?php _e('— Cualquiera —', 'tureserva'); ?></option>
                    <?php for ($i = 1; $i <= 10; $i++) printf('<option value="%d">%d</option>', $i, $i); ?>
                </select>
            </div>

            <!-- Niños -->
            <div class="tureserva-field">
                <label for="children"><?php _e('Niños', 'tureserva'); ?></label>
                <select id="children" name="children">
                    <option value=""><?php _e('— Cualquiera —', 'tureserva'); ?></option>
                    <?php for ($i = 0; $i <= 10; $i++) printf('<option value="%d">%d</option>', $i, $i); ?>
                </select>
            </div>

            <!-- Acciones -->
            <div class="tureserva-actions">
                <button type="submit" class="button button-primary">
                    <?php _e('Buscar disponibilidad', 'tureserva'); ?>
                </button>
                <button type="reset" class="button">
                    <?php _e('Limpiar', 'tureserva'); ?>
                </button>
            </div>
        </div>
    </form>

    <div id="tureserva-resultados" class="tureserva-resultados">
        <p class="description"><?php _e('Los resultados aparecerán aquí...', 'tureserva'); ?></p>
    </div>
<style>
</style>
</div>
