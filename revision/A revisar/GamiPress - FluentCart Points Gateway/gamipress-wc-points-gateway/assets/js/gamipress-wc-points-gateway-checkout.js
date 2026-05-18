(function( $ ) {

    function gamipress_wc_points_gateway_update_checkout() {
        
        var payment_method = $('#payment.woocommerce-checkout-payment input.input-radio:checked').val();

        if( payment_method !== undefined && payment_method.startsWith('gamipress_') ) {

            var points_type = payment_method.replace( 'gamipress_', '' );

            // Hide previously active gateway
            $('#order_review .gamipress-wc-points-gateway-active').removeClass('gamipress-wc-points-gateway-active').hide();

            // Show current active gateway
            $('#order_review #payment-method-gamipress-' + points_type + '-user-balance-wrap').addClass('gamipress-wc-points-gateway-active').show();
            $('#order_review #payment-method-gamipress-' + points_type + '-required-balance-wrap').addClass('gamipress-wc-points-gateway-active').show();
            $('#order_review #payment-method-gamipress-' + points_type + '-new-balance-wrap').addClass('gamipress-wc-points-gateway-active').show();
        } else {
            // Hide previously active gateway
            $('#order_review .gamipress-wc-points-gateway-active').removeClass('gamipress-wc-points-gateway-active').hide();
        }
    }

    $( 'body' ).on( 'change', '#payment.woocommerce-checkout-payment input.input-radio', function() {
        gamipress_wc_points_gateway_update_checkout();
    });
    
    // Trigger a change event on checked radio on loading the page
    if( $('#payment.woocommerce-checkout-payment input.input-radio:checked').length )
        gamipress_wc_points_gateway_update_checkout();

    // After WooCommerce updates checkout trigger again the change event to keep the points view updated
    $( 'body' ).on( 'updated_checkout', function() {
        gamipress_wc_points_gateway_update_checkout();
    });

})(jQuery);