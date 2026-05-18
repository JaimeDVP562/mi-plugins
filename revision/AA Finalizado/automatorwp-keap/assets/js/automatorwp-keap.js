(function( $ ) {

    var prefix = 'automatorwp-keap-';
    var _prefix = 'automatorwp_keap_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var access_token = $('#' + _prefix + 'access_token').val();
        // Get the specific nonce for authorization
        var nonce = $('#awp-keap-outh-ajax-nonce').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if(access_token.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect with Keap' );
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
                action: 'automatorwp_keap_save_oauth_credentials', // Updated action name
                nonce: nonce, // Use correct nonce
                access_token: access_token,
            },
            function( response ) {

                // Add class automatorwp-notice-success on successful unlock, if not will add the class automatorwp-notice-error
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

    // On click delete credentials button
    $('body').on('click', '.automatorwp_settings #automatorwp_remove_keap_oauth', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var nonce = $('#awp-keap-outh-ajax-nonce').val();

        if ( ! confirm( 'Are you sure you want to disconnect?' ) ) {
            return;
        }

        // Show spinner
        wrapper.append('<span class="spinner is-active" style="float: none;"></span>');
        button.prop('disabled', true);

        $.post(
            ajaxurl,
            {
                action: 'automatorwp_keap_delete_oauth_credentials',
                nonce: nonce
            },
            function( response ) {
                if( response.success === true && response.data.redirect_url !== undefined ) {
                    window.location = response.data.redirect_url;
                } else {
                     wrapper.find('.spinner').remove();
                     button.prop('disabled', false);
                     alert( 'Error disconnecting.' );
                }
            }
        );
    });

})( jQuery );
