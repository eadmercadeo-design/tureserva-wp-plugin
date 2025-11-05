<?php
/**
 * ==========================================================
 * CLASS: Tureserva_Payments_Table
 * ==========================================================
 * Muestra los pagos reales del CPT 'tureserva_pagos'
 * con estructura WP_List_Table (similar a MotoPress)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Tureserva_Payments_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Pago', 'tureserva'),
            'plural'   => __('Pagos', 'tureserva'),
            'ajax'     => false
        ]);
    }

    // ==========================================================
    // 🔹 Definir columnas
    // ==========================================================
    public function get_columns() {
        return [
            'cb'             => '<input type="checkbox" />',
            'identidad'      => __('Identidad', 'tureserva'),
            'cliente'        => __('Cliente', 'tureserva'),
            'estado'         => __('Estado', 'tureserva'),
            'cantidad'       => __('Cantidad', 'tureserva'),
            'reserva'        => __('Reserva', 'tureserva'),
            'pasarela'       => __('Pasarela', 'tureserva'),
            'transaccion_id' => __('ID de transacción', 'tureserva'),
            'sync_status'    => __('Sincronización', 'tureserva'),
            'fecha'          => __('Fecha', 'tureserva'),
        ];
    }

    // ==========================================================
    // 🔹 Preparar datos de los pagos
    // ==========================================================
    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $pagos = get_posts([
            'post_type'      => 'tureserva_pagos',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);

        $items = [];

        foreach ($pagos as $pago) {
            $meta = get_post_meta($pago->ID);

            $id_pago = esc_html($meta['_tureserva_pago_id'][0] ?? '—');
            $cliente = esc_html($meta['_tureserva_fact_nombre'][0] ?? '—');
            $estado  = esc_html(get_post_meta($pago->ID, '_tureserva_pago_estado', true) ?: 'Pendiente');
            $monto   = floatval($meta['_tureserva_pago_monto'][0] ?? 0);
            $moneda  = strtoupper($meta['_tureserva_pago_moneda'][0] ?? 'USD');
            $reserva_id = intval($meta['_tureserva_reserva_id'][0] ?? 0);
            $pasarela = esc_html($meta['_tureserva_pasarela'][0] ?? 'Manual');
            $transaccion = esc_html($meta['_tureserva_transaccion_id'][0] ?? '—');
            $fecha = get_the_date('Y-m-d', $pago->ID);

            // Estado de sincronización
            $sync_status = get_post_meta($pago->ID, '_tureserva_sync_status', true) ?: 'pendiente';
            $sync_fecha = get_post_meta($pago->ID, '_tureserva_sync_fecha', true);

            $items[] = [
                'identidad' => $id_pago,
                'cliente' => $cliente,
                'estado' => $estado,
                'cantidad' => '$' . number_format($monto, 2) . ' ' . $moneda,
                'reserva' => $reserva_id ? ('#' . $reserva_id) : '—',
                'pasarela' => $pasarela,
                'transaccion_id' => $transaccion,
                'sync_status' => $sync_status,
                'sync_fecha' => $sync_fecha,
                'fecha' => $fecha,
                'post_id' => $pago->ID // 👈 Guardamos el ID real del post
            ];
        }

        $this->items = $items;
    }

    // ==========================================================
    // 🔹 Render por defecto
    // ==========================================================
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'identidad':
                return $this->column_identidad($item);
            case 'sync_status':
                return $this->column_sync_status($item);
            default:
                return $item[$column_name] ?? '';
        }
    }

    // ==========================================================
    // 🔹 Checkbox de selección
    // ==========================================================
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="pago[]" value="%s" />', esc_attr($item['identidad']));
    }

    // ==========================================================
    // 🔹 Columna personalizada: Identidad con acciones
    // ==========================================================
    public function column_identidad($item) {
        $id = intval($item['post_id']);

        $edit_link  = $id ? get_edit_post_link($id) : '#';
        $trash_link = $id ? get_delete_post_link($id) : '#';

        $actions = [];
        if ($id) {
            $actions['edit']  = sprintf('<a href="%s">%s</a>', esc_url($edit_link), __('Editar', 'tureserva'));
            $actions['trash'] = sprintf('<a href="%s" style="color:#b32d2e;">%s</a>', esc_url($trash_link), __('Papelera', 'tureserva'));
        }

        return sprintf('%s %s', esc_html($item['identidad']), $this->row_actions($actions));
    }

    // ==========================================================
    // 🔹 Columna personalizada: Estado de sincronización
    // ==========================================================
    public function column_sync_status($item) {
        $status = $item['sync_status'] ?? 'pendiente';
        $sync_fecha = $item['sync_fecha'] ?? '';

        $colors = [
            'sincronizado' => '#22b14c',
            'error' => '#d9534f',
            'pendiente' => '#f0ad4e'
        ];

        $labels = [
            'sincronizado' => '✅ Sincronizado',
            'error' => '❌ Error',
            'pendiente' => '⏳ Pendiente'
        ];

        $color = $colors[$status] ?? '#777';
        $label = $labels[$status] ?? ucfirst($status);

        $html = "<span style='font-weight:600; color:{$color};'>{$label}</span>";
        
        if ($sync_fecha && $status === 'sincronizado') {
            $html .= "<br><small style='color:#777;'>" . date_i18n('d/m/Y H:i', strtotime($sync_fecha)) . "</small>";
        }

        return $html;
    }
} // ✅ Fin de la clase

