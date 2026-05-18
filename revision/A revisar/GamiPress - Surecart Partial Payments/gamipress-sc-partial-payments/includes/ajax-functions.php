<?php
/**
 * Ajax Functions
 *
 * @package GamiPress\SureCart\Partial_Payments\Ajax_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Apply a partial payment to the current checkout
 *
 * @since 1.0.0
 */
function gamipress_sc_partial_payments_apply_partial_payment() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_sc_partial_payments', 'nonce' );

    $prefix = '_gamipress_sc_partial_payments_';

    // Guests not allowed
    if( ! is_user_logged_in() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-sc-partial-payments' ) );
    }

    $user_id = get_current_user_id();
    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();

    $points_type = sanitize_text_field( $_POST['points_type'] );
    $points_type_obj = gamipress_get_points_type( $points_type );

    // Bail if not points type selected
    if( ! $points_type_obj )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-sc-partial-payments' ) );

    // Bail if points type has already in use
    if( isset( $partial_payments[$points_type] ) )
        wp_send_json_error( __( 'You already got a discount through this points type. Remove the discount if you want to change it.', 'gamipress-sc-partial-payments' ) );

    // Bail if points type is not enabled for partial payments
    if( ! (bool) gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'enable' ) )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-sc-partial-payments' ) );

    $conversion = gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'conversion' );

    // Points types without a conversion rate can't be used for partial payments
    if( empty( $conversion ) )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-sc-partial-payments' ) );

    $points = absint( $_POST[$points_type . '_points'] );

    // Bail if invalid points amount
    if( $points <= 0 )
        wp_send_json_error( sprintf( __( 'Invalid %s amount.', 'gamipress-sc-partial-payments' ), $points_type_obj['plural_name'] ) );

    // Check initial and max amounts
    $initial_amount  = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'initial_amount' ) );
    $max_amount  = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'max_amount' ) );

    if( $points < $initial_amount ) {
        wp_send_json_error( sprintf( __( 'The minimum amount of %s allowed are %d.', 'gamipress-sc-partial-payments' ), $points_type_obj['plural_name'], $initial_amount ) );
    }

    if( $max_amount > 0 && $points > $max_amount ) {
        wp_send_json_error( sprintf( __( 'You can\'t exceed the %d %s amount.', 'gamipress-sc-partial-payments' ), $max_amount, $points_type_obj['plural_name'] ) );
    }

    // Check the user points
    $user_points = gamipress_get_user_points( $user_id, $points_type );

    if( $user_points < $points )
        wp_send_json_error( __( 'Insufficient funds.', 'gamipress-sc-partial-payments' ) );

    // Discount to apply to the checkout
    $money = gamipress_sc_partial_payments_convert_to_money( $points, $points_type );

    // Bail if not discount to apply
    if( $money === 0 )
        wp_send_json_error( __( 'Invalid discount amount.', 'gamipress-sc-partial-payments' ) );

    // Check the maximum discount
    $checkout_partial_payments = gamipress_sc_partial_payments_get_checkout_partial_payments_sum();

    $max_discount = absint( gamipress_sc_partial_payments_get_option( 'max_discount', '0' ) );

    // Check if discount is limited
    if( $max_discount > 0 ) {
        $max_discount_type = gamipress_sc_partial_payments_get_option( 'max_discount_type', 'flat' );

        // Max percent discount (we can't calculate percentage of SureCart total here, just use flat)
        // For percentage, we store the limit and check it client-side or on validation
        if( $max_discount_type === 'flat' ) {
            if( ( $checkout_partial_payments + $money ) > $max_discount )
                wp_send_json_error( __( 'You have exceeded the maximum discount allowed.', 'gamipress-sc-partial-payments' ) );
        }
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
    $process_partial_payment = apply_filters( 'gamipress_sc_partial_payments_process_partial_payment', true, $user_id, $points, $points_type, $money );

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
        update_user_meta( $user_id, 'gamipress_sc_partial_payments', $partial_payments );

        // Deduct the points to the user
        gamipress_deduct_points_to_user( $user_id, $points, $points_type, array(
            'log_type' => 'points_expend',
            'reason' => gamipress_get_option( 'points_expended_log_pattern', __( '{user} expended {points} {points_type} for a new total of {total_points} {points_type}', 'gamipress' ) )
        ) );

    }

    // Build response with discount info
    $response_data = array(
        'message' => __( 'Discount applied successfully.', 'gamipress-sc-partial-payments' ),
        'discount' => array(
            'points_type' => $points_type,
            'points' => $points,
            'money' => $money,
            'money_formatted' => gamipress_sc_partial_payments_format_money( $money ),
            'points_label' => gamipress_format_points( $points, $points_type ),
        ),
        'total_discount' => gamipress_sc_partial_payments_get_checkout_partial_payments_sum(),
        'total_discount_formatted' => gamipress_sc_partial_payments_format_money( gamipress_sc_partial_payments_get_checkout_partial_payments_sum() ),
        'partial_payments' => gamipress_sc_partial_payments_get_partial_payments(),
    );

    wp_send_json_success( $response_data );

}
add_action( 'wp_ajax_gamipress_sc_partial_payments_apply_partial_payment', 'gamipress_sc_partial_payments_apply_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_sc_partial_payments_apply_partial_payment', 'gamipress_sc_partial_payments_apply_partial_payment' );

/**
 * Remove a partial payment from the current checkout
 *
 * @since 1.0.0
 */
function gamipress_sc_partial_payments_remove_partial_payment() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_sc_partial_payments', 'nonce' );

    // Guests not allowed
    if( ! is_user_logged_in() )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-sc-partial-payments' ) );

    $user_id = get_current_user_id();
    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();

    $points_type = sanitize_text_field( $_POST['points_type'] );
    $points_type_obj = gamipress_get_points_type( $points_type );

    // Bail if not points type selected
    if( ! $points_type_obj ) {
        wp_send_json_error( __( 'Invalid points type.', 'gamipress-sc-partial-payments' ) );
    }

    // Bail if points type is not in use
    if( ! isset( $partial_payments[$points_type] ) ) {
        wp_send_json_error( __( 'You did\'t got a discount through this points type.', 'gamipress-sc-partial-payments' ) );
    }

    $points = $partial_payments[$points_type]['points'];

    // Award back the points to the user
    gamipress_award_points_to_user( $user_id, $points, $points_type );

    // Remove the partial payment information
    unset( $partial_payments[$points_type] );

    // Update the user partial payments meta
    update_user_meta( $user_id, 'gamipress_sc_partial_payments', $partial_payments );

    // Build response with updated info
    $response_data = array(
        'message' => __( 'Discount removed successfully.', 'gamipress-sc-partial-payments' ),
        'total_discount' => gamipress_sc_partial_payments_get_checkout_partial_payments_sum(),
        'total_discount_formatted' => gamipress_sc_partial_payments_format_money( gamipress_sc_partial_payments_get_checkout_partial_payments_sum() ),
        'partial_payments' => gamipress_sc_partial_payments_get_partial_payments(),
    );

    wp_send_json_success( $response_data );

}
add_action( 'wp_ajax_gamipress_sc_partial_payments_remove_partial_payment', 'gamipress_sc_partial_payments_remove_partial_payment' );

/**
 * Get current partial payments status via AJAX
 *
 * @since 1.0.0
 */
function gamipress_sc_partial_payments_get_status() {
    // Security check
    check_ajax_referer( 'gamipress_sc_partial_payments', 'nonce' );

    if( ! is_user_logged_in() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-sc-partial-payments' ) );
    }

    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();

    $response_data = array(
        'partial_payments' => $partial_payments,
        'total_discount' => gamipress_sc_partial_payments_get_checkout_partial_payments_sum(),
        'total_discount_formatted' => gamipress_sc_partial_payments_format_money( gamipress_sc_partial_payments_get_checkout_partial_payments_sum() ),
    );

    wp_send_json_success( $response_data );
}
add_action( 'wp_ajax_gamipress_sc_partial_payments_get_status', 'gamipress_sc_partial_payments_get_status' );
