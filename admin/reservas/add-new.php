<?php
/**
 * ==========================================================
 * ADMIN: Añadir nueva reserva — TuReserva
 * ==========================================================
 * Interfaz simplificada: muestra todos los alojamientos
 * disponibles para seleccionar y crear reserva.
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

// ==========================================================
// 🔐 Verificación de permisos
// ==========================================================
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos para acceder a esta página.', 'tureserva'));
}

// ==========================================================
// 📦 Encolar estilos y scripts de la página
// ==========================================================
add_action('admin_enqueue_scripts', function ($hook) {
    // Detectar la página de forma más confiable usando get_current_screen
    $screen = get_current_screen();
    $is_add_reserva_page = false;
    
    if ($screen) {
        // El hook para submenús bajo CPTs suele ser: {post_type}_page_{page_slug}
        $is_add_reserva_page = (
            strpos($hook, 'tureserva_reserva_page_tureserva-add-reserva') !== false ||
            strpos($hook, 'tureserva-add-reserva') !== false ||
            (isset($screen->id) && strpos($screen->id, 'tureserva-add-reserva') !== false)
        );
    }
    
    // También verificar por GET parameter
    if (!$is_add_reserva_page) {
        $is_add_reserva_page = (
            (isset($_GET['page']) && $_GET['page'] === 'tureserva-add-reserva') ||
            (isset($_GET['post_type']) && $_GET['post_type'] === 'tureserva_reserva' && isset($_GET['page']) && $_GET['page'] === 'tureserva-add-reserva')
        );
    }
    
    if (!$is_add_reserva_page) return;

    wp_enqueue_style(
        'tureserva-add-reserva',
        TURESERVA_URL . 'assets/css/admin-add-reserva.css?v=8',
        [],
        null
    );

    wp_enqueue_script(
        'tureserva-add-reserva',
        TURESERVA_URL . 'assets/js/admin-add-reserva.js?v=6',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('tureserva-add-reserva', 'TuReservaAddReserva', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('tureserva_add_reserva_nonce'),
    ]);
}, 999); // Prioridad alta para asegurar que se ejecute


// ==========================================================
// 🧭 Renderizado principal
// ==========================================================
?>
<div class="wrap tureserva-add-reserva">
    <h1><?php _e('Añadir nueva reserva', 'tureserva'); ?></h1>
    
    <p class="description">
        <?php _e('Selecciona un alojamiento para crear una nueva reserva:', 'tureserva'); ?>
    </p>
    
    <hr style="margin: 20px 0;">

    <?php
    // Obtener todos los alojamientos publicados
    $alojamientos = get_posts([
        'post_type'      => 'trs_alojamiento',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC'
    ]);

    if (empty($alojamientos)) {
        echo '<div class="notice notice-warning"><p>' . __('No hay alojamientos disponibles. Por favor, crea al menos un alojamiento primero.', 'tureserva') . '</p></div>';
    } else {
        ?>
        <table class="widefat striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th><?php _e('Alojamiento', 'tureserva'); ?></th>
                    <th><?php _e('Capacidad', 'tureserva'); ?></th>
                    <th><?php _e('Precio por noche', 'tureserva'); ?></th>
                    <th><?php _e('Acción', 'tureserva'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alojamientos as $alojamiento) : 
                    $capacidad = (int) get_post_meta($alojamiento->ID, '_tureserva_capacidad', true);
                    $precio_noche = get_post_meta($alojamiento->ID, '_tureserva_precio_noche', true);
                    if (empty($precio_noche)) {
                        $precio_noche = get_post_meta($alojamiento->ID, '_tureserva_precio_base', true);
                    }
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($alojamiento->post_title); ?></strong></td>
                        <td><?php echo $capacidad ? esc_html($capacidad) : '-'; ?></td>
                        <td><?php echo $precio_noche ? '$' . number_format((float)$precio_noche, 2) : '-'; ?></td>
                        <td>
                            <button class="button button-primary crear-reserva" 
                                    data-id="<?php echo esc_attr($alojamiento->ID); ?>"
                                    data-nombre="<?php echo esc_attr($alojamiento->post_title); ?>">
                                <?php _e('Reservar', 'tureserva'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    ?>

    <!-- Modal para datos de la reserva -->
    <div id="tureserva-modal" class="tureserva-modal" style="display:none;">
        <div class="tureserva-modal-content">
            <span class="close-modal">&times;</span>
            <h2><?php _e('Crear Nueva Reserva', 'tureserva'); ?></h2>
            <form id="tureserva-crear-reserva-form">
                <input type="hidden" id="modal_alojamiento_id" name="alojamiento_id">
                
                <div class="tureserva-field">
                    <label><?php _e('Alojamiento:', 'tureserva'); ?></label>
                    <strong id="modal_alojamiento_nombre"></strong>
                </div>

                <div class="tureserva-field">
                    <label for="modal_check_in"><?php _e('Día de llegada', 'tureserva'); ?> *</label>
                    <input type="date" id="modal_check_in" name="check_in" required>
                </div>

                <div class="tureserva-field">
                    <label for="modal_check_out"><?php _e('Día de salida', 'tureserva'); ?> *</label>
                    <input type="date" id="modal_check_out" name="check_out" required>
                </div>

                <div class="tureserva-field">
                    <label for="modal_adults"><?php _e('Adultos', 'tureserva'); ?> *</label>
                    <select id="modal_adults" name="adults" required>
                        <?php for ($i = 1; $i <= 10; $i++) printf('<option value="%d"%s>%d</option>', $i, $i == 2 ? ' selected' : '', $i); ?>
                    </select>
                </div>

                <div class="tureserva-field">
                    <label for="modal_children"><?php _e('Niños', 'tureserva'); ?></label>
                    <select id="modal_children" name="children">
                        <option value="0">0</option>
                        <?php for ($i = 1; $i <= 10; $i++) printf('<option value="%d">%d</option>', $i, $i); ?>
                    </select>
                </div>

                <div class="tureserva-field">
                    <label for="cliente_nombre"><?php _e('Nombre del Cliente', 'tureserva'); ?> *</label>
                    <input type="text" id="cliente_nombre" name="cliente_nombre" required>
                </div>

                <div class="tureserva-field">
                    <label for="cliente_email"><?php _e('Email del Cliente', 'tureserva'); ?> *</label>
                    <input type="email" id="cliente_email" name="cliente_email" required>
                </div>

                <div class="tureserva-field">
                    <label for="cliente_telefono"><?php _e('Teléfono', 'tureserva'); ?></label>
                    <input type="text" id="cliente_telefono" name="cliente_telefono">
                </div>

                <!-- Servicios Adicionales -->
                <div class="tureserva-field">
                    <label><?php _e('Servicios Adicionales', 'tureserva'); ?></label>
                    <div class="tureserva-modal-services" style="max-height:150px; overflow-y:auto; border:1px solid #ddd; padding:10px; border-radius:4px;">
                        <?php
                        $servicios = get_posts([
                            'post_type' => 'tureserva_servicio',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        ]);
                        if ($servicios) {
                            foreach ($servicios as $s) {
                                $precio = get_post_meta($s->ID, 'tureserva_precio', true);
                                $precio_txt = $precio ? " ($" . number_format($precio, 2) . ")" : '';
                                echo '<div style="margin-bottom:5px;">';
                                echo '<label><input type="checkbox" name="servicios[]" value="' . esc_attr($s->ID) . '"> ' . esc_html($s->post_title) . $precio_txt . '</label>';
                                echo '</div>';
                            }
                        } else {
                            echo '<small>No hay servicios disponibles.</small>';
                        }
                        ?>
                    </div>
                </div>

                <div class="tureserva-actions">
                    <button type="submit" class="button button-primary"><?php _e('Confirmar Reserva', 'tureserva'); ?></button>
                    <button type="button" class="button cancel-modal"><?php _e('Cancelar', 'tureserva'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .tureserva-modal {
        position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);
    }
    .tureserva-modal-content {
        background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; max-height: 90vh; overflow-y: auto;
    }
    .tureserva-field {
        margin-bottom: 15px;
    }
    .tureserva-field label {
        display: block; font-weight: 600; margin-bottom: 5px;
    }
    .tureserva-field input[type="text"],
    .tureserva-field input[type="email"],
    .tureserva-field input[type="date"],
    .tureserva-field select {
        width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
    }
    .close-modal {
        color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;
    }
    .close-modal:hover, .close-modal:focus {
        color: black; text-decoration: none; cursor: pointer;
    }
    .tureserva-actions {
        margin-top: 20px; text-align: right;
    }
    .tureserva-actions .button {
        margin-left: 10px;
    }
    </style>
    
    <!-- Script inline como fallback para asegurar que funcione -->
    <script type="text/javascript">
    (function() {
        console.log('🔍 [TuReserva] Verificando carga de scripts...');
        
        // Esperar a que jQuery esté disponible
        function initReservaScript() {
            if (typeof jQuery === 'undefined') {
                console.warn('⚠️ [TuReserva] jQuery no disponible, reintentando en 100ms...');
                setTimeout(initReservaScript, 100);
                return;
            }
            
            var $ = jQuery;
            console.log('✅ [TuReserva] jQuery disponible, inicializando eventos inline...');
            
            // Verificar que el script externo se haya cargado
            if (typeof TuReservaAddReserva === 'undefined') {
                console.warn('⚠️ [TuReserva] TuReservaAddReserva no disponible, usando valores por defecto');
                window.TuReservaAddReserva = {
                    ajax_url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                    nonce: '<?php echo esc_js(wp_create_nonce('tureserva_add_reserva_nonce')); ?>'
                };
            }
            
            // Verificar que los botones existan
            var $botones = $('.crear-reserva');
            console.log('🔍 [TuReserva] Botones encontrados (inline):', $botones.length);
            
            if ($botones.length === 0) {
                console.error('❌ [TuReserva] No se encontraron botones .crear-reserva');
                return;
            }
            
            // Verificar que el modal exista
            var $modal = $('#tureserva-modal');
            if ($modal.length === 0) {
                console.error('❌ [TuReserva] Modal no encontrado en el DOM');
                return;
            }
            console.log('✅ [TuReserva] Modal encontrado en el DOM');
            
            // Evento de click directo (más confiable)
            $botones.off('click.tureserva').on('click.tureserva', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🖱️ [TuReserva] Click detectado (inline)');
                
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                
                console.log('📋 [TuReserva] Datos del alojamiento:', {id: id, nombre: nombre});
                
                if (!id) {
                    console.error('❌ [TuReserva] No se encontró el ID del alojamiento');
                    alert('Error: No se pudo identificar el alojamiento.');
                    return;
                }
                
                if ($modal.length === 0) {
                    console.error('❌ [TuReserva] Modal no existe');
                    alert('Error: No se encontró el formulario de reserva.');
                    return;
                }
                
                $('#modal_alojamiento_id').val(id);
                $('#modal_alojamiento_nombre').text(nombre);
                
                var hoy = new Date().toISOString().split('T')[0];
                $('#modal_check_in').attr('min', hoy);
                $('#modal_check_out').attr('min', hoy);
                
                $('#tureserva-crear-reserva-form')[0].reset();
                $('#modal_alojamiento_id').val(id);
                $('#modal_alojamiento_nombre').text(nombre);
                $('#modal_adults').val('2');
                $('#modal_children').val('0');
                
                console.log('✅ [TuReserva] Mostrando modal (inline)...');
                $modal.css('display', 'block').fadeIn(300);
                console.log('✅ [TuReserva] Modal visible:', $modal.is(':visible'));
            });
            
            // Cerrar modal
            $(document).off('click.tureserva', '.close-modal, .cancel-modal').on('click.tureserva', '.close-modal, .cancel-modal', function(e) {
                e.preventDefault();
                console.log('🔴 [TuReserva] Cerrando modal...');
                $('#tureserva-modal').fadeOut(300);
            });
            
            // Validar que check_out sea posterior a check_in
            $(document).off('change.tureserva', '#modal_check_in').on('change.tureserva', '#modal_check_in', function() {
                var checkIn = $(this).val();
                if (checkIn) {
                    var fechaCheckIn = new Date(checkIn);
                    fechaCheckIn.setDate(fechaCheckIn.getDate() + 1);
                    var fechaMinima = fechaCheckIn.toISOString().split('T')[0];
                    $('#modal_check_out').attr('min', fechaMinima);
                    
                    var checkOut = $('#modal_check_out').val();
                    if (checkOut && checkOut <= checkIn) {
                        $('#modal_check_out').val('');
                    }
                }
            });
            
            // Submit del formulario
            $('#tureserva-crear-reserva-form').off('submit.tureserva').on('submit.tureserva', function(e) {
                e.preventDefault();
                console.log('📤 [TuReserva] Enviando formulario (inline)...');
                
                var data = {
                    action: 'tureserva_create_reservation',
                    security: TuReservaAddReserva.nonce,
                    alojamiento_id: $('#modal_alojamiento_id').val(),
                    check_in: $('#modal_check_in').val(),
                    check_out: $('#modal_check_out').val(),
                    adults: $('#modal_adults').val(),
                    children: $('#modal_children').val(),
                    cliente_nombre: $('#cliente_nombre').val(),
                    cliente_email: $('#cliente_email').val(),
                    cliente_telefono: $('#cliente_telefono').val(),
                    servicios: []
                };
                
                $('input[name="servicios[]"]:checked').each(function() {
                    data.servicios.push($(this).val());
                });
                
                if (!data.check_in || !data.check_out) {
                    alert('Por favor, completa las fechas de llegada y salida.');
                    return;
                }
                
                if (new Date(data.check_out) <= new Date(data.check_in)) {
                    alert('La fecha de salida debe ser posterior a la fecha de llegada.');
                    return;
                }
                
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Procesando...');
                
                console.log('📤 [TuReserva] Enviando datos:', data);
                
                $.post(TuReservaAddReserva.ajax_url, data, function(response) {
                    console.log('📥 [TuReserva] Respuesta recibida:', response);
                    if (response.success) {
                        alert(response.data.message || 'Reserva creada exitosamente.');
                        window.location.href = response.data.redirect || 'edit.php?post_type=tureserva_reserva';
                    } else {
                        alert('Error: ' + (response.data || 'Error desconocido'));
                        $btn.prop('disabled', false).text('Confirmar Reserva');
                    }
                }).fail(function(xhr, status, error) {
                    console.error('❌ [TuReserva] Error AJAX:', {xhr, status, error, response: xhr.responseText});
                    alert('Error de conexión. Por favor, intenta nuevamente.');
                    $btn.prop('disabled', false).text('Confirmar Reserva');
                });
            });
            
            // Cerrar modal si se hace clic fuera
            $(document).off('click.tureserva', '#tureserva-modal').on('click.tureserva', '#tureserva-modal', function(e) {
                if ($(e.target).is('#tureserva-modal')) {
                    console.log('🔴 [TuReserva] Click fuera del modal, cerrando...');
                    $('#tureserva-modal').fadeOut(300);
                }
            });
            
            console.log('✅ [TuReserva] Script inline inicializado correctamente');
        }
        
        // Iniciar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initReservaScript);
        } else {
            initReservaScript();
        }
    })();
    </script>
</div>
