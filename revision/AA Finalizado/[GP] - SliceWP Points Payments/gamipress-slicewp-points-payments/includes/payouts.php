<?php
/**
 * Payout handlers
 *
 * @package GamiPress\Integrations\SliceWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle single payment with points.
 * Hooked dynamically for every registered GamiPress points type.
 *
 * Hook pattern: slicewp_do_single_payment_gamipress-{points-type-slug}
 *
 * @param int $payment_id Payment ID.
 * @return void
 */
function gamipress_slicewp_points_payments_do_single_payment( $payment_id ) {

    $payment_id = absint( $payment_id );

    if ( empty( $payment_id ) ) {
        return;
    }

    if ( gamipress_slicewp_points_payments_payment_already_processed( $payment_id ) ) {
        return;
    }

    if ( ! function_exists( 'slicewp_get_payment' ) || ! function_exists( 'gamipress_award_points_to_user' ) ) {
        return;
    }

    $payment = slicewp_get_payment( $payment_id );

    if ( empty( $payment ) || ! method_exists( $payment, 'get' ) ) {
        return;
    }

    // Resolve points type from the payout_method stored in the payment
    $payout_method = $payment->get( 'payout_method' );
    $points_type   = gamipress_slicewp_points_payments_get_points_type_from_method( $payout_method );

    if ( empty( $points_type ) ) {
        return;
    }

    // Validate that the points type actually exists in GamiPress
    if ( ! function_exists( 'gamipress_get_points_type' ) || ! gamipress_get_points_type( $points_type ) ) {
        return;
    }

    $user_id = gamipress_slicewp_points_payments_get_user_id_from_payment( $payment_id );

    if ( empty( $user_id ) ) {
        return;
    }

    $amount = (float) $payment->get( 'amount' );

    if ( $amount <= 0 ) {
        return;
    }

    $points = gamipress_slicewp_points_payments_convert_amount_to_points( $amount, $points_type );

    if ( $points <= 0 ) {
        return;
    }

    $awarded = gamipress_award_points_to_user( $user_id, $points, $points_type );

    if ( ! $awarded ) {
        return;
    }

    gamipress_slicewp_points_payments_mark_payment_processed( $payment_id );
    gamipress_slicewp_points_payments_mark_payment_as_paid( $payment_id );

    // Update commissions linked to this payment
    if ( function_exists( 'slicewp_get_commissions' ) && function_exists( 'slicewp_update_commission' ) ) {
        $commissions = slicewp_get_commissions( array(
            'payment_id' => $payment_id,
            'number'     => -1,
        ) );
        if ( ! empty( $commissions ) ) {
            foreach ( $commissions as $commission ) {
                slicewp_update_commission( $commission->get( 'id' ), array( 'status' => 'paid' ) );
            }
        }
    }
}

/**
 * Register hooks for each GamiPress points type.
 * Runs on init after GamiPress has registered its points types.
 *
 * @return void
 */
function gamipress_slicewp_points_payments_register_hooks() {

    if ( ! function_exists( 'gamipress_get_points_types' ) ) {
        return;
    }

    $points_types = gamipress_get_points_types();

    if ( empty( $points_types ) ) {
        return;
    }

    foreach ( $points_types as $slug => $data ) {
        $method = 'gamipress-' . $slug;
        add_action( 'slicewp_do_single_payment_' . $method, 'gamipress_slicewp_points_payments_do_single_payment' );
        add_action( 'slicewp_do_bulk_payments_' . $method,  'gamipress_slicewp_points_payments_do_bulk_payments' );
    }
}
add_action( 'init', 'gamipress_slicewp_points_payments_register_hooks', 15 );

/**
 * Handle bulk payout with points.
 *
 * @param int $payout_id Payout ID.
 * @return void
 */
function gamipress_slicewp_points_payments_do_bulk_payments( $payout_id ) {

    $payout_id = absint( $payout_id );

    if ( empty( $payout_id ) || ! function_exists( 'slicewp_get_payments' ) ) {
        return;
    }

    // Get the payout_method from the current hook name to filter only matching payments
    $current_hook  = current_filter();
    $payout_method = str_replace( 'slicewp_do_bulk_payments_', '', $current_hook );

    $payments = slicewp_get_payments( array(
        'payout_id'     => $payout_id,
        'payout_method' => $payout_method,
        'number'        => -1,
    ) );

    if ( empty( $payments ) || ! is_array( $payments ) ) {
        return;
    }

    foreach ( $payments as $payment ) {

        if ( empty( $payment ) || ! method_exists( $payment, 'get' ) ) {
            continue;
        }

        $payment_id = absint( $payment->get( 'id' ) );

        if ( empty( $payment_id ) ) {
            continue;
        }

        gamipress_slicewp_points_payments_do_single_payment( $payment_id );
    }
}