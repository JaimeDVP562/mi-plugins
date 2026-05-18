(function($) {

    var prefix = '_gamipress_llms_pp_';

    // On change enable checkbox, toggle fields visibility
    $('#' + prefix + 'enable').on('change', function() {

        var target = $( '.cmb2-id-' + prefix + 'conversion, '
            + '.cmb2-id-' + prefix + 'initial_amount, '
            + '.cmb2-id-' + prefix + 'max_amount' );

        if( $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( ! $('#' + prefix + 'enable').prop('checked') ) {
        $( '.cmb2-id-' + prefix + 'conversion, '
            + '.cmb2-id-' + prefix + 'initial_amount, '
            + '.cmb2-id-' + prefix + 'max_amount'
        ).hide().addClass('cmb2-tab-ignore');
    }

})(jQuery);