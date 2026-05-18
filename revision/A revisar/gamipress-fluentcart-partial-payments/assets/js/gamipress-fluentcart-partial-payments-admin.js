(function ($) {

    'use strict';

    var prefix  = '-gamipress-fluentcart-partial-payments-';
    var _prefix = '_gamipress_fluentcart_partial_payments_';

    // Toggle conversion/initial/max fields when Enable checkbox changes
    $('#' + _prefix + 'enable').on('change', function () {

        var $target = $(
            '.cmb2-id' + prefix + 'conversion, ' +
            '.cmb2-id' + prefix + 'initial-amount, ' +
            '.cmb2-id' + prefix + 'max-amount'
        );

        if ($(this).prop('checked')) {
            $target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            $target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    // Run on load: hide fields if checkbox is unchecked
    if (!$('#' + _prefix + 'enable').prop('checked')) {
        $(
            '.cmb2-id' + prefix + 'conversion, ' +
            '.cmb2-id' + prefix + 'initial-amount, ' +
            '.cmb2-id' + prefix + 'max-amount'
        ).hide().addClass('cmb2-tab-ignore');
    }

})(jQuery);
