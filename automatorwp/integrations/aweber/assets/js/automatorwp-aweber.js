(function( $ ) {

    var prefix = 'automatorwp-aweber-';
    var _prefix = 'automatorwp_aweber_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var client_id = $('#' + _prefix + 'client_id').val();
        var client_secret = $('#' + _prefix + 'client_secret').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if( client_id.length === 0 || client_secret.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect with AWeber' );
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
                action: 'automatorwp_aweber_authorize',
                nonce: automatorwp_aweber.nonce,
                client_id: client_id,
                client_secret: client_secret,
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

    // On change account
    $('body').on('change', '.automatorwp-action-aweber-add-user-list .cmb2-id-account select, '
        + '.automatorwp-action-aweber-add-user-tag .cmb2-id-account select', function(e) {
        var account = $(this).closest('.cmb-row');
        var list_row = account.next('.cmb2-id-lists');

        var account_id = $(this).val();
        var first_change = account.hasClass('is-option-change');

        if( account_id === 'any' || account_id === '' || account_id === null ) {
            // Hide the term selector
            if( first_change ) {
                list_row.hide();
            } else {
                list_row.slideUp('fast');
            }
        } else {
            var list_selector = list_row.find('select.select2-hidden-accessible');

            // Remove Select2 element
            list_selector.next('.select2').remove();

            // Update the account (since we do not use the table attribute, lets to use it as account)
            list_selector.data( 'table', account_id );

            // Reset the selector
            list_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( list_selector );

            // Show the term selector
            if( first_change ) {
                list_row.show();
            } else {
                list_row.slideDown('fast');
            }
        }

        account.removeClass('is-option-change');
    });

    

    // On click on an option, check if form contains the account selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        var account_selector = option_form.find('.cmb2-id-account');
        
        if( account_selector !== undefined ) {
            account_selector.addClass('is-option-change');
            account_selector.find('select.select2-hidden-accessible').trigger('change');
        }

    });

})( jQuery );