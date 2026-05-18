(function($) {

    var prefix = 'automatorwp-surecontact-';
    var fieldPrefix = '_';

    // Botón para probar la API Key
    $('body').on('click', '.automatorwp_settings #' + fieldPrefix + 'test_connection', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var apiKey = $('#' + fieldPrefix + 'api_key').val();

        // Contenedor del mensaje
        var responseBox = wrapper.find('#' + fieldPrefix + 'response');

        if (!responseBox.length) {
            wrapper.append('<div id="' + fieldPrefix + 'response" style="display:none; margin-top:10px;"></div>');
            responseBox = wrapper.find('#' + fieldPrefix + 'response');
        }

        // Validación básica
        if (apiKey.length === 0) {
            responseBox.attr('class', 'automatorwp-notice-error');
            responseBox.html('Debes introducir una API Key para probar la conexión.');
            responseBox.slideDown('fast');
            return;
        }

        responseBox.slideUp('fast');
        responseBox.attr('class', '');

        // Spinner
        wrapper.append('<span class="spinner is-active" style="float:none;"></span>');
        button.prop('disabled', true);

        // Petición AJAX
        $.post(
            ajaxurl,
            {
                action: 'automatorwp_surecontact_test_connection',
                nonce: AutomatorWP_SureContact.nonce,
                api_key: apiKey
            },
            function(response) {

                var type = response.success ? 'success' : 'error';
                responseBox.attr('class', 'automatorwp-notice-' + type);

                if (response.data && response.data.message) {
                    responseBox.html(response.data.message);
                } else {
                    responseBox.html('No se pudo comprobar la conexión.');
                }

                responseBox.slideDown('fast');

                wrapper.find('.spinner').remove();
                button.prop('disabled', false);
            }
        );
    });

})(jQuery);