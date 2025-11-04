<?php
/**
 * ==========================================================
 * ADMIN: Historial de Pagos — TuReserva
 * ==========================================================
 * Versión con estructura WP_List_Table (similar a MotoPress)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// 📂 Ajuste de ruta correcto
if (!class_exists('Tureserva_Payments_Table')) {
    require_once TURESERVA_PATH . 'admin/partials/pagos/class-tureserva-payments-table.php';
}

// 📋 Render principal de la página
function tureserva_historial_pagos_page_render() {
    $payments_table = new Tureserva_Payments_Table();
    $payments_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Historial de pagos', 'tureserva'); ?></h1>
        <a href="post-new.php?post_type=tureserva_pagos" class="page-title-action">
            <?php _e('Añadir nuevo pago', 'tureserva'); ?>
        </a>
        <hr class="wp-header-end">

        <form method="post">
            <?php $payments_table->display(); ?>
        </form>
    echo '<pre>';
print_r($payments_table);
echo '</pre>';
</div>
    <?php
}
