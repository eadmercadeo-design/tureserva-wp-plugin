<?php
/**
 * ==========================================================
 * CLASE: TuReserva ↔ WooCommerce Bridge
 * ==========================================================
 * Este archivo maneja la integración completa entre TuReserva
 * y WooCommerce:
 * 
 * 1️⃣ TuReserva → WooCommerce
 *    Crea pedidos WooCommerce a partir de reservas.
 * 
 * 2️⃣ WooCommerce → TuReserva
 *    Crea reservas en TuReserva cuando un pedido WooCommerce
 *    se completa exitosamente.
 * 
 * Soporta sincronización de estados y producto virtual.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * 🚀 CLASE PRINCIPAL: TuReserva → WooCommerce
 * ==========================================================
 */
class TuReserva_WooCommerce_Bridge {

    public function __construct() {
        // Inicializar solo si WooCommerce está activo
        if (!class_exists('WooCommerce')) return;

        // Crear pedido WooCommerce al confirmar una reserva
        add_action('tureserva_after_reserva_creada', [$this, 'crear_pedido_desde_reserva'], 10, 1);

        // Sincronizar estado WooCommerce → TuReserva
        add_action('woocommerce_order_status_changed', [$this, 'sincronizar_estado_reserva'], 10, 4);
    }

    /**
     * 🧾 Crear pedido WooCommerce al generar una reserva
     */
    public function crear_pedido_desde_reserva($reserva_id) {
        if (!get_option('tureserva_woo_enable')) return;

        // Obtener datos de la reserva
        $cliente = get_post_meta($reserva_id, '_tureserva_cliente', true);
        $total   = get_post_meta($reserva_id, '_tureserva_total', true);
        $titulo  = get_the_title($reserva_id);

        if (empty($total)) $total = 0;

        // Crear pedido WooCommerce
        $order = wc_create_order();
        $prefix = get_option('tureserva_woo_order_prefix', 'RES-');
        $order->set_created_via('tureserva');
        $order->set_customer_id(get_current_user_id());
        $order->add_order_note('Reserva generada desde TuReserva (#' . $reserva_id . ')');

        // Añadir línea de producto virtual representando la reserva
        $product = $this->obtener_producto_virtual();
        if ($product) {
            $order->add_product($product, 1, ['subtotal' => $total, 'total' => $total]);
        } else {
            // Si no existe el producto, crearlo automáticamente
            $product_id = $this->crear_producto_virtual();
            $product = wc_get_product($product_id);
            $order->add_product($product, 1, ['subtotal' => $total, 'total' => $total]);
        }

        $order->calculate_totals();

        // Asociar reserva y pedido
        update_post_meta($reserva_id, '_tureserva_woo_order_id', $order->get_id());
        update_post_meta($order->get_id(), '_tureserva_reserva_id', $reserva_id);

        // Redirigir al checkout de WooCommerce
        wp_redirect($order->get_checkout_payment_url());
        exit;
    }

    /**
     * 🔗 Obtener o crear el producto virtual para reservas
     */
    private function obtener_producto_virtual() {
        $product_id = get_option('tureserva_producto_virtual_id');
        if ($product_id && get_post_status($product_id)) {
            return wc_get_product($product_id);
        }
        return null;
    }

    private function crear_producto_virtual() {
        $producto_id = wp_insert_post([
            'post_title'  => __('Reserva TuReserva', 'tureserva'),
            'post_content'=> __('Producto virtual para procesar pagos de reservas.', 'tureserva'),
            'post_status' => 'publish',
            'post_type'   => 'product'
        ]);

        // Configurar como producto virtual sin stock
        update_post_meta($producto_id, '_virtual', 'yes');
        update_post_meta($producto_id, '_price', 0);
        update_post_meta($producto_id, '_stock_status', 'instock');
        update_post_meta($producto_id, '_manage_stock', 'no');

        update_option('tureserva_producto_virtual_id', $producto_id);
        return $producto_id;
    }

    /**
     * 🔄 Sincronizar estado WooCommerce → Reserva
     */
    public function sincronizar_estado_reserva($order_id, $old_status, $new_status, $order) {
        $reserva_id = get_post_meta($order_id, '_tureserva_reserva_id', true);
        if (!$reserva_id) return;

        $map_status = get_option('tureserva_woo_status_map', 'completed');

        if ($new_status === $map_status) {
            // Marcar reserva como pagada
            update_post_meta($reserva_id, '_tureserva_estado_pago', 'pagado');
            wp_update_post([
                'ID' => $reserva_id,
                'post_status' => 'publish'
            ]);

            $titulo = get_the_title($reserva_id);
            $order->add_order_note("✅ Reserva {$titulo} marcada como pagada en TuReserva.");
        }

        if ($new_status === 'failed' || $new_status === 'cancelled') {
            update_post_meta($reserva_id, '_tureserva_estado_pago', 'fallido');
        }
    }
}

/**
 * ==========================================================
 * 🔁 CLASE INVERSA: WooCommerce → TuReserva
 * ==========================================================
 * Crea una reserva en TuReserva cuando un pedido WooCommerce
 * se completa exitosamente.
 * ==========================================================
 */
class TuReserva_WooCommerce_To_TuReserva {

    public function __construct() {
        // Crear reserva cuando el pedido WooCommerce se marca como completado
        add_action('woocommerce_order_status_completed', [$this, 'crear_reserva_desde_pedido']);
    }

    /**
     * 🏨 Crear reserva en TuReserva desde pedido WooCommerce
     */
    public function crear_reserva_desde_pedido($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;

        // Evitar duplicados
        if (get_post_meta($order_id, '_tureserva_reserva_id', true)) return;

        $cliente = [
            'nombre'   => $order->get_billing_first_name(),
            'apellido' => $order->get_billing_last_name(),
            'email'    => $order->get_billing_email(),
            'telefono' => $order->get_billing_phone(),
        ];

        // Crear post de tipo "reserva"
        $reserva_id = wp_insert_post([
            'post_type'   => 'reserva',
            'post_title'  => sprintf(__('Reserva #%s – %s %s', 'tureserva'), $order_id, $cliente['nombre'], $cliente['apellido']),
            'post_status' => 'publish',
        ]);

        // Guardar metadatos de la reserva
        update_post_meta($reserva_id, '_tureserva_cliente', $cliente);
        update_post_meta($reserva_id, '_tureserva_total', $order->get_total());
        update_post_meta($reserva_id, '_tureserva_estado_pago', 'pagado');
        update_post_meta($reserva_id, '_tureserva_fuente', 'woocommerce');
        update_post_meta($reserva_id, '_tureserva_woo_order_id', $order_id);

        // Añadir nota en el pedido
        $order->add_order_note('✅ Reserva creada automáticamente en TuReserva (#' . $reserva_id . ')');

        // Relacionar ambos
        update_post_meta($order_id, '_tureserva_reserva_id', $reserva_id);
    }
}

/**
 * ==========================================================
 * ⚙️ INICIALIZAR AMBOS PUENTES
 * ==========================================================
 */
add_action('plugins_loaded', function() {
    new TuReserva_WooCommerce_Bridge();
    new TuReserva_WooCommerce_To_TuReserva();
});
