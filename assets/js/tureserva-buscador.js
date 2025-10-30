jQuery(document).ready(function ($) {

    const form = $('#tureserva-form-buscador');
    const resultados = $('#tureserva-resultados');
    const lista = $('#tureserva-lista-alojamientos');

    form.on('submit', function (e) {
        e.preventDefault();

        const check_in = $('#tureserva_check_in').val();
        const check_out = $('#tureserva_check_out').val();
        const adultos = $('#tureserva_adultos').val();
        const ninos = $('#tureserva_ninos').val();

        if (!check_in || !check_out) {
            alert('Por favor selecciona las fechas.');
            return;
        }

        lista.html('<p>Buscando alojamientos disponibles...</p>');
        resultados.show();

        $.ajax({
            url: `${tureservaBuscador.api_url}/alojamientos`,
            method: 'GET',
            success: function (alojamientos) {
                lista.empty();

                if (!alojamientos.length) {
                    lista.html('<p>No se encontraron alojamientos.</p>');
                    return;
                }

                // Revisar disponibilidad uno por uno
                alojamientos.forEach((a) => {
                    $.ajax({
                        url: `${tureservaBuscador.api_url}/disponibilidad`,
                        data: {
                            alojamiento_id: a.id,
                            check_in,
                            check_out
                        },
                        success: function (resp) {
                            if (resp.success && resp.data.disponible) {
                                lista.append(`
                                    <div class="aloj-item">
                                        <img src="${a.imagen || ''}" alt="">
                                        <div class="aloj-info">
                                            <h4>${a.titulo}</h4>
                                            <p>${a.descripcion}</p>
                                            <p><strong>Precio base:</strong> ${a.precio_base}</p>
                                            <button class="btn-reservar" 
                                                data-id="${a.id}"
                                                data-titulo="${a.titulo}"
                                                data-checkin="${check_in}"
                                                data-checkout="${check_out}"
                                                data-adultos="${adultos}"
                                                data-ninos="${ninos}"
                                            >Reservar</button>
                                        </div>
                                    </div>
                                `);
                            }
                        }
                    });
                });
            }
        });
    });

    // Crear reserva
    $('#tureserva-lista-alojamientos').on('click', '.btn-reservar', function () {
        const btn = $(this);
        const data = {
            alojamiento_id: btn.data('id'),
            check_in: btn.data('checkin'),
            check_out: btn.data('checkout'),
            adultos: btn.data('adultos'),
            ninos: btn.data('ninos'),
            nombre: prompt('Tu nombre completo:'),
            email: prompt('Tu correo electrónico:'),
            telefono: prompt('Tu teléfono (opcional):')
        };

        if (!data.nombre || !data.email) {
            alert('El nombre y correo son obligatorios.');
            return;
        }

        btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: `${tureservaBuscador.api_url}/reservar`,
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'Authorization': tureservaBuscador.token ? `Bearer ${tureservaBuscador.token}` : undefined
            },
            data: JSON.stringify(data),
            success: function (resp) {
                if (resp.success) {
                    alert('✅ Reserva enviada correctamente. Nos comunicaremos contigo para confirmar.');
                    btn.text('Reservado ✅');
                } else {
                    alert('❌ No se pudo crear la reserva.');
                    btn.prop('disabled', false).text('Reservar');
                }
            },
            error: function () {
                alert('❌ Error al conectar con el servidor.');
                btn.prop('disabled', false).text('Reservar');
            }
        });
    });
});
