(function( $ ) {

    function gamipress_sc_points_gateway_update_checkout() {

        var payment_method = $('.sc-checkout input[type="radio"]:checked').val();

        if( payment_method !== undefined && payment_method.startsWith('gamipress_') ) {

            var points_type = payment_method.replace( 'gamipress_', '' );

            // Hide previously active gateway
            $('.gamipress-sc-points-gateway-active').removeClass('gamipress-sc-points-gateway-active').hide();

            // Show current active gateway
            $('#payment-method-gamipress-' + points_type + '-user-balance-wrap').addClass('gamipress-sc-points-gateway-active').show();
            $('#payment-method-gamipress-' + points_type + '-required-balance-wrap').addClass('gamipress-sc-points-gateway-active').show();
            $('#payment-method-gamipress-' + points_type + '-new-balance-wrap').addClass('gamipress-sc-points-gateway-active').show();

        } else {
            // Hide previously active gateway
            $('.gamipress-sc-points-gateway-active').removeClass('gamipress-sc-points-gateway-active').hide();
        }
    }

    $( 'body' ).on( 'change', '.sc-checkout input[type="radio"]', function() {
        gamipress_sc_points_gateway_update_checkout();
    });

    // Trigger on page load
    if( $('.sc-checkout input[type="radio"]:checked').length )
        gamipress_sc_points_gateway_update_checkout();

    // After SureCart updates checkout
    $( 'body' ).on( 'surecart:checkout_updated', function() {
        gamipress_sc_points_gateway_update_checkout();
    });

})(jQuery);