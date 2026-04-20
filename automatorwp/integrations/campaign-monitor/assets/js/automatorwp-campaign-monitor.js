(function( $ ) {

    var prefix = 'automatorwp-campaign-monitor-';
    var _prefix = 'automatorwp_campaign_monitor_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var client_id = $('#' + _prefix + 'client_id').val();
        var api_key = $('#' + _prefix + 'api_key').val();

        var response_wrap = wrapper.find('#' + _prefix + 'response');
        if (!response_wrap.length) {
            wrapper.append('<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>');
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        if (client_id.length === 0 || api_key.length === 0) {
            response_wrap.addClass('automatorwp-notice-error');
            response_wrap.html('Client ID and API Key are required to connect with Campaign Monitor');
            response_wrap.slideDown('fast');
            return;
        }

        response_wrap.slideUp('fast');
        response_wrap.attr('class', '');

        // Show spinner
        wrapper.append('<span class="spinner is-active" style="float: none;"></span>');

        // Disable button
        button.prop('disabled', true);

        $.post(
            ajaxurl,
            {
                action: 'automatorwp_campaign_monitor_authorize',
                nonce: automatorwp_campaign_monitor.nonce,
                client_id: client_id,
                api_key: api_key
            },

            function( response ) {

                response_wrap.addClass( 'automatorwp-notice-' + ( response.success === true ? 'success' : 'error' ) );
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown('fast');

                // Hide spinner
                wrapper.find('.spinner').remove();

                // Redirect on success
                if( response.success === true && response.data.redirect_url !== undefined ) {
                    window.location = response.data.redirect_url;
                    return;
                }
                
                // Enable button
                button.prop('disabled', false);

            }
        );
 
    });


})( jQuery );
