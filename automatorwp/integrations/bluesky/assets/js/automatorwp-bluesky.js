(function( $ ) {

    var prefix = 'automatorwp-bluesky-';
    var _prefix = 'automatorwp_bluesky_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var user_handle = $('#' + _prefix + 'user_handle').val();
        var user_password = $('#' + _prefix + 'user_password').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if( user_handle.length === 0 || user_password.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect with Bluesky' );
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
                action: 'automatorwp_bluesky_authorize',
                nonce: automatorwp_bluesky.nonce,
                user_handle: user_handle,
                user_password: user_password,
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
})( jQuery );
