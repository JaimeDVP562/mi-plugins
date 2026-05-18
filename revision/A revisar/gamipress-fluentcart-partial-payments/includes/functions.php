<?php
/**
 * Functions
 *
 * @package GamiPress\FluentCart\Partial_Payments\Functions
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------
// Conversion helpers
// -----------------------------------------------------------------------

/**
 * Get the conversion rate for a given points type
 *
 * Returns an array like: array( 'points' => 100, 'money' => 1 )
 * Returns false if the points type does not exist or has no conversion set.
 *
 * @since  1.0.0
 *
 * @param  string $points_type  Points type slug.
 * @return array|false
 */
function gamipress_fluentcart_partial_payments_get_conversion( $points_type = '' ) {

    $points_types = gamipress_get_points_types();

    if ( ! isset( $points_types[ $points_type ] ) ) {
        return false;
    }

    $pt_data    = $points_types[ $points_type ];
    $conversion = get_post_meta( $pt_data['ID'], '_gamipress_fluentcart_partial_payments_conversion', true );

    if ( empty( $conversion ) || ! is_array( $conversion ) ) {
        return false;
    }

    if ( empty( $conversion['points'] ) || empty( $conversion['money'] ) ) {
        return false;
    }

    return $conversion;
}

/**
 * Convert a monetary amount to points based on the conversion rate
 *
 * @since  1.0.0
 *
 * @param  float  $amount       Monetary amount.
 * @param  string $points_type  Points type slug.
 * @return int|float
 */
function gamipress_fluentcart_partial_payments_convert_to_points( $amount, $points_type = '' ) {

    $conversion = gamipress_fluentcart_partial_payments_get_conversion( $points_type );

    if ( ! $conversion ) {
        return 0;
    }

    $conversion_rate  = $conversion['money'] / $conversion['points'];
    $converted_amount = $amount / $conversion_rate;

    if ( apply_filters( 'gamipress_fluentcart_partial_payments_convert_to_points_round', true, $converted_amount, $amount, $points_type, $conversion_rate ) ) {
        $converted_amount = ceil( $converted_amount );
    }

    return apply_filters( 'gamipress_fluentcart_partial_payments_convert_to_points', $converted_amount, $amount, $points_type, $conversion );
}

/**
 * Convert a points amount to money based on the conversion rate
 *
 * @since  1.0.0
 *
 * @param  int    $amount       Points amount.
 * @param  string $points_type  Points type slug.
 * @return float
 */
function gamipress_fluentcart_partial_payments_convert_to_money( $amount, $points_type = '' ) {

    $amount     = absint( $amount );
    $conversion = gamipress_fluentcart_partial_payments_get_conversion( $points_type );

    if ( ! $conversion ) {
        return 0;
    }

    $conversion_rate  = $conversion['money'] / $conversion['points'];
    $converted_amount = $amount * $conversion_rate;

    if ( apply_filters( 'gamipress_fluentcart_partial_payments_convert_to_money_round', false, $converted_amount, $amount, $points_type, $conversion_rate ) ) {
        $converted_amount = ceil( $converted_amount );
    }

    return apply_filters( 'gamipress_fluentcart_partial_payments_convert_to_money', $converted_amount, $amount, $points_type, $conversion );
}

// -----------------------------------------------------------------------
// Partial payment session management (stored in user meta)
// -----------------------------------------------------------------------

/**
 * Get all partial payments currently applied by the logged-in user
 *
 * @since  1.0.0
 * @return array
 */
function gamipress_fluentcart_partial_payments_get_partial_payments() {

    $partial_payments = array();

    if ( is_user_logged_in() ) {

        $user_id          = get_current_user_id();
        $partial_payments = get_user_meta( $user_id, 'gamipress_fluentcart_partial_payments', true );

        if ( ! is_array( $partial_payments ) ) {
            $partial_payments = array();
        }
    }

    return apply_filters( 'gamipress_fluentcart_partial_payments_get_partial_payments', $partial_payments );
}

/**
 * Get the total monetary discount already applied from partial payments
 *
 * @since  1.0.0
 * @return float
 */
function gamipress_fluentcart_partial_payments_get_cart_partial_payments_sum() {

    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments();
    $applied          = 0.0;

    foreach ( $partial_payments as $points_type => $data ) {
        $applied += floatval( $data['money'] );
    }

    return $applied;
}

// -----------------------------------------------------------------------
// FluentCart order helpers
// -----------------------------------------------------------------------

/**
 * Get the current FluentCart cart subtotal
 *
 * @since  1.0.0
 * @return float
 */
function gamipress_fluentcart_partial_payments_get_cart_subtotal() {

    $subtotal = 0.0;

    if ( class_exists( '\FluentCart\App\Helpers\CartHelper' ) ) {
        try {
            $cart = \FluentCart\App\Helpers\CartHelper::getCart( null, false );

            if ( $cart && method_exists( $cart, 'getEstimatedTotal' ) ) {
                $subtotal = floatval( $cart->getEstimatedTotal() );
            }
        } catch ( \Exception $e ) {
            $subtotal = 0.0;
        }
    }

    return apply_filters( 'gamipress_fluentcart_partial_payments_cart_subtotal', $subtotal );
}

/**
 * Clear all partial payments for the current user
 *
 * @since  1.0.0
 * @param  int $user_id  WP user ID.
 * @return void
 */
function gamipress_fluentcart_partial_payments_clear_partial_payments( $user_id ) {

    delete_user_meta( $user_id, 'gamipress_fluentcart_partial_payments' );
}

// -----------------------------------------------------------------------
// Extended helpers required by filters.php
// -----------------------------------------------------------------------

/**
 * Get partial payments for a specific user ID
 *
 * @since  1.0.0
 * @param  int $user_id  WordPress user ID.
 * @return array
 */
function gamipress_fluentcart_partial_payments_get_partial_payments_by_user( $user_id ) {

    $user_id = absint( $user_id );

    if ( ! $user_id ) {
        return array();
    }

    $partial_payments = get_user_meta( $user_id, 'gamipress_fluentcart_partial_payments', true );

    if ( ! is_array( $partial_payments ) ) {
        $partial_payments = array();
    }

    return apply_filters( 'gamipress_fluentcart_partial_payments_get_partial_payments', $partial_payments );
}

/**
 * Convert a monetary amount (float decimal) to cents (integer).
 *
 * @since  1.0.0
 * @param  float $amount  Decimal amount.
 * @return int
 */
function gamipress_fluentcart_partial_payments_decimal_to_cents( $amount ) {
    return absint( round( floatval( $amount ) * 100 ) );
}

/**
 * Convert cents (integer) to decimal monetary amount (float).
 *
 * @since  1.0.0
 * @param  int $cents  Amount in cents.
 * @return float
 */
function gamipress_fluentcart_partial_payments_cents_to_decimal( $cents ) {
    return round( absint( $cents ) / 100, 2 );
}

/**
 * Get the total monetary discount in CENTS from the current user session.
 *
 * @since  1.0.0
 * @param  int $user_id  WordPress user ID.
 * @return int
 */
function gamipress_fluentcart_partial_payments_get_discount_cents( $user_id ) {

    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments_by_user( $user_id );
    $total_cents      = 0;

    foreach ( $partial_payments as $data ) {
        if ( isset( $data['money_cents'] ) ) {
            $total_cents += absint( $data['money_cents'] );
        } elseif ( isset( $data['money'] ) ) {
            $total_cents += gamipress_fluentcart_partial_payments_decimal_to_cents( $data['money'] );
        }
    }

    return $total_cents;
}