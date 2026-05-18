<?php
/**
 * Functions
 *
 * @package GamiPress\Integrations\SliceWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get points type slug from a payout method key.
 * Payout method keys use the format: gamipress-{points-type-slug}
 *
 * @param string $payout_method Payout method key.
 * @return string Points type slug or empty string if not a GamiPress method.
 */
function gamipress_slicewp_points_payments_get_points_type_from_method( $payout_method ) {
    $prefix = 'gamipress-';
    if ( strpos( $payout_method, $prefix ) !== 0 ) {
        return '';
    }
    return substr( $payout_method, strlen( $prefix ) );
}

/**
 * Convert payout amount to points.
 * Base version: 1 monetary unit = 1 point.
 *
 * @param float  $amount      Payment amount.
 * @param string $points_type Points type slug.
 * @return int
 */
function gamipress_slicewp_points_payments_convert_amount_to_points( $amount, $points_type = '' ) {

    $amount = (float) $amount;
    $points = absint( round( $amount ) );

	/**
     * Filter points conversion.
     *
     * @param int    $points      Converted points.
     * @param float  $amount      Original amount.
     * @param string $points_type Points type slug.
     */
    return (int) apply_filters( 'gamipress_slicewp_points_payments_convert_amount_to_points', $points, $amount, $points_type );
}

/**
 * Get affiliate user id from payment
 *
 * @param int $payment_id Payment ID.
 * @return int
 */
function gamipress_slicewp_points_payments_get_user_id_from_payment( $payment_id ) {

	$payment_id = absint( $payment_id );

	if ( empty( $payment_id ) || ! function_exists( 'slicewp_get_payment' ) ) {
		return 0;
	}

	$payment = slicewp_get_payment( $payment_id );

	if ( empty( $payment ) || ! method_exists( $payment, 'get' ) ) {
		return 0;
	}

	$affiliate_id = absint( $payment->get( 'affiliate_id' ) );

	if ( empty( $affiliate_id ) || ! function_exists( 'slicewp_get_affiliate' ) ) {
		return 0;
	}

	$affiliate = slicewp_get_affiliate( $affiliate_id );

	if ( empty( $affiliate ) || ! method_exists( $affiliate, 'get' ) ) {
		return 0;
	}

	return absint( $affiliate->get( 'user_id' ) );
}

/**
 * Mark payment as paid.
 *
 * @param int $payment_id Payment ID.
 * @return void
 */
function gamipress_slicewp_points_payments_mark_payment_as_paid( $payment_id ) {

    $payment_id = absint( $payment_id );

    if ( empty( $payment_id ) || ! function_exists( 'slicewp_update_payment' ) ) {
        return;
    }

    slicewp_update_payment( $payment_id, array( 'status' => 'paid' ) );
}

/**
 * Check if payment was already processed
 *
 * @param int $payment_id Payment ID.
 * @return bool
 */
function gamipress_slicewp_points_payments_payment_already_processed( $payment_id ) {

    if ( ! function_exists( 'slicewp_get_payment_meta' ) ) {
        return false;
    }

    return (bool) slicewp_get_payment_meta( absint( $payment_id ), '_gamipress_slicewp_points_paid', true );
}

/**
 * Mark payment as processed by GamiPress.
 *
 * @param int $payment_id Payment ID.
 * @return void
 */
function gamipress_slicewp_points_payments_mark_payment_processed( $payment_id ) {

    if ( ! function_exists( 'slicewp_update_payment_meta' ) ) {
        return;
    }

    slicewp_update_payment_meta( absint( $payment_id ), '_gamipress_slicewp_points_paid', 1 );
}