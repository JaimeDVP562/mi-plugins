<?php
/**
 * Payout method integration
 *
 * Registers GamiPress point types as payout methods in AffiliateWP.
 *
 * @package GamiPress\Integrations\AffiliateWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register GamiPress Points as payout methods in AffiliateWP
 *
 * @param array $methods Registered payout methods.
 * @return array
 */
function gamipress_affiliatewp_points_payments_register_payout_method( $methods ) {

	$point_types = get_posts( array(
		'post_type'      => 'points-type',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	if ( ! empty( $point_types ) ) {
		foreach ( $point_types as $point_type ) {
			$slug = $point_type->post_name;
			
			$label = $point_type->post_title;

			$methods[ 'gamipress_' . $slug ] = 'GamiPress: ' . $label;
		}
	}

	return $methods;
}
add_filter( 'affwp_payout_methods', 'gamipress_affiliatewp_points_payments_register_payout_method' );