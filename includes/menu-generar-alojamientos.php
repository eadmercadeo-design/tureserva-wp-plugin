<?php
/**
 * Submenú: Generar alojamientos
 * Crea alojamientos físicos a partir de los tipos definidos
 */

if (!defined('ABSPATH')) exit;

// === AÑADIR SUBMENÚ JUSTO DEBAJO DE "AÑADIR NUEVO ALOJAMIENTO" === //
function tureserva_add_generar_alojamientos_submenu() {
    add_submenu_page(
        'edit.php?post_type=alojamiento',
        __('Generar alojamientos', 'tureserva'),
        __('Generar alojamientos', 'tureserva'),
        'manage_options',
        'tureserva-generar-alojamientos',
        'tureserva_generar_alojamientos_page'
    );
}
add_action('admin_menu', 'tureserva_add_generar_alojamientos_submenu', 11);


// === PANTALLA DE ADMINISTRACIÓN === //
function tureserva_generar_alojamientos_page() {
    echo '<div class="wrap">';
    echo '<h1>' . __('Generar alojamientos', 'tureserva') . '</h1>';
    echo '<p>Este proceso crea o regenera alojamientos físicos según el número especificado en cada tipo de alojamiento. '
        . 'Si ya existen, puedes optar por regenerarlos para ajustar la cantidad o eliminar los sobrantes.</p>';

    if (isset($_POST['tureserva_generar'])) {
        $regenerar = isset($_POST['tureserva_regenerar']) ? true : false;
        $resultado = tureserva_run_alojamientos_generator($regenerar);
        echo '<div class="notice notice-success"><p><strong>Resultado:</strong> ' . esc_html($resultado) . '</p></div>';
    }

    echo '<form method="post" style="margin-top:20px;">';
    echo '<label><input type="checkbox" name="tureserva_regenerar" value="1"> '
        . 'Regenerar alojamientos existentes (borra y recrea según el número actual)</label><br><br>';
    submit_button('Generar alojamientos', 'primary', 'tureserva_generar');
    echo '</form>';
    echo '</div>';
}


// === LÓGICA PRINCIPAL: GENERAR O REGENERAR === //
function tureserva_run_alojamientos_generator($regenerar = false) {
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

        // Obtener número real desde el metacampo (si lo tienes guardado así)
        $num_alojamientos = get_term_meta($tipo->term_id, 'tureserva_numero_alojamientos', true);
        if (empty($num_alojamientos) || $num_alojamientos < 1) continue;

        // Obtener alojamientos actuales de este tipo
        $actuales = get_posts([
            'post_type'   => 'alojamiento',
            'numberposts' => -1,
            'tax_query'   => [[
                'taxonomy' => 'tipo_alojamiento',
                'field'    => 'term_id',
                'terms'    => $tipo->term_id,
            ]]
        ]);

        // Si se marca "regenerar", borrar todos los existentes primero
        if ($regenerar && !empty($actuales)) {
            foreach ($actuales as $a) {
                wp_delete_post($a->ID, true);
                $eliminados++;
            }
        }

        // Crear las nuevas unidades
        for ($i = 1; $i <= $num_alojamientos; $i++) {
            $titulo = $tipo->name . ' ' . $i;

            // Saltar si ya existe y no se regeneró
            if (!$regenerar) {
                $existe = get_page_by_title($titulo, OBJECT, 'alojamiento');
                if ($existe) continue;
            }

            wp_insert_post([
                'post_title'  => $titulo,
                'post_type'   => 'alojamiento',
                'post_status' => 'publish',
                'tax_input'   => ['tipo_alojamiento' => [$tipo->term_id]],
            ]);

            $creados++;
        }
    }

    $msg = [];
    if ($creados > 0) $msg[] = "$creados alojamientos generados.";
    if ($eliminados > 0) $msg[] = "$eliminados eliminados antes de regenerar.";
    if (empty($msg)) $msg[] = "No se realizaron cambios.";

    return implode(' ', $msg);
}
