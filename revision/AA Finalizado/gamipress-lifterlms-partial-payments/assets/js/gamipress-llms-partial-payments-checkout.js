jQuery(document).ready(function ($) {

    // ── MOSTRAR/OCULTAR EL FORMULARIO ──────────────────────
    $(document).on('click', '.gamipress-llms-pp-toggle a', function (e) {
        e.preventDefault();
        $('.gamipress-llms-pp-form').slideToggle(300);
    });

    // ── PREVIEW EN TIEMPO REAL ──────────────────────────────
    $(document).on('input change', '.gamipress-llms-pp-points', function () {
        var puntos      = parseInt($(this).val()) || 0;
        var points_type = $('[name="points_type"]').val();

        var pt_data = gamipress_llms_pp.points_types[points_type];
        if (!pt_data || !pt_data.conversion) return;

        var tasa      = pt_data.conversion.money / pt_data.conversion.points;
        var descuento = (puntos * tasa).toFixed(2);

        $('.gamipress-llms-pp-preview-points').text(puntos);
        $('.gamipress-llms-pp-preview-money').text(gamipress_llms_pp.currency_symbol + descuento);
    });

    // ── APLICAR DESCUENTO ────────────────────────────────────
    $(document).on('submit', '.gamipress-llms-pp-form', function (e) {
        e.preventDefault();

        var $form    = $(this);
        var $btn     = $form.find('#gamipress-llms-pp-btn');
        var $notices = $('.gamipress-llms-pp-notices');
        var puntos   = parseInt($form.find('.gamipress-llms-pp-points').val()) || 0;
        var points_type = $form.find('[name="points_type"]').val();

        $btn.prop('disabled', true).text('Aplicando...');
        $notices.html('');

        var data = $form.serialize();
        data += '&action=' + gamipress_llms_pp.apply_action;
        data += '&nonce='  + gamipress_llms_pp.nonce;

        $.post(gamipress_llms_pp.ajaxurl, data, function (response) {
            if (response.success) {
                // Calculamos el descuento para actualizar el resumen visualmente
                var pt_data   = gamipress_llms_pp.points_types[points_type];
                var tasa      = pt_data.conversion.money / pt_data.conversion.points;
                var descuento = (puntos * tasa).toFixed(2);

                // Actualizamos el precio en el resumen del pedido de LifterLMS
                var $terminos = $('*:contains("Términos:")').filter(function() {
                    return $(this).children().length === 0 || $(this).find('strong').length > 0;
                });

                // Buscamos el precio en el resumen y lo actualizamos
                $('.llms-checkout-section-title, .llms-order-summary').each(function() {
                    var html = $(this).html();
                    if (html && html.indexOf('$') !== -1) {
                        // Recargamos la página para mostrar el precio actualizado
                    }
                });

                // Mostramos mensaje de éxito con el descuento aplicado
                $notices.html(
                    '<p style="color:green;font-weight:bold;padding:8px;background:#f0fff0;border:1px solid green;margin:8px 0;">' +
                    '✓ Descuento de ' + gamipress_llms_pp.currency_symbol + descuento + ' aplicado correctamente. Actualizando total...' +
                    '</p>'
                );

                $form.slideUp(300);

                // Recargamos la página después de 1.5 segundos para mostrar el precio actualizado
                setTimeout(function() {
                    location.reload();
                }, 1500);

            } else {
                $notices.html(
                    '<p style="color:red;padding:8px;background:#fff0f0;border:1px solid red;margin:8px 0;">' +
                    '✗ ' + response.data + '</p>'
                );
                $btn.prop('disabled', false).text('Aplicar descuento');
            }
        });
    });

    // ── QUITAR DESCUENTO ─────────────────────────────────────
    $(document).on('click', '.gamipress-llms-pp-remove', function (e) {
        e.preventDefault();

        var points_type = $(this).data('points-type');
        var $notices    = $('.gamipress-llms-pp-notices');

        $.post(gamipress_llms_pp.ajaxurl, {
            action:      gamipress_llms_pp.remove_action,
            nonce:       gamipress_llms_pp.nonce,
            points_type: points_type
        }, function (response) {
            if (response.success) {
                setTimeout(function() {
                    location.reload();
                }, 500);
            }
        });
    });

});
