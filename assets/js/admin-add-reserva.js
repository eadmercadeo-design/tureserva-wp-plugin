jQuery(document).ready(function ($) {
    $('#tureserva-buscar-form').on('submit', function (e) {
        e.preventDefault();

        const data = {
            action: 'tureserva_find_available_rooms',
            security: TuReservaAddReserva.nonce,
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            alojamiento_type: $('#alojamiento_type').val(),
            adults: $('#adults').val(),
            children: $('#children').val(),
        };

        $('#tureserva-resultados').html('<p>🔍 Buscando disponibilidad...</p>');

        $.post(TuReservaAddReserva.ajax_url, data, function (response) {
            if (response.success) {
                if (response.data.length === 0) {
                    $('#tureserva-resultados').html('<p>No hay alojamientos disponibles.</p>');
                } else {
                    let html = '<table class="widefat"><thead><tr><th>Alojamiento</th><th>Capacidad</th><th>Precio por noche</th><th></th></tr></thead><tbody>';
                    response.data.forEach(item => {
                        html += `<tr>
                            <td>${item.nombre}</td>
                            <td>${item.capacidad || '-'}</td>
                            <td>$${item.precio || '0.00'}</td>
                            <td><button class="button crear-reserva" data-id="${item.id}">Reservar</button></td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    $('#tureserva-resultados').html(html);
                }
            } else {
                $('#tureserva-resultados').html('<p>Error: ' + response.data + '</p>');
            }
        });
    });

    // 🟢 Abrir modal al hacer clic en "Reservar"
    $(document).on('click', '.crear-reserva', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const nombre = $(this).closest('tr').find('td:first').text();

        $('#modal_alojamiento_id').val(id);
        $('#modal_alojamiento_nombre').text(nombre);
        $('#tureserva-modal').fadeIn();
    });

    // 🔴 Cerrar modal
    $('.close-modal').on('click', function () {
        $('#tureserva-modal').fadeOut();
    });

    // 💾 Enviar formulario de creación de reserva
    $('#tureserva-crear-reserva-form').on('submit', function (e) {
        e.preventDefault();

        const data = {
            action: 'tureserva_create_reservation',
            security: TuReservaAddReserva.nonce,
            alojamiento_id: $('#modal_alojamiento_id').val(),
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            adults: $('#adults').val(),
            children: $('#children').val(),
            cliente_nombre: $('#cliente_nombre').val(),
            cliente_email: $('#cliente_email').val(),
            cliente_telefono: $('#cliente_telefono').val(),
        };

        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Procesando...');

        $.post(TuReservaAddReserva.ajax_url, data, function (response) {
            if (response.success) {
                alert(response.data.message);
                window.location.href = response.data.redirect;
            } else {
                alert('Error: ' + response.data);
                $btn.prop('disabled', false).text('Confirmar Reserva');
            }
        });
    });

    // Cerrar modal si se hace clic fuera
    $(window).on('click', function (e) {
        if ($(e.target).is('#tureserva-modal')) {
            $('#tureserva-modal').fadeOut();
        }
    });
});
