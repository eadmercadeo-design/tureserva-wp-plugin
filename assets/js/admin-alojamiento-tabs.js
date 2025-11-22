jQuery(document).ready(function ($) {
    // 🟢 Inicializar Tabs
    $('.tureserva-tabs-nav li a').on('click', function (e) {
        e.preventDefault();

        // Remover clases activas
        $('.tureserva-tabs-nav li').removeClass('active');
        $('.tureserva-tab-panel').removeClass('active');

        // Activar tab actual
        $(this).parent().addClass('active');
        const target = $(this).attr('href');
        $(target).addClass('active');
    });

    // 🖼️ Galería de Imágenes
    let galleryFrame;
    $('#tureserva-add-gallery').on('click', function (e) {
        e.preventDefault();

        if (galleryFrame) {
            galleryFrame.open();
            return;
        }

        galleryFrame = wp.media({
            title: 'Seleccionar imágenes para la galería',
            button: { text: 'Usar estas imágenes' },
            multiple: true
        });

        galleryFrame.on('select', function () {
            const selection = galleryFrame.state().get('selection');
            const container = $('.tureserva-gallery-preview');
            const input = $('#tureserva_galeria_ids');

            let ids = input.val() ? input.val().split(',') : [];

            selection.map(function (attachment) {
                attachment = attachment.toJSON();

                if (!ids.includes(attachment.id.toString())) {
                    ids.push(attachment.id);
                    container.append(`
                        <div class="tureserva-gallery-item" data-id="${attachment.id}">
                            <img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" />
                            <span class="tureserva-gallery-remove">&times;</span>
                        </div>
                    `);
                }
            });

            input.val(ids.join(','));
        });

        galleryFrame.open();
    });

    // ❌ Eliminar imagen de galería
    $(document).on('click', '.tureserva-gallery-remove', function () {
        const item = $(this).parent();
        const id = item.data('id').toString();
        const input = $('#tureserva_galeria_ids');

        let ids = input.val().split(',');
        ids = ids.filter(i => i !== id);

        input.val(ids.join(','));
        item.remove();
    });

    // 🌟 Imagen Destacada Custom
    let featuredFrame;
    $(document).on('click', '#tureserva-set-featured', function (e) {
        e.preventDefault();
        if (featuredFrame) { featuredFrame.open(); return; }

        featuredFrame = wp.media({
            title: 'Seleccionar Imagen Destacada',
            button: { text: 'Establecer imagen destacada' },
            multiple: false
        });

        featuredFrame.on('select', function () {
            const attachment = featuredFrame.state().get('selection').first().toJSON();
            $('#_thumbnail_id').val(attachment.id);
            $('#tureserva-featured-image-wrapper').html(`
                <img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" style="max-width:100%; height:auto; border-radius:4px; border:1px solid #ddd;">
                <br><a href="#" id="tureserva-remove-featured">Quitar imagen</a>
            `);
        });

        featuredFrame.open();
    });

    $(document).on('click', '#tureserva-remove-featured', function (e) {
        e.preventDefault();
        $('#_thumbnail_id').val('');
        $('#tureserva-featured-image-wrapper').html('<button type="button" id="tureserva-set-featured" class="button">Establecer imagen</button>');
    });
});
