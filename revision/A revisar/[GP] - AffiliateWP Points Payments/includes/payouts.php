<?php
/**
 * Payout handlers
 *
 * Handles paying referrals with GamiPress points dynamically.
 *
 * @package GamiPress\Integrations\AffiliateWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gamipress_affiliatewp_points_payments_pay_referral( $referral_id, $points_type ) {
	$referral_id = absint( $referral_id );

	if ( empty( $referral_id ) || empty( $points_type ) ) {
		return false;
	}

	if ( gamipress_affiliatewp_points_payments_referral_already_processed( $referral_id ) ) {
		return false;
	}

	if ( ! function_exists( 'affwp_get_referral' ) || ! function_exists( 'gamipress_award_points_to_user' ) ) {
		return false;
	}

	$referral = affwp_get_referral( $referral_id );

	if ( empty( $referral ) ) {
		return false;
	}

	$user_id = gamipress_affiliatewp_points_payments_get_user_id_from_referral( $referral );

	if ( empty( $user_id ) ) {
		return false;
	}

	$amount = (float) $referral->amount;

	if ( $amount <= 0 ) {
		return false;
	}

	$points = gamipress_affiliatewp_points_payments_convert_amount_to_points( $amount, $points_type );

	if ( $points <= 0 ) {
		return false;
	}

	gamipress_award_points_to_user(
		$user_id,
		$points,
		$points_type,
		array(
			'admin_id'      => get_current_user_id(),
			'earnings_id'   => $referral_id,
			'earnings_type' => 'affiliatewp_referral',
			'log_type'      => 'affiliatewp_points_payment',
		)
	);

	gamipress_affiliatewp_points_payments_mark_referral_processed( $referral_id );
	affwp_set_referral_status( $referral_id, 'paid' );

	return true;
}

function gamipress_affiliatewp_points_payments_process_bulk_payout( $start, $end, $minimum, $affiliate_id, $payout_method, $bypass_holding = false ) {
	$points_type  = str_replace( 'gamipress_', '', $payout_method );
	$affiliate_id = absint( $affiliate_id );

	$args = array(
		'status' => 'unpaid',
		'number' => -1,
	);

	if ( ! empty( $affiliate_id ) ) {
		$args['affiliate_id'] = $affiliate_id;
	}

	if ( function_exists( 'affwp_get_referrals' ) ) {
		$referrals = affwp_get_referrals( $args );
	} else {
		$referrals = affiliate_wp()->referrals->get_referrals( $args );
	}

	if ( empty( $referrals ) || ! is_array( $referrals ) ) {
		$url = admin_url( 'admin.php?page=affiliate-wp-payouts&affwp_notice=payout_failed' );
		if ( headers_sent() ) {
			echo '<script>window.location.href="' . esc_url( $url ) . '";</script>';
		} else {
			wp_safe_redirect( $url );
		}
		exit;
	}

	$referral_ids = array();

	foreach ( $referrals as $referral ) {
		$paid = gamipress_affiliatewp_points_payments_pay_referral( $referral->referral_id, $points_type );
		if ( $paid ) {
			$referral_ids[] = $referral->referral_id;
		}
	}

	if ( ! empty( $referral_ids ) && function_exists( 'affwp_add_payout' ) ) {
		$referrals_by_affiliate = array();

		foreach ( $referral_ids as $ref_id ) {
			$referral = ( function_exists( 'affwp_get_referral' ) ) ? affwp_get_referral( $ref_id ) : affiliate_wp()->referrals->get_referral( $ref_id );
			
			if ( empty( $referral ) ) {
				continue;
			}

			$aff_id = $referral->affiliate_id;

			if ( ! isset( $referrals_by_affiliate[ $aff_id ] ) ) {
				$referrals_by_affiliate[ $aff_id ] = array(
					'referral_ids' => array(),
					'amount'       => 0,
				);
			}

			$referrals_by_affiliate[ $aff_id ]['referral_ids'][] = $ref_id;
			$referrals_by_affiliate[ $aff_id ]['amount'] += (float) $referral->amount;
		}

		foreach ( $referrals_by_affiliate as $aff_id => $payout_data ) {
			affwp_add_payout( array(
				'affiliate_id'  => $aff_id,
				'referrals'     => $payout_data['referral_ids'],
				'amount'        => $payout_data['amount'],
				'payout_method' => $payout_method,
				'status'        => 'paid',
			) );
		}

		$url = admin_url( 'admin.php?page=affiliate-wp-payouts&affwp_notice=payout_created' );
		if ( headers_sent() ) {
			echo '<script>window.location.href="' . esc_url( $url ) . '";</script>';
		} else {
			wp_safe_redirect( $url );
		}
		exit;

	} else {
		$url = admin_url( 'admin.php?page=affiliate-wp-payouts&affwp_notice=payout_failed' );
		if ( headers_sent() ) {
			echo '<script>window.location.href="' . esc_url( $url ) . '";</script>';
		} else {
			wp_safe_redirect( $url );
		}
		exit;
	}
}

function gamipress_affiliatewp_points_payments_process_single_payout( $referral_id ) {
	$referral_id = absint( $referral_id );

	if ( empty( $referral_id ) ) {
		return;
	}

	$action        = current_action(); 
	$points_type   = str_replace( 'affwp_process_single_payout_gamipress_', '', $action );
	$payout_method = 'gamipress_' . $points_type;
	
	$paid = gamipress_affiliatewp_points_payments_pay_referral( $referral_id, $points_type );

	if ( $paid && function_exists( 'affwp_add_payout' ) && function_exists( 'affwp_get_referral' ) ) {
		$referral = affwp_get_referral( $referral_id );

		if ( ! empty( $referral ) ) {
			affwp_add_payout( array(
				'affiliate_id'  => $referral->affiliate_id,
				'referrals'     => array( $referral_id ),
				'amount'        => $referral->amount,
				'payout_method' => $payout_method,
				'status'        => 'paid',
			) );
		}
	}
}

add_action( 'affwp_new_payout', function( $data ) {
	$point_types = get_posts( array(
		'post_type'      => 'points-type',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	if ( ! empty( $point_types ) ) {
		foreach ( $point_types as $point_type ) {
			$slug = $point_type->post_name; 
			add_action( 'affwp_process_payout_gamipress_' . $slug, 'gamipress_affiliatewp_points_payments_process_bulk_payout', 10, 6 );
			add_action( 'affwp_process_single_payout_gamipress_' . $slug, 'gamipress_affiliatewp_points_payments_process_single_payout', 10, 1 );
		}
	}
}, 1 );