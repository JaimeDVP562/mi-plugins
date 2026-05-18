<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\BookingPress\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the related WordPress user ID from a BookingPress customer ID.
 *
 * @since 1.0.0
 *
 * @param int $customer_id BookingPress customer ID.
 *
 * @return int WordPress user ID or 0 when there is no linked account.
 */
function automatorwp_bookingpress_get_wp_user_id_from_customer_id( $customer_id ) {

    global $wpdb;

    $customer_id = absint( $customer_id );

    if( empty( $customer_id ) ) {
        return 0;
    }

    $customer_table = $wpdb->prefix . 'bookingpress_customers';
    $wp_user_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT bookingpress_wpuser_id FROM {$customer_table} WHERE bookingpress_customer_id = %d",
            $customer_id
        )
    );

    return absint( $wp_user_id );

}
