<?php
/**
 * Functions
 *
 * @package GamiPress\FluentCart\Points_Gateway\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the given points type slug conversion rate
 *
 * @since 1.0.0
 *
 * @param string $points_type
 *
 * @return int|float|bool
 */
function gamipress_fluentcart_points_gateway_get_conversion_rate( $points_type = '' ) {

    $points_types = gamipress_get_points_types();

    if ( ! isset( $points_types[ $points_type ] ) ) {
        return false;
    }

    // Get conversion rate from plugin settings
    $settings = get_option( 'gamipress_fluentcart_points_gateway_settings', array() );

    $conversion_rate = isset( $settings[ 'conversion_rate_' . $points_type ] )
        ? floatval( $settings[ 'conversion_rate_' . $points_type ] )
        : 0;

    if ( $conversion_rate <= 0 ) {
        // Default conversion is 100 points => 1 unit of currency
        return 100;
    }

    return $conversion_rate;
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
function gamipress_fluentcart_points_gateway_convert_to_points( $amount, $points_type = '' ) {

    $amount = floatval( $amount );

    $conversion_rate = gamipress_fluentcart_points_gateway_get_conversion_rate( $points_type );

    $converted_amount = $amount * $conversion_rate;

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
    if ( apply_filters( 'gamipress_fluentcart_points_gateway_convert_to_points_round', true, $converted_amount, $amount, $points_type, $conversion_rate ) ) {
        $converted_amount = ceil( $converted_amount );
    }

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
    return apply_filters( 'gamipress_fluentcart_points_gateway_convert_to_points', $converted_amount, $amount, $points_type, $conversion_rate );
}

/**
 * Get all gateway settings
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_fluentcart_points_gateway_get_settings() {
    return get_option( 'gamipress_fluentcart_points_gateway_settings', array() );
}

/**
 * Update gateway settings
 *
 * @since 1.0.0
 *
 * @param array $settings
 *
 * @return bool
 */
function gamipress_fluentcart_points_gateway_update_settings( $settings ) {
    return update_option( 'gamipress_fluentcart_points_gateway_settings', $settings );
}

/**
 * Check if a specific points type gateway is enabled
 *
 * @since 1.0.0
 *
 * @param string $points_type
 *
 * @return bool
 */
function gamipress_fluentcart_points_gateway_is_enabled( $points_type = '' ) {

    $settings = gamipress_fluentcart_points_gateway_get_settings();

    return isset( $settings[ 'enabled_' . $points_type ] ) && $settings[ 'enabled_' . $points_type ] === 'yes';
}
