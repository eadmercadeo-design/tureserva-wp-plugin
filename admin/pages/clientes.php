<?php
/**
 * ==========================================================
 * MÓDULO: Gestión de Clientes — TuReserva
 * ==========================================================
 * Genera una tabla de clientes basada en los metadatos de las reservas.
 * No crea usuarios de WordPress, solo agrega información.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// =======================================================
// 🏗️ CLASE WP_LIST_TABLE PERSONALIZADA
// =======================================================
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class TuReserva_Clientes_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'cliente',
            'plural'   => 'clientes',
            'ajax'     => false
        ]);
    }

    /**
     * 🔍 Obtener datos de clientes desde la BD
     */
    private function get_clients_data($per_page = 20, $page_number = 1)
    {
        global $wpdb;

        $offset = ($page_number - 1) * $per_page;

        // Consulta SQL optimizada para agrupar por email
        // Se asume que el nombre más reciente es el válido
        $sql = "
            SELECT 
                m_email.meta_value as email,
                MAX(m_nombre.meta_value) as nombre,
                MAX(m_telefono.meta_value) as telefono,
                COUNT(p.ID) as total_reservas,
                MIN(p.post_date) as fecha_registro,
                MAX(p.post_date) as ultima_actividad,
                (
                    SELECT pm_estado.meta_value 
                    FROM {$wpdb->postmeta} pm_estado 
                    WHERE pm_estado.post_id = MAX(p.ID) 
                    AND pm_estado.meta_key = '_tureserva_estado'
                ) as ultimo_estado
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m_email ON p.ID = m_email.post_id AND m_email.meta_key = '_tureserva_cliente_email'
            LEFT JOIN {$wpdb->postmeta} m_nombre ON p.ID = m_nombre.post_id AND m_nombre.meta_key = '_tureserva_cliente_nombre'
            LEFT JOIN {$wpdb->postmeta} m_telefono ON p.ID = m_telefono.post_id AND m_telefono.meta_key = '_tureserva_cliente_telefono'
            WHERE p.post_type = 'tureserva_reserva' 
            AND p.post_status IN ('publish', 'future', 'private')
            GROUP BY m_email.meta_value
            ORDER BY ultima_actividad DESC
            LIMIT $per_page OFFSET $offset
        ";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * 🔢 Contar total de clientes únicos
     */
    private function get_total_clients()
    {
        global $wpdb;
        $sql = "
            SELECT COUNT(DISTINCT m_email.meta_value)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m_email ON p.ID = m_email.post_id AND m_email.meta_key = '_tureserva_cliente_email'
            WHERE p.post_type = 'tureserva_reserva'
            AND p.post_status IN ('publish', 'future', 'private')
        ";
        return $wpdb->get_var($sql);
    }

    /**
     * 📝 Definir columnas
     */
    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'cliente'     => 'Cliente',
            'email'       => 'Email',
            'telefono'    => 'Teléfono',
            'reservas'    => 'Reservas',
            'registro'    => 'Registrado',
            'actividad'   => 'Última Actividad',
            'estado'      => 'Estado'
        ];
    }

    /**
     * 🖌️ Renderizar columna checkbox
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="cliente[]" value="%s" />',
            esc_attr($item['email'])
        );
    }

    /**
     * 🖌️ Renderizar columna Cliente (con acciones)
     */
    protected function column_cliente($item)
    {
        // Avatar generado con Gravatar o placeholder
        $avatar = get_avatar($item['email'], 32);
        
        $actions = [
            'view' => sprintf(
                '<a href="?post_type=tureserva_reserva&page=tureserva-clientes&view=details&email=%s">Ver detalles</a>',
                urlencode($item['email'])
            )
        ];

        return sprintf(
            '<div style="display:flex;align-items:center;gap:10px;">%s <strong>%s</strong></div>%s',
            $avatar,
            esc_html($item['nombre'] ?: 'Sin nombre'),
            $this->row_actions($actions)
        );
    }

    /**
     * 🖌️ Renderizar columnas genéricas
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'email':
                return '<a href="mailto:' . esc_attr($item['email']) . '">' . esc_html($item['email']) . '</a>';
            case 'telefono':
                return esc_html($item['telefono'] ?: '—');
            case 'reservas':
                return '<span class="ts-badge-count">' . intval($item['total_reservas']) . '</span>';
            case 'registro':
                return date_i18n(get_option('date_format'), strtotime($item['fecha_registro']));
            case 'actividad':
                return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['ultima_actividad']));
            case 'estado':
                $estado = $item['ultimo_estado'] ?: 'unknown';
                $color = match($estado) {
                    'confirmada' => '#46b450',
                    'pendiente'  => '#ffb900',
                    'cancelada'  => '#dc3232',
                    default      => '#72aee6'
                };
                return sprintf(
                    '<span style="color:%s;font-weight:600;">%s</span>',
                    $color,
                    ucfirst($estado)
                );
            default:
                return print_r($item, true);
        }
    }

    /**
     * ⚙️ Preparar items para la tabla
     */
    public function prepare_items()
    {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_clients();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = $this->get_clients_data($per_page, $current_page);
        $this->_column_headers = [$this->get_columns(), [], []];
    }
}


// =======================================================
// 🖥️ RENDERIZADO DE LA PÁGINA
// =======================================================
function tureserva_clientes_page_render()
{
    // 🕵️ VISTA DE DETALLES
    if (isset($_GET['view']) && $_GET['view'] === 'details' && !empty($_GET['email'])) {
        tureserva_render_cliente_detalles(sanitize_email($_GET['email']));
        return;
    }

    // 📋 VISTA DE TABLA (DEFAULT)
    $table = new TuReserva_Clientes_Table();
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">👥 Clientes</h1>
        <p class="description">Listado de clientes generado automáticamente desde las reservas.</p>
        <hr class="wp-header-end">

        <style>
            .ts-badge-count {
                background: #f0f0f1;
                color: #1d2327;
                padding: 2px 8px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 11px;
            }
            .wp-list-table th { font-weight: 700; }
            .alternate, .striped > tbody > :nth-child(odd), ul.striped > :nth-child(odd) { background-color: #f9f9f9; }
            .row-actions { visibility: hidden; }
            tr:hover .row-actions { visibility: visible; }
        </style>

        <form method="post">
            <?php $table->display(); ?>
        </form>
    </div>
    <?php
}

// =======================================================
// 👤 VISTA DETALLADA DEL CLIENTE
// =======================================================
function tureserva_render_cliente_detalles($email)
{
    global $wpdb;

    // Obtener todas las reservas de este email
    $reservas = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, p.post_date, p.post_status
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id
        WHERE p.post_type = 'tureserva_reserva'
        AND m.meta_key = '_tureserva_cliente_email'
        AND m.meta_value = %s
        ORDER BY p.post_date DESC
    ", $email));

    if (empty($reservas)) {
        echo '<div class="wrap"><h1>Cliente no encontrado</h1></div>';
        return;
    }

    // Datos del cliente (del último registro)
    $ultimo_id = $reservas[0]->ID;
    $nombre = get_post_meta($ultimo_id, '_tureserva_cliente_nombre', true);
    $telefono = get_post_meta($ultimo_id, '_tureserva_cliente_telefono', true);
    $total_gastado = 0;

    foreach ($reservas as $r) {
        $total_gastado += floatval(get_post_meta($r->ID, '_tureserva_precio_total', true));
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">👤 Detalle del Cliente</h1>
        <a href="?post_type=tureserva_reserva&page=tureserva-clientes" class="page-title-action">← Volver al listado</a>
        <hr class="wp-header-end">

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px; margin-top:20px;">
            
            <!-- 🆔 TARJETA DE PERFIL -->
            <div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="text-align:center; margin-bottom:20px;">
                    <?php echo get_avatar($email, 96, '', '', ['class' => 'ts-avatar']); ?>
                    <h2 style="margin:10px 0 5px;"><?php echo esc_html($nombre); ?></h2>
                    <p style="color:#666; margin:0;"><?php echo esc_html($email); ?></p>
                </div>
                <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
                <p><strong>📞 Teléfono:</strong> <?php echo esc_html($telefono ?: '—'); ?></p>
                <p><strong>📅 Primera reserva:</strong> <?php echo date_i18n('d M, Y', strtotime($reservas[count($reservas)-1]->post_date)); ?></p>
                <p><strong>💰 Total gastado (aprox):</strong> $<?php echo number_format($total_gastado, 2); ?></p>
                <p><strong>🏷️ Total reservas:</strong> <?php echo count($reservas); ?></p>
            </div>

            <!-- 📜 HISTORIAL DE RESERVAS -->
            <div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top:0;">Historial de Reservas</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Alojamiento</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): 
                            $alojamiento_id = get_post_meta($reserva->ID, '_tureserva_alojamiento_id', true);
                            $estado = get_post_meta($reserva->ID, '_tureserva_estado', true);
                            $precio = get_post_meta($reserva->ID, '_tureserva_precio_total', true);
                            
                            $color = match($estado) {
                                'confirmada' => '#46b450',
                                'pendiente'  => '#ffb900',
                                'cancelada'  => '#dc3232',
                                default      => '#72aee6'
                            };
                        ?>
                        <tr>
                            <td>#<?php echo $reserva->ID; ?></td>
                            <td><?php echo date_i18n('d/m/Y', strtotime($reserva->post_date)); ?></td>
                            <td>
                                <?php echo $alojamiento_id ? esc_html(get_the_title($alojamiento_id)) : '—'; ?>
                            </td>
                            <td>
                                <span style="color:<?php echo $color; ?>; font-weight:bold;">
                                    <?php echo ucfirst($estado); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format((float)$precio, 2); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($reserva->ID); ?>" class="button button-small">Ver Reserva</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .ts-avatar { border-radius: 50%; border: 3px solid #f0f0f1; }
    </style>
    <?php
}
