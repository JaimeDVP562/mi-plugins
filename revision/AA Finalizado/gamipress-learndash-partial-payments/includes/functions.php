<?php
/**
 * Functions
 *
 * @package GamiPress\LearnDash\Partial_Payments\Functions
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
function gamipress_ld_partial_payments_get_conversion( $points_type = '' ) {

if( empty( $points_type ) ) {
        return false;
    }

    $conversion = gamipress_get_points_type_meta( $points_type, '_gamipress_ld_partial_payments_conversion', true );

    if( empty( $conversion ) || ! is_array( $conversion ) ) {
        return false;
    }

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
function gamipress_ld_partial_payments_convert_to_points( $amount, $points_type = '' ) {

$conversion = gamipress_ld_partial_payments_get_conversion( $points_type );

    if( ! $conversion ) {
        return (float) $amount;
    }

    $converted_amount = ( $amount * $conversion['points'] ) / $conversion['cost'];

    return (float) $converted_amount;   

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
function gamipress_ld_partial_payments_convert_to_money( $amount, $points_type = '' ) {

    $conversion = gamipress_ld_partial_payments_get_conversion( $points_type );

    if( ! $conversion ) {
        return (float) $amount;
    }

    $converted_amount = ( $amount * $conversion['cost'] ) / $conversion['points'];

    /**
     * Filter the ability to round (rounding up) or not the converted amount (by default, false)
     */
    $round = apply_filters( 'gamipress_ld_partial_payments_round_converted_amount', false, $converted_amount, $amount, $points_type, $conversion );

    if( $round ) {
        $converted_amount = ceil( $converted_amount );
    }

    /**
     * Filters the converted amount of money to points based on configured conversion rate
     */
    return (float) apply_filters( 'gamipress_ld_partial_payments_converted_amount', $converted_amount, $amount, $points_type, $conversion );

}

/**
 * Get all applied partial payments
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_ld_partial_payments_get_partial_payments() {


}

/**
 * Get a sum of all applied partial payments
 *
 * @since 1.0.0
 *
 * @return float
 */
function gamipress_ld_partial_payments_get_cart_partial_payments_sum() {

 

}

