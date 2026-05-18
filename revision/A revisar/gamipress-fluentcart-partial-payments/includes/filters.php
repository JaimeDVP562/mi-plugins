<?php
/**
 * Filters — GamiPress FluentCart Partial Payments
 *
 * All hooks verified against FluentCart source code:
 *
 * CONFIRMED HOOKS:
 *   fluent_cart/cart/cart_data_items_updated (action)
 *     CheckoutPageHandler.php line 67
 *     Fires on checkout page load with scope='loading'.
 *     Used to inject the partial payments form.
 *
 *   fluent_cart/order_paid_done (action)
 *     actions.php — fires after payment confirmed.
 *     Receives array('order', 'transaction', 'customer').
 *     Used to commit discount into order record.
 *
 *   fluent_cart/order_placed_offline (action)
 *     CodHandler.php line 52 — fires for Cash on Delivery orders.
 *
 *   fluent_cart/order/refunded (action)
 *   fluent_cart/order/cancelled (action)
 *     Forward compatibility hooks.
 *
 *   fluent_cart/cart/estimated_total (filter)
 *     WebCheckoutHandler.php line 358
 *     Reduces the total shown to Vue.js frontend.
 *
 * CONFIRMED FIELDS (Order.php fillable):
 *   manual_discount_total — float, for manual/custom discounts
 *   total_amount           — float, final charged amount
 *
 * CONFIRMED METHODS (Order.php):
 *   $order->getMeta($key, $default)     line 384
 *   $order->updateMeta($key, $value)    line 397
 *   $order->deleteMeta($key)            line 419
 *
 * @package GamiPress\FluentCart\Partial_Payments
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================================
// 1. CHECKOUT FORM — inject partial payments UI on checkout page load
// ==========================================================================

/**
 * Render the partial payments form when the checkout page is initialised.
 *
 * @since  1.0.0
 * @param  array $context  { cart, scope, scope_data }
 */
function gamipress_fluentcart_pp_render_form_on_checkout( $context ) {
    $scope = $context['scope'] ?? '';
    if ( $scope !== 'loading' ) {
        return;
    }
    if ( ! is_user_logged_in() ) {
        return;
    }
    gamipress_fluentcart_partial_payments_form();
}
add_action( 'fluent_cart/cart/cart_data_items_updated', 'gamipress_fluentcart_pp_render_form_on_checkout', 10, 1 );

// ==========================================================================
// 2. ORDER PAID — commit discount into Order record
// ==========================================================================

/**
 * When an order is confirmed as paid, write the points discount into
 * the order's manual_discount_total field and reduce total_amount.
 *
 * @since  1.0.0
 * @param  array $event_data
 */
function gamipress_fluentcart_pp_on_order_paid( $event_data ) {

    if ( ! is_array( $event_data ) ) {
        return;
    }

    $order = $event_data['order'] ?? null;

    if ( ! $order ) {
        return;
    }

    $user_id = absint( $order->user_id ?? 0 );

    if ( ! $user_id && ! empty( $order->customer_id ) ) {
        $customer = $order->customer ?? null;
        if ( $customer ) {
            $user_id = absint( $customer->user_id ?? 0 );
        }
    }

    if ( ! $user_id ) {
        return;
    }

    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments_by_user( $user_id );

    if ( empty( $partial_payments ) ) {
        gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id );
        return;
    }

    $total_discount_cents = 0;
    foreach ( $partial_payments as $points_type => $data ) {
        $total_discount_cents += absint( $data['money_cents'] ?? 0 );
    }

    if ( $total_discount_cents <= 0 ) {
        gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id );
        return;
    }

    $total_discount = gamipress_fluentcart_partial_payments_cents_to_decimal( $total_discount_cents );

    $existing = floatval( $order->manual_discount_total ?? 0 );
    $order->manual_discount_total = $existing + $total_discount;
    $order->total_amount = max( 0.0, floatval( $order->total_amount ?? 0 ) - $total_discount );
    $order->save();

    $order->updateMeta( 'gamipress_partial_payments', wp_json_encode( $partial_payments ) );
    gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id );
}
add_action( 'fluent_cart/order_paid_done', 'gamipress_fluentcart_pp_on_order_paid', 10, 1 );

// ==========================================================================
// 3. REFUND / CANCEL — restore points to user
// ==========================================================================

/**
 * Restore points when an order is fully refunded or cancelled.
 *
 * @since  1.0.0
 * @param  mixed $payload  Order object or array with 'order' key
 */
function gamipress_fluentcart_pp_restore_on_refund( $payload ) {

    $order = ( is_array( $payload ) && isset( $payload['order'] ) )
        ? $payload['order']
        : $payload;

    if ( ! is_object( $order ) ) {
        return;
    }

    $raw = $order->getMeta( 'gamipress_partial_payments', '' );

    if ( empty( $raw ) ) {
        return;
    }

    $partial_payments = json_decode( $raw, true );

    if ( empty( $partial_payments ) || ! is_array( $partial_payments ) ) {
        return;
    }

    $user_id = absint( $order->user_id ?? 0 );
    if ( ! $user_id && ! empty( $order->customer_id ) ) {
        $customer = $order->customer ?? null;
        if ( $customer ) {
            $user_id = absint( $customer->user_id ?? 0 );
        }
    }

    if ( ! $user_id ) {
        return;
    }

    foreach ( $partial_payments as $points_type => $data ) {
        $points = absint( $data['points'] ?? 0 );
        if ( $points > 0 ) {
            gamipress_award_points_to_user( $user_id, $points, $points_type );
        }
    }

    $order->deleteMeta( 'gamipress_partial_payments' );
}
add_action( 'fluent_cart/order/refunded',  'gamipress_fluentcart_pp_restore_on_refund', 10, 1 );
add_action( 'fluent_cart/order/cancelled', 'gamipress_fluentcart_pp_restore_on_refund', 10, 1 );

// ==========================================================================
// 4. CART UPDATED — auto-remove excess discount if cart total drops
// ==========================================================================

/**
 * When the cart is updated, check that the applied discount does not
 * exceed the new cart subtotal. If it does, restore all points.
 *
 * @since  1.0.0
 * @param  array $context  { cart, scope, scope_data }
 */
function gamipress_fluentcart_pp_check_excess_on_cart_update( $context ) {

    $scope = $context['scope'] ?? '';

    if ( $scope === 'loading' || ! is_user_logged_in() ) {
        return;
    }

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments_by_user( $user_id );

    if ( empty( $partial_payments ) ) {
        return;
    }

    $cart = $context['cart'] ?? null;

    if ( ! $cart ) {
        return;
    }

    $subtotal_cents = 0;
    if ( method_exists( $cart, 'getEstimatedTotal' ) ) {
        $subtotal_cents = absint( $cart->getEstimatedTotal() );
    }

    if ( $subtotal_cents <= 0 ) {
        foreach ( $partial_payments as $points_type => $data ) {
            $points = absint( $data['points'] ?? 0 );
            if ( $points > 0 ) {
                gamipress_award_points_to_user( $user_id, $points, $points_type );
            }
        }
        gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id );
        return;
    }

    $applied_cents = 0;
    foreach ( $partial_payments as $data ) {
        $applied_cents += absint( $data['money_cents'] ?? 0 );
    }

    if ( $applied_cents <= $subtotal_cents ) {
        return;
    }

    foreach ( $partial_payments as $points_type => $data ) {
        $points = absint( $data['points'] ?? 0 );
        if ( $points > 0 ) {
            gamipress_award_points_to_user( $user_id, $points, $points_type );
        }
    }
    gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id );
}
add_action( 'fluent_cart/cart/cart_data_items_updated', 'gamipress_fluentcart_pp_check_excess_on_cart_update', 20, 1 );

// ==========================================================================
// 5. CART ESTIMATED TOTAL — reduce total shown to Vue.js frontend
// ==========================================================================

/**
 * Reduce the cart estimated total by the applied points discount.
 *
 * Hook: fluent_cart/cart/estimated_total
 * CONFIRMED: WebCheckoutHandler.php line 358
 *
 * @since  1.0.0
 * @param  int   $total    Cart total in cents.
 * @param  array $context  Contains 'cart' object.
 * @return int
 */
function gamipress_fluentcart_pp_filter_estimated_total( $total, $context ) {

    if ( ! is_user_logged_in() ) {
        return $total;
    }

    $user_id        = get_current_user_id();
    $discount_cents = gamipress_fluentcart_partial_payments_get_discount_cents( $user_id );

    if ( $discount_cents <= 0 ) {
        return $total;
    }

    return max( 0, absint( $total ) - $discount_cents );
}
add_filter( 'fluent_cart/cart/estimated_total', 'gamipress_fluentcart_pp_filter_estimated_total', 10, 2 );

// ==========================================================================
// 6. ORDER PLACED OFFLINE — commit discount for Cash on Delivery
// ==========================================================================

/**
 * Same as order_paid_done but for offline payment (Cash on Delivery).
 *
 * Hook: fluent_cart/order_placed_offline
 * CONFIRMED: CodHandler.php line 52
 *
 * @since  1.0.0
 * @param  array $event_data
 */
function gamipress_fluentcart_pp_on_order_placed_offline( $event_data ) {
    gamipress_fluentcart_pp_on_order_paid( $event_data );
}
add_action( 'fluent_cart/order_placed_offline', 'gamipress_fluentcart_pp_on_order_placed_offline', 10, 1 );