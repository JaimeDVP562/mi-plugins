(function($) {

    var prefix = 'gamipress-sc-partial-payments-';

    // Form toggle
    $('body').on('click', '.' + prefix + 'form-toggle a', function(e) {
        e.preventDefault();

        $('.' + prefix + 'form').slideToggle();
    });

    // Range preview
    $('body').on('input', '.' + prefix + 'points', function(e) {
        var $this = $(this);

        $('#' + $this.attr('id') + '-preview').text( $this.val() );
    });

    // Points change
    $('body').on('change input', '.' + prefix + 'points', function(e) {
        gamipress_sc_partial_payments_update_preview();
    });

    // Points type change
    $('body').on('change', '#' + prefix + 'points-type', function(e) {
        var $this = $(this);
        var points_type = $this.val();

        // Hide all points types fields
        $('.' + prefix + 'points-label').hide();
        $('.' + prefix + 'points-preview').hide();
        $('.' + prefix + 'points').hide();
        $('.' + prefix + 'points-balance').hide();

        // Show current selected points type fields
        $('label[for="' + prefix + 'points-' + points_type + '"]').show();
        $('#' + prefix + 'points-' + points_type + '-preview').show();
        $('#' + prefix + 'points-' + points_type).show();
        $('#' + prefix + 'points-' + points_type + '-balance').show();

        gamipress_sc_partial_payments_update_preview();
    });

    // Submit partial payments form
    $('body').on('click', '#gamipress-sc-partial-payments button[name="apply_partial_payment"]', function(e) {
        e.preventDefault();

        var $this = $(this);
        var $form = $this.closest( '.' + prefix + 'form' );
        var notices_wrapper = $('.gamipress-sc-partial-payments-notices');

        // Block the form
        gamipress_sc_partial_payments_block( $form );

        $.ajax({
            url: gamipress_sc_partial_payments.ajaxurl,
            method: 'POST',
            data: $form.serialize()
                + '&action=gamipress_sc_partial_payments_apply_partial_payment'
                + '&nonce=' + gamipress_sc_partial_payments.nonce,
            success: function( response ) {
                // Clean up other notices
                notices_wrapper.find('.gamipress-sc-partial-payments-error, .gamipress-sc-partial-payments-success').remove();

                if( response.success === false ) {
                    // Display an error notice
                    gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-error" role="alert">' + response.data + '</div>', notices_wrapper );
                } else {
                    // Display a success notice
                    gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-success" role="alert">' + response.data.message + '</div>', notices_wrapper );

                    // Update the applied discounts section
                    gamipress_sc_partial_payments_update_applied_discounts( response.data );

                    // Hide the form after applying
                    $form.slideUp();

                    // Refresh the page to show updated form state
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function( response ) {
                // Clean up other notices
                notices_wrapper.find('.gamipress-sc-partial-payments-error, .gamipress-sc-partial-payments-success').remove();
                // Display an error notice
                gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-error" role="alert">' + 'An error occurred. Please try again.' + '</div>', notices_wrapper );
            },
            complete: function() {
                // Unblock the form
                gamipress_sc_partial_payments_unblock( $form );
            }
        });

    });

    // Click remove partial payment
    $('body').on('click', '.gamipress-sc-partial-payments-remove', function(e) {
        e.preventDefault();

        var $this = $(this);
        var notices_wrapper = $('.gamipress-sc-partial-payments-notices');

        // Block the applied item
        var $item = $this.closest('.gamipress-sc-partial-payments-applied-item');
        gamipress_sc_partial_payments_block( $item );

        $.ajax({
            url: gamipress_sc_partial_payments.ajaxurl,
            method: 'POST',
            data: {
                action: 'gamipress_sc_partial_payments_remove_partial_payment',
                nonce: gamipress_sc_partial_payments.nonce,
                points_type: $this.data('points-type')
            },
            success: function( response ) {
                // Clean up other notices
                notices_wrapper.find('.gamipress-sc-partial-payments-error, .gamipress-sc-partial-payments-success').remove();

                if( response.success === false ) {
                    // Display an error notice
                    gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-error" role="alert">' + response.data + '</div>', notices_wrapper );
                } else {
                    // Slide up and remove the applied item
                    $item.slideUp('fast', function() {
                        $(this).remove();
                    });

                    // Display a success notice
                    gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-success" role="alert">' + response.data.message + '</div>', notices_wrapper );

                    // Refresh the page to show updated form state
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function( response ) {
                // Clean up other notices
                notices_wrapper.find('.gamipress-sc-partial-payments-error, .gamipress-sc-partial-payments-success').remove();
                // Display an error notice
                gamipress_sc_partial_payments_show_notice( '<div class="gamipress-sc-partial-payments-error" role="alert">' + 'An error occurred. Please try again.' + '</div>', notices_wrapper );
            },
            complete: function() {
                // Unblock the applied item
                gamipress_sc_partial_payments_unblock( $item );
            }
        });
    });

    // -----------------------------------------
    // SC Partial Payments functions
    // -----------------------------------------

    /**
     * Update preview function
     *
     * @since 1.0.0
     */
    function gamipress_sc_partial_payments_update_preview() {
        var $form = $( '.' + prefix + 'form' );
        var points_type = $form.find('*[name="points_type"]').val();
        var points_type_label = gamipress_sc_partial_payments.points_types[points_type].plural_name;
        var points =  $form.find('*[name="' + points_type + '_points"]').val();

        // Update points preview amount
        $('.' + prefix + 'preview-points').text( points );
        $('.' + prefix + 'preview-points-type').text( points_type_label );

        // Update money amount
        var money = gamipress_sc_partial_payments_convert_to_money( points, points_type );

        var decimals           = gamipress_sc_partial_payments.decimals;
        var decimal_separator  = gamipress_sc_partial_payments.decimal_separator;
        var thousand_separator = gamipress_sc_partial_payments.thousand_separator;

        money = gamipress_sc_partial_payments_number_format( money, decimals, decimal_separator, thousand_separator );

        $('.' + prefix + 'preview-money').text( gamipress_sc_partial_payments.currency_symbol + money );
    }

    /**
     * Javascript version of the gamipress_sc_partial_payments_get_conversion() PHP function
     *
     * @since 1.0.0
     *
     * @param points_type
     *
     * @returns {boolean|*}
     */
    function gamipress_sc_partial_payments_get_conversion( points_type ) {
        if( gamipress_sc_partial_payments.points_types[points_type] === undefined )
            return false;

        points_type = gamipress_sc_partial_payments.points_types[points_type];

        return points_type['conversion'];
    }

    /**
     * Javascript version of the gamipress_sc_partial_payments_convert_to_money() PHP function
     *
     * @since 1.0.0
     *
     * @param amount
     * @param points_type
     *
     * @returns {number}
     */
    function gamipress_sc_partial_payments_convert_to_money( amount, points_type ) {
        var conversion = gamipress_sc_partial_payments_get_conversion( points_type );

        if( ! conversion ) return 0;

        var conversion_rate  = conversion['money'] / conversion['points'];

        return amount * conversion_rate;
    }

    /**
     * Javascript version of the number_format() PHP function
     *
     * @since 1.0.0
     *
     * @param number
     * @param decimals
     * @param decimal_separator
     * @param thousand_separator
     *
     * @returns {string}
     */
    function gamipress_sc_partial_payments_number_format( number, decimals, decimal_separator, thousand_separator ) {
        decimals = Math.abs(decimals);
        decimals = isNaN(decimals) ? 2 : decimals;

        var sign = number < 0 ? "-" : "";

        number = Math.abs(Number(number) || 0);

        var string_number = parseInt( number.toFixed(decimals) ).toString();
        var thousands = ( string_number.length > 3 ) ? string_number.length % 3 : 0;

        return sign
            + (thousands ? string_number.substr(0, thousands) + thousand_separator : '') + string_number.substr(thousands).replace(/(\d{3})(?=\d)/g, "$1" + thousand_separator)
            + (decimals ? decimal_separator + Math.abs(number - string_number).toFixed(decimals).slice(2) : "");
    }

    /**
     * Update the applied discounts display
     *
     * @since 1.0.0
     *
     * @param data Response data from the server
     */
    function gamipress_sc_partial_payments_update_applied_discounts( data ) {
        if( data.discount ) {
            var $applied = $('.gamipress-sc-partial-payments-applied');
            var item_html = '<div class="gamipress-sc-partial-payments-applied-item" data-points-type="' + data.discount.points_type + '">'
                + '<span class="gamipress-sc-partial-payments-applied-label">'
                + 'Discount using ' + data.discount.points_label + ': -' + data.discount.money_formatted
                + '</span> '
                + '<a href="#" class="gamipress-sc-partial-payments-remove" data-points-type="' + data.discount.points_type + '">' + gamipress_sc_partial_payments.remove_label + '</a>'
                + '</div>';

            $applied.append( item_html );
        }
    }

    // -----------------------------------------
    //  UI functions
    // -----------------------------------------

    // Block element
    function gamipress_sc_partial_payments_block( $element ) {
        $element.css({
            'opacity': '0.6',
            'pointer-events': 'none'
        });
    }

    // Unblock element
    function gamipress_sc_partial_payments_unblock( $element ) {
        $element.css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
    }

    // Show notice
    function gamipress_sc_partial_payments_show_notice( html_element, $target ) {
        if ( ! $target ) {
            $target = $( '.gamipress-sc-partial-payments-notices' );
        }
        $target.prepend( html_element );

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $target.find('.gamipress-sc-partial-payments-success, .gamipress-sc-partial-payments-error').first().fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

})(jQuery);
