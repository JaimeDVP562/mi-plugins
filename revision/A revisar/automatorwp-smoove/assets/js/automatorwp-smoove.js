(function( $ ) {

    var prefix = 'automatorwp-smoove-';
    var _prefix = 'automatorwp_smoove_';

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var api_key = $('#' + _prefix + 'api_key').val();
        var access_token = $('#' + _prefix + 'access_token').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if( api_key.length === 0 || access_token.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect with Smoove' );
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
                action: 'automatorwp_smoove_authorize',
                nonce: automatorwp_smoove.nonce,
                api_key: api_key,
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
 

    // On change board
$('body').on('change', '.automatorwp-action-smoove-create-card .cmb2-id-board select, '
    + '.automatorwp-action-smoove-change-card-list .cmb2-id-board select, '
    + '.automatorwp-action-smoove-delete-card .cmb2-id-board select, '
    + '.automatorwp-action-smoove-change-desc .cmb2-id-board select, '
    + '.automatorwp-action-smoove-comment-card .cmb2-id-board select, '
    + '.automatorwp-action-smoove-add-label .cmb2-id-board select, '
    + '.automatorwp-action-smoove-add-member .cmb2-id-board select, '
    + '.automatorwp-action-smoove-add-checklist-item .cmb2-id-board select', function(e, first_change) {
        var board = $(this).closest('.cmb-row'); 
        var list_board = board.next('.cmb2-id-list');
        var board_id = $(this).val();
    
        if( board_id === 'any' || board_id === '' || board_id === null ) {
            if( first_change ) {
                list_board.hide();
            } else {
                list_board.slideUp();
            }
        }else{
            var list_selector = list_board.find('select.select2-hidden-accessible');
    
            // Remove Select2 element
            list_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            list_selector.data( 'table', board_id );
    
            if( ! first_change ) {
                // Update the term value
                list_selector.val( '' );
            }
    
            // Reset the selector
            list_selector.removeAttr('data-select2-id');
    
            // Init it again
            automatorwp_ajax_selector( list_selector );
    
            // Show the term selector
            if( first_change ) {
                list_board.show();
            } else {
                list_board.slideDown('fast');
            }
        }
    })
    
    // On change list
    $('body').on('change', '.automatorwp-action-smoove-change-card-list .cmb2-id-list select, '
    + '.automatorwp-action-smoove-delete-card .cmb2-id-list select, '
    + '.automatorwp-action-smoove-change-desc .cmb2-id-list select, '
    + '.automatorwp-action-smoove-comment-card .cmb2-id-list select, '
    + '.automatorwp-action-smoove-add-label .cmb2-id-list select, '
    + '.automatorwp-action-smoove-add-member .cmb2-id-list select, '
    + '.automatorwp-action-smoove-add-checklist-item .cmb2-id-list select', function(e, first_change) {
        var list = $(this).closest('.cmb-row');
        
        var card_list = list.next('.cmb2-id-card');
    
        var list_id = $(this).val();
    
        if ( first_change === undefined ) {
            first_change = false;
        }
    
        if( list_id === 'any' || list_id === '' || list_id === null ) {
            // Hide the term selector
            if( first_change ) {
                card_list.hide();
            }else {
                card_list.slideUp('fast')
            }
        }else {
            var card_selector = card_list.find('select.select2-hidden-accessible');
    
            // Remove Select2 element
            card_selector.next('.select2').remove();
    
            // Update the space (since we do not use the table attribute, lets to use it as space)
            card_selector.data( 'table', list_id );
    
            if( ! first_change ) {
                // Update the the term value
                card_selector.val('');
            }
    
            // Reset the selector
            card_selector.removeAttr('data-select2-id');
    
            // Init it again
            automatorwp_ajax_selector( card_selector );
    
            // Show the term selector
            if( first_change ) {
                card_list.show();
            } else {
                card_list.slideDown('fast');
            }
        }
    });
    
    // On click on an option, check if form contains the board selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {
        var item = $(this).closest('.automatorwp-automation-item[class*="smoove"]');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        
        var board_selector = option_form.find('.cmb2-id-board');
    
        if( board_selector !== undefined ) {
            board_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }
    
        var list_selector = option_form.find('.cmb2-id-list');
    
        if( list_selector !== undefined ) {
            list_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }
    
        var card_selector = option_form.find('.cmb2-id-card');
    
        if( card_selector !== undefined ) {
            card_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }
    });
    
    })( jQuery );
    