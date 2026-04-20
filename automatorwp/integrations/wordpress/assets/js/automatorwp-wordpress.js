(function ( $ ) {
    
    // On change taxonomy
    $('body').on('change', '.automatorwp-action-posts-create-post .cmb2-id-taxonomy select, '
        + '.automatorwp-action-posts-update-post .cmb2-id-taxonomy select, '
        + '.automatorwp-action-posts-update-multiple-posts .cmb2-id-taxonomy select', function(e) {
        var taxonomy = $(this).closest('.cmb-row');
        var taxonomy_term = taxonomy.next('.cmb2-id-post-terms');

        var taxonomy_id = $(this).val();

        var first_change = taxonomy.hasClass('is-option-change');

        if( taxonomy_id === 'any' || taxonomy_id === '' ) {
            // Hide the term selector
            if( first_change ) {
                taxonomy_term.hide();
            } else {
                taxonomy_term.slideUp('fast');
            }
        } else {
            var term_selector = taxonomy_term.find('select.select2-hidden-accessible');

            // Remove Select2 element
            term_selector.next('.select2').remove();

            // Update the taxonomy (since we do not use the table attribute, lets to use it as taxonomy)
            term_selector.data( 'table', taxonomy_id );

            // Reset the selector
            term_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( term_selector );

            // Show the term selector
            if( first_change ) {
                taxonomy_term.show();
            } else {
                taxonomy_term.slideDown('fast');
            }
        }

        taxonomy.removeClass('is-option-change');
    });

    // On click on an option, check if form contains the taxonomy selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        var taxonomy_selector = option_form.find('.cmb2-id-taxonomy');

        if( taxonomy_selector !== undefined ) {
            taxonomy_selector.addClass('is-option-change');
            taxonomy_selector.find('select.select2-hidden-accessible').trigger('change');
        }

    });

})( jQuery );