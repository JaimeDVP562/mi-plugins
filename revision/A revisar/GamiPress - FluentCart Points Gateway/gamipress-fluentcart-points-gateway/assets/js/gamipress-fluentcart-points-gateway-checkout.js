(function( $ ) {

    'use strict';

    // Check if our localized data exists
    if ( typeof gamipress_fc_points_gateway === 'undefined' ) {
        return;
    }

    var pointsData = gamipress_fc_points_gateway.points_data || {};
    var i18n = gamipress_fc_points_gateway.i18n || {};

    /**
     * Update the points balance display when a GamiPress points gateway is selected
     */
    function updatePointsDisplay() {

        // Find the currently selected payment method in FluentCart's checkout
        var $selectedPayment = $('input[name="payment_method"]:checked, .fc-payment-method.selected, .fc-payment-method-radio:checked');

        if ( ! $selectedPayment.length ) {
            hidePointsInfo();
            return;
        }

        var paymentMethod = $selectedPayment.val() || $selectedPayment.data('method') || '';

        if ( paymentMethod && paymentMethod.indexOf('gamipress_') === 0 ) {

            var pointsType = paymentMethod.replace('gamipress_', '');

            if ( pointsData[pointsType] ) {
                showPointsInfo(pointsType, pointsData[pointsType]);
            }
        } else {
            hidePointsInfo();
        }
    }

    /**
     * Show points balance information
     *
     * @param {string} pointsType
     * @param {object} data
     */
    function showPointsInfo( pointsType, data ) {

        var $container = getOrCreateContainer();
        var pluralName = data.plural_name || 'Points';

        // Calculate required points based on cart total
        var cartTotal = getCartTotal();
        var requiredPoints = Math.ceil( cartTotal * data.conversion_rate );
        var balanceAfter = data.user_points - requiredPoints;

        // Build the display
        var html = '<div class="gamipress-fc-points-info gamipress-fc-points-info-' + pointsType + '">';

        // Current balance
        html += '<div class="gamipress-fc-points-row">';
        html += '<span class="gamipress-fc-points-label">' + i18n.current_balance.replace('%s', pluralName) + '</span>';
        html += '<span class="gamipress-fc-points-value">' + data.user_points + '</span>';
        html += '</div>';

        // Required points
        html += '<div class="gamipress-fc-points-row">';
        html += '<span class="gamipress-fc-points-label">' + i18n.required_points.replace('%s', pluralName) + '</span>';
        html += '<span class="gamipress-fc-points-value">' + requiredPoints + '</span>';
        html += '</div>';

        // Balance after purchase
        html += '<div class="gamipress-fc-points-row">';
        html += '<span class="gamipress-fc-points-label">' + i18n.balance_after.replace('%s', pluralName) + '</span>';

        if ( balanceAfter < 0 ) {
            html += '<span class="gamipress-fc-points-value gamipress-fc-points-negative">' + balanceAfter + '</span>';
        } else {
            html += '<span class="gamipress-fc-points-value">' + balanceAfter + '</span>';
        }

        html += '</div>';

        // Insufficient points warning
        if ( balanceAfter < 0 ) {
            html += '<div class="gamipress-fc-points-warning">';
            html += '<span>' + i18n.insufficient.replace('%s', pluralName) + '</span>';
            html += '</div>';
        }

        html += '</div>';

        $container.html(html).show();
    }

    /**
     * Hide the points information container
     */
    function hidePointsInfo() {
        var $container = $('.gamipress-fc-points-container');
        if ( $container.length ) {
            $container.hide();
        }
    }

    /**
     * Get or create the points info container
     *
     * @return {jQuery}
     */
    function getOrCreateContainer() {

        var $container = $('.gamipress-fc-points-container');

        if ( ! $container.length ) {
            $container = $('<div class="gamipress-fc-points-container"></div>');

            // Try to insert it after the order summary / payment methods
            var $target = $('.fc-checkout-summary, .fc-order-summary, .fc-payment-methods').last();

            if ( $target.length ) {
                $target.after($container);
            } else {
                // Fallback: append to the checkout form
                var $checkout = $('.fc-checkout, .fluent-cart-checkout').first();
                if ( $checkout.length ) {
                    $checkout.append($container);
                }
            }
        }

        return $container;
    }

    /**
     * Get the current cart total
     *
     * @return {float}
     */
    function getCartTotal() {

        // Try multiple selectors for FluentCart's total display
        var totalText = '';
        var selectors = [
            '.fc-order-total .fc-price',
            '.fc-checkout-total .amount',
            '.fc-total-amount',
            '.fc-order-summary-total'
        ];

        for ( var i = 0; i < selectors.length; i++ ) {
            var $el = $(selectors[i]).last();
            if ( $el.length ) {
                totalText = $el.text();
                break;
            }
        }

        if ( ! totalText ) {
            return 0;
        }

        // Parse the price value (remove currency symbols, thousand separators)
        var price = totalText.replace(/[^0-9.,]/g, '');

        // Handle comma as decimal separator
        if ( price.indexOf(',') > price.indexOf('.') ) {
            price = price.replace('.', '').replace(',', '.');
        } else {
            price = price.replace(',', '');
        }

        return parseFloat(price) || 0;
    }

    // Watch for payment method changes
    $(document).on('change', 'input[name="payment_method"]', function() {
        updatePointsDisplay();
    });

    // Watch for FluentCart's dynamic payment method selection
    $(document).on('click', '.fc-payment-method, .fc-payment-option', function() {
        setTimeout(updatePointsDisplay, 100);
    });

    // Initial check on page load
    $(document).ready(function() {
        setTimeout(updatePointsDisplay, 500);
    });

    // Listen for FluentCart's checkout update events
    $(document).on('fluent_cart_checkout_updated fc_checkout_updated', function() {
        setTimeout(updatePointsDisplay, 200);
    });

})(jQuery);
