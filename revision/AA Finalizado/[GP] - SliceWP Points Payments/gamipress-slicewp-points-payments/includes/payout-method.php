<?php
/**
 * Payout method integration
 *
 * @package GamiPress\Integrations\SliceWP_Points_Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register one payout method per GamiPress points type.
 * Key format: gamipress-{points-type-slug}
 * Label: plural name of the points type.
 *
 * @param array $payout_methods
 * @return array
 */
function gamipress_slicewp_points_payments_register_payout_methods( $payout_methods ) {

    if ( ! function_exists( 'gamipress_get_points_types' ) ) {
        return $payout_methods;
    }

    $points_types = gamipress_get_points_types();

    if ( empty( $points_types ) ) {
        return $payout_methods;
    }

    foreach ( $points_types as $slug => $data ) {

        $plural_name = ! empty( $data['plural_name'] ) ? $data['plural_name'] : __( 'Points', 'gamipress-slicewp-points-payments' );

        $payout_methods[ 'gamipress-' . $slug ] = array(
            'label'    => $plural_name,
            'supports' => array( 'bulk_payments' ),
        );
    }

    return $payout_methods;
}
add_filter( 'slicewp_register_payout_methods', 'gamipress_slicewp_points_payments_register_payout_methods' );

/**
 * Allow bulk payments for GamiPress points payout methods.
 * Returns true when there are unpaid payments for that method in the payout.
 *
 * @param bool   $can
 * @param int    $payout_id
 * @param string $payout_method
 * @return bool
 */
function gamipress_slicewp_points_payments_can_do_bulk_payments( $can, $payout_id, $payout_method ) {

    if ( strpos( $payout_method, 'gamipress-' ) !== 0 ) {
        return $can;
    }

    $unpaid = slicewp_get_payments( array(
        'payout_id'     => absint( $payout_id ),
        'payout_method' => $payout_method,
        'status'        => array_diff(
            array_keys( slicewp_get_payment_available_statuses() ),
            array( 'paid' )
        ),
        'number'        => 1,
    ), true );

    return $unpaid > 0;
}
add_filter( 'slicewp_can_do_bulk_payments', 'gamipress_slicewp_points_payments_can_do_bulk_payments', 10, 3 );

/**
 * Replace GamiPress SliceWP commission status listener with a
 * defensive version that handles missing 'status' key.
 *
 * @return void
 */
function gamipress_slicewp_points_patch_commission_listener() {

    if ( ! function_exists( 'gamipress_slicewp_commission_status_change' ) ) {
        return;
    }

    remove_action( 'slicewp_update_commission', 'gamipress_slicewp_commission_status_change', 10 );
    add_action( 'slicewp_update_commission', 'gamipress_slicewp_points_safe_commission_status_change', 10, 3 );
}
add_action( 'init', 'gamipress_slicewp_points_patch_commission_listener', 20 );

/**
 * Safe replacement for gamipress_slicewp_commission_status_change.
 *
 * @param int   $commission_id
 * @param array $commission_data
 * @param array $commission_old_data
 * @return void
 */
function gamipress_slicewp_points_safe_commission_status_change( $commission_id, $commission_data, $commission_old_data ) {

    if ( ! isset( $commission_data['status'] ) ) {
        if ( isset( $commission_old_data['status'] ) ) {
            $commission_data['status'] = $commission_old_data['status'];
        } elseif ( function_exists( 'slicewp_get_commission' ) ) {
            $commission                = slicewp_get_commission( absint( $commission_id ) );
            $commission_data['status'] = ( ! is_null( $commission ) ) ? $commission->get( 'status' ) : 'unpaid';
        } else {
            $commission_data['status'] = 'unpaid';
        }
    }

    if ( ! isset( $commission_old_data['status'] ) ) {
        $commission_old_data['status'] = 'unpaid';
    }

    gamipress_slicewp_commission_status_change( $commission_id, $commission_data, $commission_old_data );
}