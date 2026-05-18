<?php
/**
 * Ajax Functions
 *
 * @package GamiPress\FluentCart\Partial_Payments\Ajax_Functions
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------
// Apply partial payment
// -----------------------------------------------------------------------

/**
 * Handle AJAX request to apply a partial payment to the current cart
 *
 * @since  1.0.0
 */
function gamipress_fluentcart_partial_payments_apply_partial_payment() {

    // Security check
    check_ajax_referer( 'gamipress_fluentcart_partial_payments', 'nonce' );

    $prefix = '_gamipress_fluentcart_partial_payments_';

    // Only logged-in users
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-fluentcart-partial-payments' ) );
    }

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments();

    // Sanitize inputs
    $points_type     = isset( $_POST['points_type'] ) ? sanitize_text_field( $_POST['points_type'] ) : '';
    $points_type_obj = gamipress_get_points_type( $points_type );

    if ( ! $points_type_obj ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Prevent double application for the same points type
    if ( isset( $partial_payments[ $points_type ] ) ) {
        wp_send_json_error( __( 'You already have a discount applied for this points type. Remove it first if you want to change the amount.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Verify the points type is enabled for partial payments
    if ( ! (bool) gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'enable' ) ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-fluentcart-partial-payments' ) );
    }

    $conversion = gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'conversion' );

    if ( empty( $conversion ) ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Sanitize points amount
    $points_key = $points_type . '_points';
    $points     = absint( isset( $_POST[ $points_key ] ) ? $_POST[ $points_key ] : 0 );

    if ( $points <= 0 ) {
        wp_send_json_error( sprintf(
            /* translators: %s: points type plural name */
            __( 'Invalid %s amount.', 'gamipress-fluentcart-partial-payments' ),
            $points_type_obj['plural_name']
        ) );
    }

    // Validate initial amount minimum
    $initial_amount = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'initial_amount' ) );
    $max_amount     = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'max_amount' ) );

    if ( $points < $initial_amount ) {
        wp_send_json_error( sprintf(
            /* translators: 1: points type plural name, 2: minimum amount */
            __( 'The minimum amount of %1$s allowed is %2$d.', 'gamipress-fluentcart-partial-payments' ),
            $points_type_obj['plural_name'],
            $initial_amount
        ) );
    }

    if ( $max_amount > 0 && $points > $max_amount ) {
        wp_send_json_error( sprintf(
            /* translators: 1: maximum amount, 2: points type plural name */
            __( 'You cannot exceed the %1$d %2$s limit.', 'gamipress-fluentcart-partial-payments' ),
            $max_amount,
            $points_type_obj['plural_name']
        ) );
    }

    // Check the user has enough points
    $user_points = gamipress_get_user_points( $user_id, $points_type );

    if ( $user_points < $points ) {
        wp_send_json_error( __( 'Insufficient points balance.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Calculate the monetary discount
    $money = gamipress_fluentcart_partial_payments_convert_to_money( $points, $points_type );

    if ( $money <= 0 ) {
        wp_send_json_error( __( 'Invalid discount amount.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Ensure the combined discount does not exceed the cart subtotal
    $cart_partial_payments_sum = gamipress_fluentcart_partial_payments_get_cart_partial_payments_sum();
    $subtotal                  = gamipress_fluentcart_partial_payments_get_cart_subtotal();

    if ( ( $cart_partial_payments_sum + $money ) > $subtotal ) {
        wp_send_json_error( __( 'The discount amount cannot exceed the cart total.', 'gamipress-fluentcart-partial-payments' ) );
    }

    // Check global maximum discount setting
    $max_discount = absint( gamipress_fluentcart_partial_payments_get_option( 'max_discount', '0' ) );

    if ( $max_discount > 0 ) {
        $max_discount_type = gamipress_fluentcart_partial_payments_get_option( 'max_discount_type', 'flat' );

        if ( $max_discount_type === 'percent' ) {
            $max_discount = ( $max_discount / 100 ) * $subtotal;
        }

        if ( ( $cart_partial_payments_sum + $money ) > $max_discount ) {
            wp_send_json_error( __( 'You have exceeded the maximum discount allowed for this order.', 'gamipress-fluentcart-partial-payments' ) );
        }
    }

    /**
     * Filter: allow external code to prevent the partial payment
     *
     * Return true to allow, or a translated error string to block.
     *
     * @param bool|string $process
     * @param int         $user_id
     * @param int         $points
     * @param string      $points_type
     * @param float       $money
     */
    $process = apply_filters( 'gamipress_fluentcart_partial_payments_process_partial_payment', true, $user_id, $points, $points_type, $money );

    if ( $process !== true ) {
        wp_send_json_error( $process );
    }

    // Store the partial payment in user meta.
    // money_cents is stored alongside money (float) so filters.php can compare
    // against FluentCart's internal cent values without repeated conversion.
    $partial_payments[ $points_type ] = array(
        'points'      => $points,
        'money'       => $money,
        'money_cents' => gamipress_fluentcart_partial_payments_decimal_to_cents( $money ),
    );

    update_user_meta( $user_id, 'gamipress_fluentcart_partial_payments', $partial_payments );

    // Deduct the points from the user's balance
    gamipress_deduct_points_to_user( $user_id, $points, $points_type, array(
        'log_type' => 'points_expend',
        'reason'   => gamipress_get_option(
            'points_expended_log_pattern',
            __( '{user} expended {points} {points_type} for a new total of {total_points} {points_type}', 'gamipress' )
        ),
    ) );

    wp_send_json_success( __( 'Discount applied successfully.', 'gamipress-fluentcart-partial-payments' ) );
}
add_action( 'wp_ajax_gamipress_fluentcart_partial_payments_apply_partial_payment',        'gamipress_fluentcart_partial_payments_apply_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_fluentcart_partial_payments_apply_partial_payment', 'gamipress_fluentcart_partial_payments_apply_partial_payment' );

// -----------------------------------------------------------------------
// Remove partial payment
// -----------------------------------------------------------------------

/**
 * Handle AJAX request to remove a partial payment from the current cart
 *
 * @since  1.0.0
 */
function gamipress_fluentcart_partial_payments_remove_partial_payment() {

    // Security check
    check_ajax_referer( 'gamipress_fluentcart_partial_payments', 'nonce' );

    // Only logged-in users
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-fluentcart-partial-payments' ) );
    }

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments();

    $points_type     = isset( $_POST['points_type'] ) ? sanitize_text_field( $_POST['points_type'] ) : '';
    $points_type_obj = gamipress_get_points_type( $points_type );

    if ( ! $points_type_obj ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-fluentcart-partial-payments' ) );
    }

    if ( ! isset( $partial_payments[ $points_type ] ) ) {
        wp_send_json_error( __( 'You do not have a discount applied for this points type.', 'gamipress-fluentcart-partial-payments' ) );
    }

    $points = $partial_payments[ $points_type ]['points'];

    // Refund the points back to the user
    gamipress_award_points_to_user( $user_id, $points, $points_type );

    // Remove from session meta
    unset( $partial_payments[ $points_type ] );
    update_user_meta( $user_id, 'gamipress_fluentcart_partial_payments', $partial_payments );

    wp_send_json_success( __( 'Discount removed successfully.', 'gamipress-fluentcart-partial-payments' ) );
}
add_action( 'wp_ajax_gamipress_fluentcart_partial_payments_remove_partial_payment',        'gamipress_fluentcart_partial_payments_remove_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_fluentcart_partial_payments_remove_partial_payment', 'gamipress_fluentcart_partial_payments_remove_partial_payment' );
