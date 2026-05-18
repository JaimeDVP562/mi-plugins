(function($) {

    var prefix = 'automatorwp-gohighlevel-';
    var _prefix = 'automatorwp_gohighlevel_';

    if (window.automatorwpGoHighLevelBound) {
        return;
    }
    window.automatorwpGoHighLevelBound = true;

    function getNonce() {
        if (typeof automatorwp_gohighlevel !== 'undefined' && automatorwp_gohighlevel.nonce) {
            return automatorwp_gohighlevel.nonce;
        }

        if (typeof automatorwp !== 'undefined' && automatorwp.nonce) {
            return automatorwp.nonce;
        }

        return '';
    }

    function getResponseWrap(wrapper) {
        var responseWrap = wrapper.find('#' + _prefix + 'response');

        if (!responseWrap.length) {
            wrapper.append('<div id="' + _prefix + 'response" style="display:none;margin-top:10px;"></div>');
            responseWrap = wrapper.find('#' + _prefix + 'response');
        }

        return responseWrap;
    }

    function showResponse(responseWrap, type, message) {
        responseWrap.attr('class', '').addClass('automatorwp-notice-' + type);
        responseWrap.html(message).slideDown('fast');
    }

    function getCredentials() {
        return {
            api_key: $('#' + _prefix + 'api_key').val() || '',
            location_id: $('#' + _prefix + 'location_id').val() || '',
            webhook_token: $('#' + _prefix + 'webhook_token').val() || ''
        };
    }

    // Compatibility with integrations that use an "authorize" button field.
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var responseWrap = getResponseWrap(wrapper);
        var credentials = getCredentials();

        if (credentials.api_key.length === 0) {
            showResponse(responseWrap, 'error', 'Access token is required to connect with GoHighLevel');
            return;
        }

        responseWrap.slideUp('fast').attr('class', '');
        wrapper.append('<span class="spinner is-active" style="float:none;"></span>');
        button.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'automatorwp_gohighlevel_authorize',
            nonce: getNonce(),
            api_key: credentials.api_key,
            location_id: credentials.location_id,
            webhook_token: credentials.webhook_token
        }, function(response) {
            showResponse(
                responseWrap,
                response && response.success === true ? 'success' : 'error',
                response && response.data && response.data.message ? response.data.message : (response && response.data ? response.data : '')
            );

            wrapper.find('.spinner').remove();

            if (response && response.success === true && response.data && response.data.redirect_url) {
                window.location = response.data.redirect_url;
                return;
            }

            button.prop('disabled', false);
        });
    });

    // Save credentials button from current admin UI.
    $('body').on('click', 'input[name="automatorwp_save_gohighlevel_oauth"]', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var responseWrap = getResponseWrap(wrapper);
        var credentials = getCredentials();

        if (credentials.api_key.length === 0) {
            showResponse(responseWrap, 'error', 'Access token is missing');
            return;
        }

        responseWrap.slideUp('fast').attr('class', '');
        wrapper.append('<span class="spinner is-active" style="float:none;"></span>');
        button.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'automatorwp_gohighlevel_save_oauth_credentials',
            nonce: getNonce(),
            api_key: credentials.api_key,
            location_id: credentials.location_id,
            webhook_token: credentials.webhook_token
        }, function(response) {
            showResponse(
                responseWrap,
                response && response.success === true ? 'success' : 'error',
                response && response.data && response.data.message ? response.data.message : (response && response.data ? response.data : '')
            );

            wrapper.find('.spinner').remove();
            button.prop('disabled', false);
        });
    });

    // Delete credentials button from current admin UI.
    $('body').on('click', '#automatorwp_remove_gohighlevel_oauth', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var responseWrap = getResponseWrap(wrapper);

        responseWrap.slideUp('fast').attr('class', '');
        wrapper.append('<span class="spinner is-active" style="float:none;"></span>');
        button.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'automatorwp_gohighlevel_delete_oauth_credentials',
            nonce: getNonce()
        }, function(response) {
            showResponse(
                responseWrap,
                response && response.success === true ? 'success' : 'error',
                response && response.data && response.data.message ? response.data.message : (response && response.data ? response.data : '')
            );

            if (response && response.success === true) {
                $('#' + _prefix + 'api_key').val('');
                $('#' + _prefix + 'location_id').val('');
                $('#' + _prefix + 'webhook_token').val('');
            }

            wrapper.find('.spinner').remove();
            button.prop('disabled', false);
        });
    });

})(jQuery);
