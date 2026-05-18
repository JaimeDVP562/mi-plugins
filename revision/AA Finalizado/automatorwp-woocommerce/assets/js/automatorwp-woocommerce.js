(function ( $ ) {

    // Change the variable product/subscription
    $('body').on('change', '.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_variable_products"], '
        + '.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_variable_subscriptions"]', function(e) {
        var item = $(this).closest('.automatorwp-automation-item');
        var row = $(this).closest('.cmb-row');
        var variation_row = item.find('.cmb-row.cmb2-id-variation');
        var variation_target_row = item.find('.cmb-row.cmb2-id-variation-target');

        // Bail if next field is not a term selector
        if( variation_row === undefined ) {
            return;
        }

        var product_id = $(this).val();
        //var first_change = row.hasClass('is-option-change');

        if( product_id !== 'any' && product_id !== '' ) {
            var variation_selector = variation_row.find('select.select2-hidden-accessible');

            // Remove Select2 element
            variation_selector.next('.select2').remove();

            // Update the event ID
            variation_selector.data( 'product-id', product_id );

            // Reset the selector
            variation_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( variation_selector );

            if ( variation_target_row.length !== 0 ) {
                var variation_selector = variation_target_row.find('select.select2-hidden-accessible');

                // Remove Select2 element
                variation_selector.next('.select2').remove();

                // Update the event ID
                variation_selector.data( 'product-id', product_id );

                // Reset the selector
                variation_selector.removeAttr('data-select2-id');

                // Init it again
                automatorwp_ajax_selector( variation_selector );
            }
        }

        row.removeClass('is-option-change');
    });

    // Append custom data to the ajax selector
    $('body').on('automatorwp_ajax_selector_data', '.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_product_variations"]', function(e, data, element) {
        data.product_id = element.data('product-id');
    });

    // On click on an option, check if form contains the product or variation selector
    $('body').on('click', '.automatorwp-automation-item[class*="automatorwp-trigger-woocommerce"] .automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');

        // If is a product/subscription option, force change to its select
        var product_selector = option_form.find('.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_variable_products"], '
            + '.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_variable_subscriptions"]');

        if( product_selector !== undefined ) {
            product_selector.closest('.automatorwp-ajax-selector').addClass('is-option-change');
            product_selector.trigger('change');
        }

        // If is a variation option, force change to the product select
        var variation_selector = option_form.find('.automatorwp-ajax-selector select[data-action="automatorwp_woocommerce_get_product_variations"]');

        if( variation_selector !== undefined ) {
            var product_row = item.find('.cmb-row.cmb2-id-product');
            product_row.addClass('is-option-change');
            product_row.find('select').trigger('change');
        }

    });

})( jQuery );