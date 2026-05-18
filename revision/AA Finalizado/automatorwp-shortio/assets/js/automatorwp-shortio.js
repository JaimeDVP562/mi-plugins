(function( $ ) {

    var prefix = 'automatorwp-shortio-';
    var _prefix = 'automatorwp_shortio_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var api_key = $('#' + _prefix + 'api_key').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }
    
        // Show error message if not correctly configured
        if( api_key === undefined || api_key.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'API key is required to connect with Short.io' );
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
                action: 'automatorwp_shortio_authorize',
                nonce: automatorwp_shortio.nonce,
                api_key: api_key,
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

    // On change domain
    $('body').on('change', '.automatorwp-action-shortio-redirect-to-link .cmb2-id-domain select, '
    + '.automatorwp-action-shortio-delete-link .cmb2-id-domain select', function(e) {
    var domain = $(this).closest('.cmb-row');
    var link_domain = domain.next('.cmb2-id-link');

    var domain_id = $(this).val();
    var first_change = domain.hasClass('is-option-change');
		
    if( domain_id === 'any' || domain_id === '' || domain_id === null ) {
        // Hide the term selector
        if( first_change ) {
            link_domain.hide();
        } else {
            link_domain.slideUp('fast');
        }
    } else {
        var link_selector = link_domain.find('select.select2-hidden-accessible');

        // Remove Select2 element
        link_selector.next('.select2').remove();

        // Update the domain (since we do not use the table attribute, lets to use it as domain)
        link_selector.data( 'table', domain_id );

        // Reset the selector
        link_selector.removeAttr('data-select2-id');

        // Init it again
        automatorwp_ajax_selector( link_selector );

        // Show the term selector
        if( first_change ) {
            link_domain.show();
        } else {
            link_domain.slideDown('fast');
        }
    }

    });

    // On click on an option, check if form contains the domain selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

    var item = $(this).closest('.automatorwp-automation-item');
    var option = $(this).data('option');
    var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
    var domain_selector = option_form.find('.cmb2-id-domain');
				
    if( domain_selector !== undefined ) {
        domain_selector.addClass('is-option-change');
        domain_selector.find('select.select2-hidden-accessible').trigger('change');
    }

    });
})( jQuery );