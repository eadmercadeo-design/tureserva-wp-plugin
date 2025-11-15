<?php
/**
 * ==========================================================
 * ADMIN PAGE: Generar Alojamientos — TuReserva
 * ==========================================================
 * NOTA IMPORTANTE:
 * Este archivo SOLO contiene:
 *  ✔ La pantalla de administración
 *  ✔ La lógica para generar alojamientos
 *
 * El submenú que apunta a esta pantalla se registra EXCLUSIVAMENTE 
 * en /includes/menu-alojamiento.php para evitar duplicados.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🖥 PANTALLA DE ADMINISTRACIÓN: Generar Alojamientos
// ==========================================================
function tureserva_render_generar_alojamientos_page()
{
    echo '<div class="wrap">';
    echo '<h1>' . __('Generar alojamientos', 'tureserva') . '</h1>';

    echo '<p style="max-width:700px;">Este proceso crea o regenera alojamientos físicos según 
          el número definido en cada <strong>tipo de alojamiento</strong>. 
          Si ya existen, puedes optar por regenerarlos para ajustar cantidades 
          o eliminar los sobrantes.</p>';

    // Procesar envío del formulario
    if (isset($_POST['tureserva_generar'])) {

        $regenerar = isset($_POST['tureserva_regenerar']);
        $resultado = tureserva_run_alojamientos_generator($regenerar);

        echo '<div class="notice notice-success"><p><strong>Resultado:</strong> ' 
            . esc_html($resultado) . '</p></div>';
    }

    // Formulario
    echo '<form method="post" style="margin-top:20px;">';
    echo '<label><input type="checkbox" name="tureserva_regenerar" value="1"> 
          Regenerar alojamientos existentes (borra y recrea según el número actual)</label><br><br>';

    submit_button('Generar alojamientos', 'primary', 'tureserva_generar');

    echo '</form>';
    echo '</div>';
}


// ==========================================================
// 🧠 LÓGICA PRINCIPAL: Generar o Regenerar Alojamientos
// ==========================================================
function tureserva_run_alojamientos_generator($regenerar = false)
{
    // Obtener tipos de alojamiento
    $tipos = get_terms([
        'taxonomy'   => 'tipo_alojamiento',
        'hide_empty' => false,
    ]);

    if (empty($tipos)) {
        return 'No hay tipos de alojamiento definidos.';
    }

    $creados = 0;
    $eliminados = 0;

    foreach ($tipos as $tipo) {

        // Cuántos alojamientos debe tener este tipo
        $num_alojamientos = (int) get_term_meta($tipo->term_id, 'tureserva_numero_alojamientos', true);

        if ($num_alojamientos < 1) continue;

        // Obtener alojamientos existentes del tipo
        $actuales = get_posts([
            'post_type'   => 'tureserva_alojamiento',
            'numberposts' => -1,
            'tax_query'   => [[
                'taxonomy' => 'tipo_alojamiento',
                'field'    => 'term_id',
                'terms'    => $tipo->term_id,
            ]]
        ]);

        // Regeneración completa (borrar todo)
        if ($regenerar && !empty($actuales)) {
            foreach ($actuales as $a) {
                wp_delete_post($a->ID, true);
                $eliminados++;
            }
        }

        // Crear nuevas unidades
        for ($i = 1; $i <= $num_alojamientos; $i++) {

            $titulo = $tipo->name . ' ' . $i;

            // Si NO estamos regenerando, evitar duplicados
            if (!$regenerar) {
                $existe = get_page_by_title($titulo, OBJECT, 'tureserva_alojamiento');
                if ($existe) continue;
            }

            wp_insert_post([
                'post_title'  => $titulo,
                'post_type'   => 'tureserva_alojamiento',
                'post_status' => 'publish',
                'tax_input'   => [
                    'tipo_alojamiento' => [$tipo->term_id]
                ],
            ]);

            $creados++;
        }
    }

    // Resultado
    $msg = [];
    if ($creados > 0) $msg[] = "$creados alojamientos generados.";
    if ($eliminados > 0) $msg[] = "$eliminados eliminados antes de regenerar.";
    if (empty($msg)) $msg[] = "No se realizaron cambios.";

    return implode(' ', $msg);
}
