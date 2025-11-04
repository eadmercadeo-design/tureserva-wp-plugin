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

    $(document).on('click', '.crear-reserva', function () {
        alert('🧾 Crear reserva para alojamiento ID: ' + $(this).data('id'));
        // Aquí puedes abrir un modal o redirigir al editor del CPT "reserva"
    });
});
