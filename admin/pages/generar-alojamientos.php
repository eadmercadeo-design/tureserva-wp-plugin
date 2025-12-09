<?php
/**
 * ==========================================================
 * ADMIN PAGE: Generar Alojamientos — TuReserva
 * ==========================================================
 * Crea múltiples alojamientos automáticamente clonando uno existente.
 * Copia los metadatos (precio, capacidad, galería, etc.)
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
        echo '<p>' . esc_html__('Herramienta para clonar alojamientos existentes y crear múltiples copias rápidamente.', 'tureserva') . '</p>';

        // ✅ Procesar formulario
        if (isset($_POST['tureserva_generar_nonce']) && wp_verify_nonce($_POST['tureserva_generar_nonce'], 'tureserva_generar_action')) {
            $cantidad = intval($_POST['cantidad']);
            $origen_id  = intval($_POST['origen_alojamiento']);
            $titulo   = sanitize_text_field($_POST['titulo']);
            tureserva_generar_alojamientos($cantidad, $origen_id, $titulo);
        }

        // Obtener alojamientos existentes para usar como plantilla
        $alojamientos = get_posts([
            'post_type'   => TURESERVA_CPT_ALOJAMIENTO,
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
                        <th><label for="cantidad"><?php _e('Número de copias', 'tureserva'); ?></label></th>
                        <td>
                            <input type="number" id="cantidad" name="cantidad" value="1" min="1" class="small-text">
                            <p class="description"><?php _e('Cantidad de nuevos alojamientos a crear.', 'tureserva'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="origen_alojamiento"><?php _e('Alojamiento base (Plantilla)', 'tureserva'); ?></label></th>
                        <td>
                            <select id="origen_alojamiento" name="origen_alojamiento" required>
                                <option value=""><?php _e('— Seleccione un alojamiento —', 'tureserva'); ?></option>
                                <?php foreach ($alojamientos as $aloj) : ?>
                                    <option value="<?php echo esc_attr($aloj->ID); ?>"><?php echo esc_html($aloj->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Se copiarán todos los datos (precios, capacidad, fotos) de este alojamiento.', 'tureserva'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="titulo"><?php _e('Nombre base (opcional)', 'tureserva'); ?></label></th>
                        <td>
                            <input type="text" id="titulo" name="titulo" class="regular-text">
                            <p class="description"><?php _e('Si se deja vacío, usará el nombre del original. Se agregará #1, #2 al final.', 'tureserva'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Generar copias', 'tureserva')); ?>
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

    function tureserva_generar_alojamientos($cantidad, $origen_id, $titulo_base = '') {
        $origen = get_post($origen_id);
        if (!$origen) {
            echo '<div class="error"><p>' . __('Alojamiento base no encontrado.', 'tureserva') . '</p></div>';
            return;
        }

        $titulo_base = $titulo_base ?: $origen->post_title;

        // Obtener todos los metadatos del original
        $meta = get_post_meta($origen_id);

        $nuevos = [];

        for ($i = 1; $i <= $cantidad; $i++) {
            $nuevo_titulo = "{$titulo_base} #{$i}";

            $nuevo_id = wp_insert_post([
                'post_type'   => TURESERVA_CPT_ALOJAMIENTO,
                'post_title'  => $nuevo_titulo,
                'post_status' => 'publish',
                'post_content'=> $origen->post_content,
                'post_excerpt'=> $origen->post_excerpt,
            ]);

            if ($nuevo_id && !is_wp_error($nuevo_id)) {
                // Clonar metadatos
                foreach ($meta as $key => $value) {
                    // Evitar duplicar metadatos internos de WP que no deberían copiarse ciegamente si existieran
                    if ( in_array($key, ['_edit_lock', '_edit_last']) ) continue;

                    if (is_serialized($value[0])) {
                        update_post_meta($nuevo_id, $key, maybe_unserialize($value[0]));
                    } else {
                        update_post_meta($nuevo_id, $key, $value[0]);
                    }
                }

                // Guardar referencia al padre (opcional, por si sirve de algo)
                update_post_meta($nuevo_id, '_tureserva_clonado_de', $origen_id);

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

