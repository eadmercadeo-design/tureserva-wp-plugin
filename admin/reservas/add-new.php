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
    // Carga solo en la página correcta
    if (!isset($_GET['page']) || $_GET['page'] !== 'tureserva-add-reserva') return;

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

    <form id="tureserva-buscar-form" class="tureserva-form" method="post">
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
    <!-- Modal para datos del cliente -->
    <div id="tureserva-modal" class="tureserva-modal" style="display:none;">
        <div class="tureserva-modal-content">
            <span class="close-modal">&times;</span>
            <h2><?php _e('Finalizar Reserva', 'tureserva'); ?></h2>
            <form id="tureserva-crear-reserva-form">
                <input type="hidden" id="modal_alojamiento_id" name="alojamiento_id">
                
                <p>
                    <label><?php _e('Alojamiento:', 'tureserva'); ?></label>
                    <strong id="modal_alojamiento_nombre"></strong>
                </p>

                <div class="tureserva-field">
                    <label for="cliente_nombre"><?php _e('Nombre del Cliente', 'tureserva'); ?> *</label>
                    <input type="text" id="cliente_nombre" name="cliente_nombre" required>
                </div>

                <div class="tureserva-field">
                    <label for="cliente_email"><?php _e('Email del Cliente', 'tureserva'); ?> *</label>
                    <input type="email" id="cliente_email" name="cliente_email" required>
                </div>

                <div class="tureserva-field">
                    <label for="cliente_telefono"><?php _e('Teléfono', 'tureserva'); ?></label>
                    <input type="text" id="cliente_telefono" name="cliente_telefono">
                </div>

                <!-- Servicios Adicionales -->
                <div class="tureserva-field">
                    <label><?php _e('Servicios Adicionales', 'tureserva'); ?></label>
                    <div class="tureserva-modal-services" style="max-height:100px; overflow-y:auto; border:1px solid #ddd; padding:10px; border-radius:4px;">
                        <?php
                        $servicios = get_posts([
                            'post_type' => 'tureserva_servicio',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        ]);
                        if ($servicios) {
                            foreach ($servicios as $s) {
                                $precio = get_post_meta($s->ID, 'tureserva_precio', true);
                                $precio_txt = $precio ? " ($" . number_format($precio, 2) . ")" : '';
                                echo '<div style="margin-bottom:5px;">';
                                echo '<label><input type="checkbox" name="servicios[]" value="' . esc_attr($s->ID) . '"> ' . esc_html($s->post_title) . $precio_txt . '</label>';
                                echo '</div>';
                            }
                        } else {
                            echo '<small>No hay servicios disponibles.</small>';
                        }
                        ?>
                    </div>
                </div>

                <div class="tureserva-actions">
                    <button type="submit" class="button button-primary"><?php _e('Confirmar Reserva', 'tureserva'); ?></button>
                </div>
            </form>
        </div>
    </div>

<style>
.tureserva-modal {
    position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);
}
.tureserva-modal-content {
    background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; max-width: 500px; border-radius: 8px;
}
.close-modal {
    color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;
}
.close-modal:hover, .close-modal:focus {
    color: black; text-decoration: none; cursor: pointer;
}
</style>
</div>
