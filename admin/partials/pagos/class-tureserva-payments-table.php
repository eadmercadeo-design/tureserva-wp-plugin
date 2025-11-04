<?php
/**
 * ==========================================================
 * CLASS: Tureserva_Payments_Table
 * ==========================================================
 * Tabla de pagos tipo WP_List_Table (similar a MotoPress)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once trailingslashit(ABSPATH) . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('WP_List_Table')) {
    wp_die(__('No se pudo cargar WP_List_Table. Asegúrate de que estás dentro del panel de administración.', 'tureserva'));
}


class Tureserva_Payments_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('pago', 'tureserva'),
            'plural'   => __('pagos', 'tureserva'),
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        return [
            'cb'             => '<input type="checkbox" />',
            'id'             => __('Identidad', 'tureserva'),
            'cliente'        => __('Cliente', 'tureserva'),
            'estado'         => __('Estado', 'tureserva'),
            'cantidad'       => __('Cantidad', 'tureserva'),
            'reserva'        => __('Reserva', 'tureserva'),
            'pasarela'       => __('Pasarela', 'tureserva'),
            'transaccion_id' => __('ID de transacción', 'tureserva'),
            'fecha'          => __('Fecha', 'tureserva'),
        ];
    }

    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = [
            [
                'id' => 'PG-0001',
                'cliente' => 'Juan Pérez',
                'estado' => 'Completado',
                'cantidad' => '$120.00',
                'reserva' => '#R-1023',
                'pasarela' => 'Stripe',
                'transaccion_id' => 'txn_89HF3KD',
                'fecha' => '2025-11-03'
            ],
        ];
    }

    public function column_default($item, $column_name) {
        return $item[$column_name] ?? '';
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="pago[]" value="%s" />', $item['id']);
    }
}
