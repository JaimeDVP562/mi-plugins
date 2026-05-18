(function( $ ) {

    var prefix = 'automatorwp-slack-';
    var _prefix = 'automatorwp_slack_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
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

        // Show error message if not correctly configured
        if( token.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'API token is required to connect with Slack' );
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
                action: 'automatorwp_slack_authorize',
                nonce: automatorwp_slack.nonce,
                token: token,
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

    // On change channel for thread
    $('body').on('change', '.automatorwp-action-slack-add-comment-thread .cmb2-id-channel select'
    + ', .automatorwp-action-slack-add-comment-thread-list .cmb2-id-channel select'
    + ', .automatorwp-action-slack-add-reaction-message .cmb2-id-channel select'
   
    , function(e, first_change) {    
       
        var channel = $(this).closest('.cmb-row');
        var space_channel = channel.next('.cmb2-id-thread');
        var channel_id = $(this).val();
        
        
	    var first_change = channel.hasClass('is-option-change');
		
        if( channel_id === 'any' || channel_id === '' || channel_id === null ) {
            // Hide the term selector
            if( first_change ) {
                space_channel.hide();
            } else {
                space_channel.slideUp('fast');
            }
        } else {
            var space_selector = space_channel.find('select.select2-hidden-accessible');

            // Remove Select2 element
            space_selector.next('.select2').remove();

            // Update the channel (since we do not use the table attribute, lets to use it as channel)
            space_selector.data( 'table', channel_id );

            // Reset the selector
            space_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( space_selector );

            // Show the term selector
            if( first_change ) {
                space_channel.show();
            } else {
                space_channel.slideDown('fast');
            }
        }

    });


     // On change channel for user
    $('body').on('change', '.automatorwp-action-slack-remove-from-user-list-to-channel .cmb2-id-thread select'
    
    , function(e, first_change) {    
        
        var channel = $(this).closest('.cmb-row');
        var space_channel = channel.next('.cmb2-id-user');
        var channel_id = $(this).val();
        var first_change = channel.hasClass('is-option-change');
     
        if( channel_id === 'any' || channel_id === '' || channel_id === null ) {
 
            // Hide the term selector
            if( first_change ) {
                 space_channel.hide();
            } else {
                 space_channel.slideUp('fast');
            }
 
        } else {
             var space_selector = space_channel.find('select.select2-hidden-accessible');

            // Remove Select2 element
            space_selector.next('.select2').remove();

            // Update the channel (since we do not use the table attribute, lets to use it as channel)
            space_selector.data( 'table', channel_id );

            // Reset the selector
            space_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( space_selector );

            // Show the term selector
            if( first_change ) {
                 space_channel.show();
            } else {
                 space_channel.slideDown('fast');
            }
        }

    });
  

    // On click on an option, check if form contains the list selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        var list_selector = option_form.find('.cmb2-id-channel');

        if( list_selector !== undefined ) {
            list_selector.addClass('is-option-change');
            list_selector.find('select.select2-hidden-accessible').trigger('change');
        }

    });
    

})( jQuery );