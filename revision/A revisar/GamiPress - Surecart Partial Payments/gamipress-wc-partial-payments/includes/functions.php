<?php
/**
 * Functions
 *
 * @package GamiPress\WooCommerce\Partial_Payments\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the given points type slug conversion
 *
 * @since 1.0.0
 *
 * @param string $points_type
 *
 * @return array|bool
 */
function gamipress_wc_partial_payments_get_conversion( $points_type = '' ) {

    $points_types = gamipress_get_points_types();

    if( ! isset( $points_types[$points_type] ) )
        return false;

    $points_type = $points_types[$points_type];

    $conversion = gamipress_get_post_meta( $points_type['ID'], '_gamipress_wc_partial_payments_conversion', true );

    if( empty( $conversion) )
        return false;

    return $conversion;

}

/**
 * Convert an amount of money to points based on configured conversion rate
 *
 * @since 1.0.0
 *
 * @param string|float  $amount
 * @param string        $points_type
 *
 * @return float
 */
function gamipress_wc_partial_payments_convert_to_points( $amount, $points_type = '' ) {

    $conversion = gamipress_wc_partial_payments_get_conversion( $points_type );

    $conversion_rate  = $conversion['money'] / $conversion['points'];

    $converted_amount = $amount / $conversion_rate;

    /**
     * Filter the ability to round (rounding up) or not the converted amount (by default, true)
     *
     * @since 1.0.0
     *
     * @param bool          $round
     * @param string|float  $converted_amount
     * @param string|float  $amount
     * @param string        $points_type
     * @param int           $conversion_rate
     *
     * @return int|float
     */
    if( apply_filters( 'gamipress_wc_partial_payments_convert_to_points_round', true, $converted_amount, $amount, $points_type, $conversion_rate ) )
        $converted_amount = ceil( $converted_amount );

    /**
     * Filters the converted amount of money to points based on configured conversion rate
     *
     * @since 1.0.0
     *
     * @param string|float  $converted_amount
     * @param string|float  $amount
     * @param string        $points_type
     * @param int           $conversion_rate
     *
     * @return int|float
     */
    return apply_filters( 'gamipress_wc_partial_payments_convert_to_points', $converted_amount, $amount, $points_type, $conversion );

}

/**
 * Convert an amount of points to money based on configured conversion rate
 *
 * @since 1.0.0
 *
 * @param int       $amount
 * @param string    $points_type
 *
 * @return float
 */
function gamipress_wc_partial_payments_convert_to_money( $amount, $points_type = '' ) {

    $amount = absint( $amount );

    $conversion = gamipress_wc_partial_payments_get_conversion( $points_type );

    $conversion_rate  = $conversion['money'] / $conversion['points'];

    $converted_amount = $amount * $conversion_rate;

    /**
     * Filter the ability to round (rounding up) or not the converted amount (by default, false)
     *
     * @since 1.0.0
     *
     * @param bool          $round
     * @param string|float  $converted_amount
     * @param string|float  $amount
     * @param string        $points_type
     * @param int           $conversion_rate
     *
     * @return int|float
     */
    if( apply_filters( 'gamipress_wc_partial_payments_convert_to_money_round', false, $converted_amount, $amount, $points_type, $conversion_rate ) )
        $converted_amount = ceil( $converted_amount );

    /**
     * Filters the converted amount of money to points based on configured conversion rate
     *
     * @since 1.0.0
     *
     * @param string|float  $converted_amount
     * @param string|float  $amount
     * @param string        $points_type
     * @param int           $conversion_rate
     *
     * @return int|float
     */
    return apply_filters( 'gamipress_wc_partial_payments_convert_to_money', $converted_amount, $amount, $points_type, $conversion );

}

/**
 * Get all applied partial payments
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_wc_partial_payments_get_partial_payments() {

    $partial_payments = array();

    if( is_user_logged_in() ) {

        $user_id = get_current_user_id();

        $partial_payments = get_user_meta( $user_id, 'gamipress_wc_partial_payments', true );

        if( ! is_array( $partial_payments ) ) {
            $partial_payments = array();
        }
    }

    return apply_filters( 'gamipress_wc_partial_payments_get_partial_payments', $partial_payments );

}

/**
 * Get a sum of all applied partial payments
 *
 * @since 1.0.0
 *
 * @return float
 */
function gamipress_wc_partial_payments_get_cart_partial_payments_sum() {

    $partial_payments = gamipress_wc_partial_payments_get_partial_payments();
    $applied = 0;

    // Loop all applied partial payments to sum them
    foreach( $partial_payments as $points_type => $data ) {
        $applied += floatval( $data['money'] );
    }

    return $applied;

}

/**
 * WooCommerce HPOS compatibility
 *
 * @since  1.1.7
 */
function gamipress_wc_partial_payments_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', GAMIPRESS_WC_PARTIAL_PAYMENTS_FILE, true );
	}
}
add_action( 'before_woocommerce_init', 'gamipress_wc_partial_payments_hpos_compatibility' );