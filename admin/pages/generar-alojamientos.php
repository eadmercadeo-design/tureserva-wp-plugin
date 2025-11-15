<?php
/**
 * ==========================================================
 * ADMIN PAGE: Generar Alojamientos — TuReserva
 * ==========================================================
 * Crea múltiples alojamientos automáticamente según un tipo base.
 * Copia los metadatos del tipo (precio, capacidad, galería, etc.)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🧾 Renderizado de la página
// =======================================================
if ( ! function_exists( 'tureserva_render_generar_alojamientos_page' ) ) {

    function tureserva_render_generar_alojamientos_page() {

        echo '<div class="wrap">';
        echo '<h1><span class="dashicons dashicons-admin-home" style="color:#2271b1;margin-right:8px;"></span>' . esc_html__('Generar alojamientos', 'tureserva') . '</h1>';
        echo '<p>' . esc_html__('Herramienta para crear múltiples alojamientos automáticamente según la cantidad definida en cada tipo.', 'tureserva') . '</p>';

        // ✅ Procesar formulario
        if (isset($_POST['tureserva_generar_nonce']) && wp_verify_nonce($_POST['tureserva_generar_nonce'], 'tureserva_generar_action')) {
            $cantidad = intval($_POST['cantidad']);
            $tipo_id  = intval($_POST['tipo_alojamiento']);
            $titulo   = sanitize_text_field($_POST['titulo']);
            tureserva_generar_alojamientos($cantidad, $tipo_id, $titulo);
        }

        // Obtener tipos de alojamiento publicados
        $tipos = get_posts([
            'post_type'   => 'tipo_alojamiento',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby'     => 'title',
            'order'       => 'ASC'
        ]);

        ?>
        <style>
            .tureserva-card {
                background: #fff;
                padding: 20px;
                margin-top: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                max-width: 600px;
            }
            .tureserva-card h2 {
                margin-bottom: 15px;
                font-size: 1.2em;
                color: #1d2327;
            }
            .tureserva-card table th {
                width: 200px;
            }
        </style>

        <div class="tureserva-card">
            <form method="post">
                <?php wp_nonce_field('tureserva_generar_action', 'tureserva_generar_nonce'); ?>

                <h2><?php _e('Configuración de generación', 'tureserva'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><label for="cantidad"><?php _e('Número de alojamientos', 'tureserva'); ?></label></th>
                        <td>
                            <input type="number" id="cantidad" name="cantidad" value="1" min="1" class="small-text">
                            <p class="description"><?php _e('Cantidad de alojamientos reales que desea crear.', 'tureserva'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="tipo_alojamiento"><?php _e('Tipo de alojamiento base', 'tureserva'); ?></label></th>
                        <td>
                            <select id="tipo_alojamiento" name="tipo_alojamiento" required>
                                <option value=""><?php _e('— Seleccione un tipo —', 'tureserva'); ?></option>
                                <?php foreach ($tipos as $tipo) : ?>
                                    <option value="<?php echo esc_attr($tipo->ID); ?>"><?php echo esc_html($tipo->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Los metadatos y precios del tipo seleccionado se copiarán a cada alojamiento nuevo.', 'tureserva'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="titulo"><?php _e('Prefijo de título (opcional)', 'tureserva'); ?></label></th>
                        <td>
                            <input type="text" id="titulo" name="titulo" class="regular-text">
                            <p class="description"><?php _e('Ejemplo: “Cabaña Mirador” generará “Cabaña Mirador #1”, “Cabaña Mirador #2”…', 'tureserva'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Generar alojamientos', 'tureserva')); ?>
            </form>
        </div>

        </div>
        <?php
    }
}

// =======================================================
// ⚙️ Función generadora
// =======================================================
if ( ! function_exists( 'tureserva_generar_alojamientos' ) ) {

    function tureserva_generar_alojamientos($cantidad, $tipo_id, $titulo_base = '') {
        $tipo = get_post($tipo_id);
        if (!$tipo) {
            echo '<div class="error"><p>' . __('Tipo de alojamiento no encontrado.', 'tureserva') . '</p></div>';
            return;
        }

        $titulo_base = $titulo_base ?: $tipo->post_title;

        // Obtener todos los metadatos del tipo base
        $meta = get_post_meta($tipo_id);

        $nuevos = [];

        for ($i = 1; $i <= $cantidad; $i++) {
            $nuevo_titulo = "{$titulo_base} #{$i}";

            $nuevo_id = wp_insert_post([
                'post_type'   => 'alojamiento',
                'post_title'  => $nuevo_titulo,
                'post_status' => 'publish',
            ]);

            if ($nuevo_id && !is_wp_error($nuevo_id)) {
                // Clonar metadatos
                foreach ($meta as $key => $value) {
                    if (is_serialized($value[0])) {
                        update_post_meta($nuevo_id, $key, maybe_unserialize($value[0]));
                    } else {
                        update_post_meta($nuevo_id, $key, $value[0]);
                    }
                }

                // Guardar relación con tipo
                update_post_meta($nuevo_id, '_tureserva_tipo_alojamiento', $tipo_id);

                $nuevos[] = $nuevo_id;
            }
        }

        // Mensaje de éxito
        echo '<div class="updated notice"><p>' . sprintf(__('✅ Se generaron %d alojamientos correctamente.', 'tureserva'), count($nuevos)) . '</p>';
        if (!empty($nuevos)) {
            echo '<ul>';
            foreach ($nuevos as $id) {
                echo '<li><a href="' . get_edit_post_link($id) . '" target="_blank">' . get_the_title($id) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }
}

