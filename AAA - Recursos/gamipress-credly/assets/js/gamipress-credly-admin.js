(function( $ ) {

    var prefix = 'gamipress-credly-';
    var _prefix = 'gamipress_credly_';

    // ------------------------------------------
    // Settings
    // ------------------------------------------

    // On click authorize button
    $('body').on('click', '.gamipress_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var token = $('#' + _prefix + 'token').val();
        
        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );

            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        response_wrap.slideUp('fast');
        response_wrap.attr('class', '');

        if( token.length === 0 ) {
            response_wrap.addClass( 'gamipress-notice-error' );
            response_wrap.html( 'All fields are required to connect with the Credly API' );
            response_wrap.slideDown('fast');
            return;
        }

        // Show spinner
        wrapper.append('<span class="spinner is-active" style="float: none;"></span>');

        // Disable button
        button.prop('disabled', true);

        $.post(
            ajaxurl,
            {
                action: 'gamipress_credly_authorize',
                nonce: gamipress_credly_admin.nonce,
                token: token,
                authorization : btoa( token + ':'),
            },
            function( response ) {

                // Add class gamipress-notice-success on successful unlock, if not will add the class gamipress-notice-error
                response_wrap.addClass( 'gamipress-notice-' + ( response.success === true ? 'success' : 'error' ) );
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown('fast');

                if( response.success === true ) {
                    $('#' + _prefix + 'authorization_status').html('<div style="padding: 5px 0; color: #37863e;">Connected</div>');
                } else {
                    $('#' + _prefix + 'authorization_status').html('<div style="padding: 5px 0; color: #a00;">Not Connected</div>');
                }

                // Hide spinner
                wrapper.find('.spinner').remove();

                // Enable button
                button.prop('disabled', false);

            }
        );

    });

    var import_achievements_loop;

    // On click import achievements button
    $('body').on('click', '.gamipress_settings #' + _prefix + 'import_achievements', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();
        var achievement_type = $('#' + _prefix + 'achievement_type').val();
        var post_status = $('#' + _prefix + 'post_status').val();
        

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'import_achievement_response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'import_achievement_response" style="display: none; margin-top: 10px;"></div>' );

            response_wrap = wrapper.find('#' + _prefix + 'import_achievement_response');
        }

        response_wrap.slideUp('fast');
        response_wrap.attr('class', '');

        if( achievement_type.length === 0 || post_status.length === 0 ) {
            response_wrap.addClass( 'gamipress-notice-error' );
            response_wrap.html( 'All fields are required to import achievements' );
            response_wrap.slideDown('fast');
            return;
        }

        // Show spinner
        $('<span class="spinner is-active" style="float: none;"></span>').insertBefore( response_wrap );

        // Disable button
        button.prop('disabled', true);

        import_achievements_loop = 0;

        gamipress_credly_import_achievements();

    });

    function gamipress_credly_import_achievements() {

        var button = $('#' + _prefix + 'import_achievements');
        var wrapper = button.parent();
        var achievement_type = $('#' + _prefix + 'achievement_type').val();
        var post_status = $('#' + _prefix + 'post_status').val();
        var response_wrap = wrapper.find('#' + _prefix + 'import_achievement_response');
        var token = $('#' + _prefix + 'token').val();
        import_achievements_loop += 1;

        $.post(
            ajaxurl,
            {
                action: 'gamipress_credly_import_achievements',
                nonce: gamipress_credly_admin.nonce,
                token: token,
                authorization : btoa( token + ':'),
                achievement_type: achievement_type,
                post_status: post_status,
                loop: import_achievements_loop,
            },
            function( response ) {

                if( response.success === true ) {

                    // Run again the import tool
                    if( response.data.imported !== undefined ) {

                        var imported_text = response_wrap.find( '#' + _prefix + 'imported' );

                        if( ! imported_text.length ) {
                            // Add the imported text to the response
                            response_wrap.append( '<div id="' + _prefix + 'imported">Achievements imported: <span>' + response.data.imported + '</span></div>' );
                            response_wrap.slideDown('fast');
                        } else {
                            // Update the imported items count
                            var import_count = imported_text.find('span');
                            import_count.text( parseInt( import_count.text() ) + response.data.imported );
                        }

                        // Run this again
                        gamipress_credly_import_achievements();

                        return;

                    }

                    // Import finished
                    response_wrap.append( ( response.data.message !== undefined ? response.data.message : response.data ) );

                    // Hide spinner
                    wrapper.find('.spinner').remove();

                    // Enable button
                    button.prop('disabled', false);

                } else {

                    // Add class gamipress-notice-error and show the error message
                    response_wrap.addClass( 'gamipress-notice-error' );
                    response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                    response_wrap.slideDown('fast');

                    // Hide spinner
                    wrapper.find('.spinner').remove();

                    // Enable button
                    button.prop('disabled', false);

                }

            }
        );

    }

})( jQuery );