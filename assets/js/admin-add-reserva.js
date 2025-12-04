(function($) {
    'use strict';
    
    // Verificar que jQuery esté disponible
    if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
        console.error('❌ jQuery no está disponible');
        return;
    }
    
    console.log('✅ Script admin-add-reserva.js cargado correctamente');
    
    $(document).ready(function () {
        console.log('✅ Documento listo, inicializando eventos...');
        
        // Verificar que el modal exista en el DOM
        const $modal = $('#tureserva-modal');
        if ($modal.length === 0) {
            console.error('❌ El modal #tureserva-modal no existe en el DOM');
        } else {
            console.log('✅ Modal encontrado en el DOM');
        }
        
        // Verificar que los botones existan
        const $botones = $('.crear-reserva');
        console.log('🔍 Botones "Reservar" encontrados:', $botones.length);
        
        if ($botones.length === 0) {
            console.warn('⚠️ No se encontraron botones con clase .crear-reserva');
        }
        
        // 🟢 Abrir modal al hacer clic en "Reservar"
        $(document).on('click', '.crear-reserva', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Click en botón Reservar detectado');
            
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            
            console.log('📋 Datos del alojamiento:', {id, nombre});
            
            if (!id) {
                console.error('❌ No se encontró el ID del alojamiento');
                alert('Error: No se pudo identificar el alojamiento.');
                return;
            }

            // Verificar que el modal exista
            const $modal = $('#tureserva-modal');
            if ($modal.length === 0) {
                console.error('❌ El modal #tureserva-modal no existe en el DOM');
                alert('Error: No se encontró el formulario de reserva. Por favor, recarga la página.');
                return;
            }

            $('#modal_alojamiento_id').val(id);
            $('#modal_alojamiento_nombre').text(nombre);
            
            // Establecer fecha mínima como hoy
            const hoy = new Date().toISOString().split('T')[0];
            $('#modal_check_in').attr('min', hoy);
            $('#modal_check_out').attr('min', hoy);
            
            // Limpiar formulario
            $('#tureserva-crear-reserva-form')[0].reset();
            $('#modal_alojamiento_id').val(id);
            $('#modal_alojamiento_nombre').text(nombre);
            
            // Establecer valores por defecto
            $('#modal_adults').val('2');
            $('#modal_children').val('0');
            
            console.log('✅ Mostrando modal...');
            $modal.css('display', 'block').fadeIn(300);
            console.log('✅ Modal visible:', $modal.is(':visible'));
        });

        // Validar que check_out sea posterior a check_in
        $(document).on('change', '#modal_check_in', function() {
            const checkIn = $(this).val();
            if (checkIn) {
                const fechaCheckIn = new Date(checkIn);
                fechaCheckIn.setDate(fechaCheckIn.getDate() + 1);
                const fechaMinima = fechaCheckIn.toISOString().split('T')[0];
                $('#modal_check_out').attr('min', fechaMinima);
                
                // Si check_out es anterior o igual a check_in, limpiarlo
                const checkOut = $('#modal_check_out').val();
                if (checkOut && checkOut <= checkIn) {
                    $('#modal_check_out').val('');
                }
            }
        });

        // 🔴 Cerrar modal
        $(document).on('click', '.close-modal, .cancel-modal', function (e) {
            e.preventDefault();
            console.log('🔴 Cerrando modal...');
            $('#tureserva-modal').fadeOut(300);
        });

        // 💾 Enviar formulario de creación de reserva
        $(document).on('submit', '#tureserva-crear-reserva-form', function (e) {
            e.preventDefault();
            console.log('📤 Enviando formulario de reserva...');

            const data = {
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

            // Validar fechas
            if (!data.check_in || !data.check_out) {
                alert('Por favor, completa las fechas de llegada y salida.');
                return;
            }

            if (new Date(data.check_out) <= new Date(data.check_in)) {
                alert('La fecha de salida debe ser posterior a la fecha de llegada.');
                return;
            }

            // Recoger servicios seleccionados
            $('input[name="servicios[]"]:checked').each(function () {
                data.servicios.push($(this).val());
            });

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text('Procesando...');

            console.log('📤 Enviando datos de reserva:', data);

            $.post(TuReservaAddReserva.ajax_url, data, function (response) {
                console.log('📥 Respuesta recibida:', response);
                
                if (response.success) {
                    alert(response.data.message || 'Reserva creada exitosamente.');
                    window.location.href = response.data.redirect || 'edit.php?post_type=tureserva_reserva';
                } else {
                    alert('Error: ' + (response.data || 'Error desconocido'));
                    $btn.prop('disabled', false).text('Confirmar Reserva');
                }
            }).fail(function(xhr, status, error) {
                console.error('❌ Error AJAX:', {xhr, status, error, response: xhr.responseText});
                alert('Error de conexión. Por favor, intenta nuevamente.');
                $btn.prop('disabled', false).text('Confirmar Reserva');
            });
        });

        // Cerrar modal si se hace clic fuera
        $(document).on('click', '#tureserva-modal', function (e) {
            if ($(e.target).is('#tureserva-modal')) {
                console.log('🔴 Click fuera del modal, cerrando...');
                $('#tureserva-modal').fadeOut(300);
            }
        });
        
        console.log('✅ Todos los eventos inicializados correctamente');
        
        // Verificación final
        console.log('🔍 Verificación final:');
        console.log('  - jQuery disponible:', typeof $ !== 'undefined');
        console.log('  - TuReservaAddReserva:', typeof TuReservaAddReserva !== 'undefined' ? TuReservaAddReserva : 'NO DISPONIBLE');
        console.log('  - Modal existe:', $('#tureserva-modal').length > 0);
        console.log('  - Botones Reservar:', $('.crear-reserva').length);
    });
})(jQuery);
