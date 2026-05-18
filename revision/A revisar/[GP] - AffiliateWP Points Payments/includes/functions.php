<?php
/**
 * Functions
 *
 * @package GamiPress\Integrations\AffiliateWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get points type
 *
 * @return string
 */
function gamipress_affiliatewp_points_payments_get_points_type() {
	return apply_filters( 'gamipress_affiliatewp_points_payments_points_type', 'points' );
}

/**
 * Convert an amount (money) to points.
 *
 * @param float  $amount      
 * @param string $points_type 
 * @return int The points amount.
 */
function gamipress_affiliatewp_points_payments_convert_amount_to_points( $amount, $points_type = '' ) {
	$conversion_rate = apply_filters( 'gamipress_affiliatewp_points_payments_conversion_rate', 1, $points_type );
	
	return (int) round( $amount * $conversion_rate );
}

/**
 * Get User ID from a referral.
 *
 * @param object $referral The referral object.
 * @return int The user ID or 0.
 */
function gamipress_affiliatewp_points_payments_get_user_id_from_referral( $referral ) {
	if ( is_object( $referral ) && ! empty( $referral->affiliate_id ) && function_exists( 'affwp_get_affiliate_user_id' ) ) {
		return affwp_get_affiliate_user_id( $referral->affiliate_id );
	}
	return 0;
}

/**
 * Check if a referral has already been processed for points payment.
 *
 * @param int $referral_id The referral ID.
 * @return bool True if processed, false otherwise.
 */
function gamipress_affiliatewp_points_payments_referral_already_processed( $referral_id ) {
	if ( function_exists( 'affwp_get_referral_meta' ) ) {
		$processed = affwp_get_referral_meta( $referral_id, '_gamipress_points_payment_processed', true );
		return ! empty( $processed );
	}
	return false;
}

/**
 * Mark a referral as processed for points payment.
 *
 * @param int $referral_id The referral ID.
 */
function gamipress_affiliatewp_points_payments_mark_referral_processed( $referral_id ) {
	if ( function_exists( 'affwp_add_referral_meta' ) ) {
		affwp_add_referral_meta( $referral_id, '_gamipress_points_payment_processed', '1', true );
	}
}
