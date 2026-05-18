<?php
/**
 * Plugin Name:           GamiPress - AffiliateWP Points Payments
 * Plugin URI:            https://gamipress.com/
 * Description:           Adds an AffiliateWP payout method to pay affiliates with GamiPress points instead of money.
 * Version:               1.0.0
 * Author:                GamiPress
 * Author URI:            https://gamipress.com/
 * Text Domain:           gamipress-affiliatewp-points-payments
 * Domain Path:           /languages/
 * Requires at least:     5.0
 * Requires PHP:          7.4
 * License:               GNU AGPL v3.0
 *
 * @package GamiPress\Integrations\AffiliateWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
final class GamiPress_AffiliateWP_Points_Payments {

	/**
	 * Plugin instance
	 *
	 * @var GamiPress_AffiliateWP_Points_Payments|null
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return GamiPress_AffiliateWP_Points_Payments
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->define_constants();
			self::$instance->includes();
		}

		return self::$instance;
	}

	/**
	 * Define constants
	 *
	 * @return void
	 */
	private function define_constants() {

		if ( ! defined( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_VER' ) ) {
			define( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_VER', '1.0.0' );
		}

		if ( ! defined( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_FILE' ) ) {
			define( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_FILE', __FILE__ );
		}

		if ( ! defined( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_DIR' ) ) {
			define( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_URL' ) ) {
			define( 'GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Check plugin requirements
	 *
	 * Checks that both AffiliateWP and GamiPress are active and loaded.
	 *
	 * @return bool
	 */
	private function meets_requirements() {
		return (
			function_exists( 'affiliate_wp' ) &&
			function_exists( 'gamipress_award_points_to_user' )
		);
	}

	/**
	 * Include files
	 *
	 * @return void
	 */
	private function includes() {

		if ( ! $this->meets_requirements() ) {
			return;
		}

		require_once GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_DIR . 'includes/functions.php';
		require_once GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_DIR . 'includes/payout-method.php';
		require_once GAMIPRESS_AFFILIATEWP_POINTS_PAYMENTS_DIR . 'includes/payouts.php';
	}
}

/**
 * Init plugin
 *
 * @return GamiPress_AffiliateWP_Points_Payments
 */
function gamipress_affiliatewp_points_payments() {
	return GamiPress_AffiliateWP_Points_Payments::instance();
}

add_action( 'plugins_loaded', 'gamipress_affiliatewp_points_payments', 20 );