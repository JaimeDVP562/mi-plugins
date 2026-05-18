
viendo (function( $ ) {

    var prefix = 'automatorwp-constant-contact-';
    var _prefix = 'automatorwp_constant_contact_';

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
            response_wrap.html( 'All fields are required to connect with Constant Contact' );
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
                action: 'automatorwp_constant_contact_authorize',
                nonce: automatorwp_constant_contact.nonce,
                client_id: client_id,
                client_secret: client_secret,
            },
            function( response ) {

                console.log(response.data.redirect_url);


                // Add class automatorwp-notice-success on successful unlock, if not will add the class automatorwp-notice-error
                response_wrap.addClass( 'automatorwp-notice-' + ( response.success === true ? 'success' : 'error' ) );
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown('fast');

                // Hide spinner
                wrapper.find('.spinner').remove();

                // Redirect on success
                if( response.success === true && response.data.redirect_url !== undefined ) {
                    window.location = response.data.url;
                    return;
                }

                // Enable button
                button.prop('disabled', false);

            }
        );

    });

    // On change spreadsheet
    $('body').on('change', '.automatorwp-action-constant-contact-add-row .cmb2-id-spreadsheet select, '
        + '.automatorwp-trigger-automatorwp-import-file .cmb2-id-csv-spreadsheet select', function(e) {
        var row = $(this).closest('.cmb-row');
        var worksheet_row = row.next('.cmb2-id-worksheet');

        var spreadsheet_id = $(this).val();
        var first_change = row.hasClass('is-option-change');

        if( spreadsheet_id === 'any' || spreadsheet_id === '' ) {
            // Hide the term selector
            if( first_change ) {
                worksheet_row.hide();
            } else {
                worksheet_row.slideUp('fast');
            }
        } else {
            var worksheet_selector = worksheet_row.find('select.select2-hidden-accessible');

            // Remove Select2 element
            worksheet_selector.next('.select2').remove();

            // Update the spreadsheet (since we do not use the table attribute, lets to use it as spreadsheet)
            worksheet_selector.data( 'table', spreadsheet_id );

            // Reset the selector
            worksheet_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( worksheet_selector );

            // Show the term selector
            if( first_change ) {
                worksheet_row.show();
            } else {
                worksheet_row.slideDown('fast');
            }
        }

        row.removeClass('is-option-change');
    });

    // On click on an option, check if form contains the spreadsheet selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        var spreadsheet_selector = option_form.find('.cmb2-id-spreadsheet');

        if( spreadsheet_selector !== undefined ) {
            spreadsheet_selector.addClass('is-option-change');
            spreadsheet_selector.find('select.select2-hidden-accessible').trigger('change');
        }

    });

})( jQuery );
