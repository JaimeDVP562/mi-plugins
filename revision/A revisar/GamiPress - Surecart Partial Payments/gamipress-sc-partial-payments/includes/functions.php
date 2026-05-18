<?php
/**
 * Functions
 *
 * @package GamiPress\SureCart\Partial_Payments\Functions
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
function gamipress_sc_partial_payments_get_conversion( $points_type = '' ) {

    $points_types = gamipress_get_points_types();

    if( ! isset( $points_types[$points_type] ) )
        return false;

    $points_type = $points_types[$points_type];

    $conversion = gamipress_get_post_meta( $points_type['ID'], '_gamipress_sc_partial_payments_conversion', true );

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
function gamipress_sc_partial_payments_convert_to_points( $amount, $points_type = '' ) {

    $conversion = gamipress_sc_partial_payments_get_conversion( $points_type );

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
    if( apply_filters( 'gamipress_sc_partial_payments_convert_to_points_round', true, $converted_amount, $amount, $points_type, $conversion_rate ) )
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
    return apply_filters( 'gamipress_sc_partial_payments_convert_to_points', $converted_amount, $amount, $points_type, $conversion );

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
function gamipress_sc_partial_payments_convert_to_money( $amount, $points_type = '' ) {

    $amount = absint( $amount );

    $conversion = gamipress_sc_partial_payments_get_conversion( $points_type );

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
    if( apply_filters( 'gamipress_sc_partial_payments_convert_to_money_round', false, $converted_amount, $amount, $points_type, $conversion_rate ) )
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
    return apply_filters( 'gamipress_sc_partial_payments_convert_to_money', $converted_amount, $amount, $points_type, $conversion );

}

/**
 * Get all applied partial payments
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_sc_partial_payments_get_partial_payments() {

    $partial_payments = array();

    if( is_user_logged_in() ) {

        $user_id = get_current_user_id();

        $partial_payments = get_user_meta( $user_id, 'gamipress_sc_partial_payments', true );

        if( ! is_array( $partial_payments ) ) {
            $partial_payments = array();
        }
    }

    return apply_filters( 'gamipress_sc_partial_payments_get_partial_payments', $partial_payments );

}

/**
 * Get a sum of all applied partial payments (in money)
 *
 * @since 1.0.0
 *
 * @return float
 */
function gamipress_sc_partial_payments_get_checkout_partial_payments_sum() {

    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();
    $applied = 0;

    // Loop all applied partial payments to sum them
    foreach( $partial_payments as $points_type => $data ) {
        $applied += floatval( $data['money'] );
    }

    return $applied;

}

/**
 * Get the SureCart currency symbol
 *
 * @since  1.0.0
 *
 * @return string
 */
function gamipress_sc_partial_payments_get_currency_symbol() {
    // Try to get currency from SureCart account settings
    // Default to USD symbol if not available
    $currency_symbol = '$';

    // SureCart stores currency in its account settings
    // Try to retrieve it if the SureCart plugin provides a method
    if ( function_exists( 'SureCart' ) ) {
        try {
            $account = \SureCart\Models\Account::find();
            if ( $account && isset( $account->currency ) ) {
                $currency_symbol = gamipress_sc_partial_payments_currency_code_to_symbol( $account->currency );
            }
        } catch ( Exception $e ) {
            // Fallback to default
        }
    }

    return apply_filters( 'gamipress_sc_partial_payments_currency_symbol', $currency_symbol );
}

/**
 * Convert a currency code to its symbol
 *
 * @since  1.0.0
 *
 * @param string $currency_code The 3-letter currency code (e.g., 'usd', 'eur')
 *
 * @return string The currency symbol
 */
function gamipress_sc_partial_payments_currency_code_to_symbol( $currency_code ) {

    $symbols = array(
        'usd' => '$',
        'eur' => '€',
        'gbp' => '£',
        'jpy' => '¥',
        'cny' => '¥',
        'krw' => '₩',
        'inr' => '₹',
        'brl' => 'R$',
        'mxn' => '$',
        'cad' => 'CA$',
        'aud' => 'A$',
        'chf' => 'CHF',
        'sek' => 'kr',
        'nok' => 'kr',
        'dkk' => 'kr',
        'pln' => 'zł',
        'czk' => 'Kč',
        'huf' => 'Ft',
        'ron' => 'lei',
        'bgn' => 'лв',
        'hrk' => 'kn',
        'try' => '₺',
        'rub' => '₽',
        'zar' => 'R',
        'ngn' => '₦',
        'php' => '₱',
        'thb' => '฿',
        'idr' => 'Rp',
        'myr' => 'RM',
        'sgd' => 'S$',
        'hkd' => 'HK$',
        'twd' => 'NT$',
        'nzd' => 'NZ$',
        'ils' => '₪',
        'aed' => 'د.إ',
        'sar' => '﷼',
        'cop' => '$',
        'clp' => '$',
        'pen' => 'S/.',
        'ars' => '$',
    );

    $currency_code = strtolower( $currency_code );

    return isset( $symbols[$currency_code] ) ? $symbols[$currency_code] : strtoupper( $currency_code );
}

/**
 * Format money amount for display
 *
 * @since 1.0.0
 *
 * @param float $amount The amount in standard notation (e.g., 10.50)
 *
 * @return string
 */
function gamipress_sc_partial_payments_format_money( $amount ) {

    $currency_symbol = gamipress_sc_partial_payments_get_currency_symbol();
    $formatted = number_format( $amount, 2, '.', ',' );

    return $currency_symbol . $formatted;
}

/**
 * Clear all partial payments for the current user
 *
 * @since 1.0.0
 */
function gamipress_sc_partial_payments_clear_partial_payments() {

    if( is_user_logged_in() ) {

        $user_id = get_current_user_id();

        delete_user_meta( $user_id, 'gamipress_sc_partial_payments' );

    }

}
