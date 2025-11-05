<?php
/**
 * ==========================================================
 * ADMIN: Historial de Pagos — TuReserva
 * ==========================================================
 * Versión con botón de sincronización manual con Supabase.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// Asegurar carga de la clase
if (!class_exists('Tureserva_Payments_Table')) {
    require_once TURESERVA_PATH . 'admin/partials/pagos/class-tureserva-payments-table.php';
}

// =======================================================
// 🔄 Acción AJAX: Sincronizar manualmente pagos completados
// =======================================================
add_action('wp_ajax_tureserva_sync_pagos_manual', 'tureserva_sync_pagos_manual_callback');

function tureserva_sync_pagos_manual_callback() {

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Acceso denegado');
    }

    // Incluir el archivo de sincronización
    require_once TURESERVA_PATH . 'includes/sync/tureserva-sync-pagos.php';

    $pagos = get_posts([
        'post_type'      => 'tureserva_pagos',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $count = 0;
    foreach ($pagos as $pago) {
        $estado = strtolower(get_post_meta($pago->ID, '_tureserva_pago_estado', true) ?: '');
        if ($estado === 'completado') {
            tureserva_sync_pago_supabase($pago->ID, $pago);
            $count++;
        }
    }

    wp_send_json_success("Se sincronizaron $count pagos completados con Supabase.");
}

// =======================================================
// 🧾 Render principal de la página
// =======================================================
function tureserva_historial_pagos_page_render() {
    $payments_table = new Tureserva_Payments_Table();
    $payments_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Historial de pagos', 'tureserva'); ?></h1>
        <a href="post-new.php?post_type=tureserva_pagos" class="page-title-action">
            <?php _e('Añadir nuevo pago', 'tureserva'); ?>
        </a>

        <!-- Botón manual -->
        <button id="tureserva-sync-btn" class="button button-primary" style="margin-left:10px;">
            <?php _e('Sincronizar pagos completados', 'tureserva'); ?>
        </button>
        <span id="tureserva-sync-status" style="margin-left:8px;"></span>

        <hr class="wp-header-end">

        <form method="post">
            <?php $payments_table->display(); ?>
        </form>
    </div>

    <script>
    (function($){
        $('#tureserva-sync-btn').on('click', function(){
            const $btn = $(this);
            const $status = $('#tureserva-sync-status');
            $btn.prop('disabled', true);
            $status.text('Sincronizando...');

            $.post(ajaxurl, { action: 'tureserva_sync_pagos_manual' }, function(response){
                if(response.success){
                    $status.text(response.data);
                } else {
                    $status.text('Error: ' + response.data);
                }
                $btn.prop('disabled', false);
            });
        });
    })(jQuery);
    </script>
    <?php
}
