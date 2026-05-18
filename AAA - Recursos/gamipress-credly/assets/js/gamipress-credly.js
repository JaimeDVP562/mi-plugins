(function( $ ) {

    // Prevent the form submission from pressing the enter key

    $('body').on( 'submit', '.gamipress-credly-form', function(e) {
        e.preventDefault();

        return false;
    });

    $('body').on( 'keypress', '.gamipress-credly-form', function(e) {
        return e.keyCode != 13;
    });

    // Handle the login form submission through clicking the submit button

    $('body').on( 'click', '.gamipress-credly-form .gamipress-credly-form-submit-button', function(e) {
        e.preventDefault();

        var $this               = $(this);
        var form                = $(this).closest('.gamipress-credly-form');
        var submit_wrap         = form.find('.gamipress-credly-form-submit');
        var email               = form.find('input[name="email"]').val();

        // Ensure response wrap
        if( submit_wrap.find('.gamipress-credly-form-response').length === 0 ) {
            submit_wrap.prepend('<div class="gamipress-credly-form-response" style="display: none;"></div>')
        }

        var response_wrap = submit_wrap.find('.gamipress-credly-form-response');

        // Check the email
        if( ! email.length ) {
            response_wrap.addClass( 'gamipress-credly-error' );
            response_wrap.html( gamipress_credly.email_error );
            response_wrap.slideDown();
            return;
        }

        if( ! /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(email).toLowerCase()) ) {
            response_wrap.addClass( 'gamipress-credly-error' );
            response_wrap.html( gamipress_credly.email_error );
            response_wrap.slideDown();
            return;
        }

        // Disable the submit button
        $this.prop( 'disabled', true );

        // Hide previous notices
        if( response_wrap.length ) {
            response_wrap.slideUp()
        }

        // Show the loading spinner
        submit_wrap.find( '.gamipress-spinner' ).show();

        /**
         * Event before perform a login request
         * Example:  $('body').on( 'gamipress_credly_before_login_request', '.gamipress-credly-form', function(e) {});
         *
         * @since 1.0.0
         *
         * @selector    .gamipress-credly-form
         * @event       gamipress_credly_before_login_request
         */
        form.trigger( 'gamipress_credly_before_login_request' );

        $.ajax({
            url: gamipress_credly.ajaxurl,
            method: 'POST',
            data: form.serialize() + '&action=gamipress_credly_login',
            success: function( response ) {

                // Add class gamipress-credly-success on successful login, if not will add the class gamipress-credly-error
                response_wrap.addClass( 'gamipress-credly-' + ( response.success === true ? 'success' : 'error' ) );

                // Update and show response messages
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown();

                // Restore submit button
                $this.prop( 'disabled', false );

                // Hide the loading spinner
                submit_wrap.find( '.gamipress-spinner' ).hide();

                if( response.success === true ) {
                    // Hide the email part
                    form.find('#gamipress-credly-login-form-email').slideUp('fast', function() {
                       $(this).remove();
                    });

                    // Hide the button
                    $this.slideUp('fast', function() {
                        $(this).remove();
                    });
                }

                /**
                 * Triggers 'gamipress_credly_login_success' on success and 'gamipress_credly_login_error' on error
                 *
                 * @since 1.0.0
                 *
                 * @selector    .gamipress-credly-form
                 * @event       gamipress_credly_login_success|gamipress_credly_login_error
                 */
                form.trigger( 'gamipress_credly_login_' + ( response.success === true ? 'success' : 'error' ) );

                /**
                 * Event after perform a login request
                 *
                 * @since 1.0.0
                 *
                 * @selector    .gamipress-credly-form
                 * @event       gamipress_credly_after_login_request
                 */
                form.trigger( 'gamipress_credly_after_login_request' );

            },
            error: function( response ) {

                /**
                 * Triggers login error
                 *
                 * @since 1.0.0
                 *
                 * @selector    .gamipress-credly-form
                 * @event       gamipress_credly_login_error
                 */
                form.trigger( 'gamipress_credly_login_error' );

                /**
                 * Event after perform a login request
                 *
                 * @since 1.0.0
                 *
                 * @selector    .gamipress-credly-form
                 * @event       gamipress_credly_after_login_request
                 */
                form.trigger( 'gamipress_credly_after_login_request' );

            }
        });
    });

})( jQuery );