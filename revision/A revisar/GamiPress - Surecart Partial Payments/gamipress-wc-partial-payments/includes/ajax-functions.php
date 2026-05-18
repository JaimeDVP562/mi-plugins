<?php
/**
 * Ajax Functions
 *
 * @package GamiPress\WooCommerce\Partial_Payments\Ajax_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Apply a partial payment to the current cart
 *
 * @since 1.0.0
 */
function gamipress_wc_partial_payments_apply_partial_payment() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_wc_partial_payments', 'nonce' );

    $prefix = '_gamipress_wc_partial_payments_';

    // Guests not allowed
    if( ! is_user_logged_in() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-wc-partial-payments' ) );
    }

    $user_id = get_current_user_id();
    $partial_payments = gamipress_wc_partial_payments_get_partial_payments();

    $points_type = $_POST['points_type'];
    $points_type_obj = gamipress_get_points_type( $points_type );

    // Bail if not points type selected
    if( ! $points_type_obj )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-wc-partial-payments' ) );

    // Bail if points type has already in use
    if( isset( $partial_payments[$points_type] ) )
        wp_send_json_error( __( 'You already got a discount through this points type. Remove the discount if you want to change it.', 'gamipress-wc-partial-payments' ) );

    // Bail if points type is not enabled for partial payments
    if( ! (bool) gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'enable' ) )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-wc-partial-payments' ) );

    $conversion = gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'conversion' );

    // Points types without a conversion rate can't be used for partial payments
    if( empty( $conversion ) )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-wc-partial-payments' ) );

    $points = absint( $_POST[$points_type . '_points'] );

    // Bail if invalid points amount
    if( $points <= 0 )
        wp_send_json_error( sprintf( __( 'Invalid %s amount.', 'gamipress-wc-partial-payments' ), $points_type_obj['plural_name'] ) );

    // Check initial and max amounts
    $initial_amount  = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'initial_amount' ) );
    $max_amount  = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'max_amount' ) );

    if( $points < $initial_amount ) {
        wp_send_json_error( sprintf( __( 'The minimum amount of %s allowed are %d.', 'gamipress-wc-partial-payments' ), $points_type_obj['plural_name'], $initial_amount ) );
    }

    if( $max_amount > 0 && $points > $max_amount ) {
        wp_send_json_error( sprintf( __( 'You can\'t exceed the %d %s amount.', 'gamipress-wc-partial-payments' ), $max_amount, $points_type_obj['plural_name'] ) );
    }

    // Check the user points
    $user_points = gamipress_get_user_points( $user_id, $points_type );

    if( $user_points < $points )
        wp_send_json_error( __( 'Insufficient funds.', 'gamipress-wc-partial-payments' ) );

    // Discount to apply to the cart
    $money = gamipress_wc_partial_payments_convert_to_money( $points, $points_type );

    // Bail if not discount to apply
    if( $money === 0 )
        wp_send_json_error( __( 'Invalid discount amount.', 'gamipress-wc-partial-payments' ) );

    // Check if discount exceeds cart subtotal
    $cart_partial_payments = gamipress_wc_partial_payments_get_cart_partial_payments_sum();
    $total = WC()->cart->get_subtotal();

    if( WC()->cart->display_prices_including_tax() ) {
        // Desired but WC does not allows to reduce taxes
        $total += WC()->cart->get_subtotal_tax();
    }

    if( ( $cart_partial_payments + $money ) > $total ){
        // If disable fee taxes is not checked, then exclude fee taxes
        if( ! (bool) gamipress_wc_partial_payments_get_option( 'disable_fee_taxes', false ) ) {
            wp_send_json_error( __( 'The discount amount can\'t exceed the cart total.', 'gamipress-wc-partial-payments' ) );
        } else{
            wp_send_json_error( __( 'The discount amount can\'t exceed the cart subtotal.', 'gamipress-wc-partial-payments' ) );
        }
    }
        

    $max_discount = absint( gamipress_wc_partial_payments_get_option( 'max_discount', '0' ) );

    // Check if discount is limited
    if( $max_discount > 0 ) {
        $max_discount_type = gamipress_wc_partial_payments_get_option( 'max_discount_type', 'flat' );

        // Max percent discount
        if( $max_discount_type === 'percent' )
            $max_discount = ($max_discount / 100) * $total;

        // Bail if not discount to apply
        if( ( $cart_partial_payments + $money ) > $max_discount )
            wp_send_json_error( __( 'You have exceeded the maximum discount allowed.', 'gamipress-wc-partial-payments' ) );
    }

    /**
     * Filter to process if partial payment should be applied or not
     *
     * @since 1.0.0
     *
     * @param bool      $process_partial_payment    Whatever if partial payment should be processed or not, by default true
     * @param int       $user_id                    The user ID
     * @param int       $points                     The points amount used
     * @param string    $points_type                The points type's slug
     * @param float     $money                      The money discount amount
     *
     * @return true|string  True if partial payment should be processed, or a message to return explaining why no
     */
    $process_partial_payment = apply_filters( 'gamipress_wc_partial_payments_process_partial_payment', true, $user_id, $points, $points_type, $money );

    if( $process_partial_payment !== true ) {
        wp_send_json_error( $process_partial_payment );
    }


    // Add the partial payment information to the user
    if( ! isset( $partial_payments[$points_type] ) ) {
        $partial_payments[$points_type] = array(
            'points' => $points,
            'money' => $money,
        );

        // Update the user partial payments meta
        update_user_meta( $user_id, 'gamipress_wc_partial_payments', $partial_payments );

        // Deduct the points to the user
        gamipress_deduct_points_to_user( $user_id, $points, $points_type, array(
            'log_type' => 'points_expend',
            'reason' => gamipress_get_option( 'points_expended_log_pattern', __( '{user} expended {points} {points_type} for a new total of {total_points} {points_type}', 'gamipress' ) )
        ) );

    }

    wp_send_json_success( __( 'Discount applied successfully.', 'gamipress-wc-partial-payments' ) );

}
add_action( 'wp_ajax_gamipress_wc_partial_payments_apply_partial_payment', 'gamipress_wc_partial_payments_apply_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_wc_partial_payments_apply_partial_payment', 'gamipress_wc_partial_payments_apply_partial_payment' );

/**
 * Remove a partial payment from the current cart
 *
 * @since 1.0.0
 */
function gamipress_wc_partial_payments_remove_partial_payment() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_wc_partial_payments', 'nonce' );

    // Guests not allowed
    if( ! is_user_logged_in() )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-wc-partial-payments' ) );

    $user_id = get_current_user_id();
    $partial_payments = gamipress_wc_partial_payments_get_partial_payments();

    $points_type = $_POST['points_type'];
    $points_type_obj = gamipress_get_points_type( $points_type );

    // Bail if not points type selected
    if( ! $points_type_obj ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-wc-partial-payments' ) );
    }

    // Bail if points type is not in use
    if( ! isset( $partial_payments[$points_type] ) ) {
        wp_send_json_error( __( 'You did\'t got a discount through this points type.', 'gamipress-wc-partial-payments' ) );
    }

    $points = $partial_payments[$points_type]['points'];

    // Award back the points to the user
    gamipress_award_points_to_user( $user_id, $points, $points_type );

    // Remove the partial payment information
    unset( $partial_payments[$points_type] );

    // Update the user partial payments meta
    update_user_meta( $user_id, 'gamipress_wc_partial_payments', $partial_payments );

    wp_send_json_success( __( 'Discount removed successfully.', 'gamipress-wc-partial-payments' ) );

}
add_action( 'wp_ajax_gamipress_wc_partial_payments_remove_partial_payment', 'gamipress_wc_partial_payments_remove_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_wc_partial_payments_remove_partial_payment', 'gamipress_wc_partial_payments_remove_partial_payment' );


