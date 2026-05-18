<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\FluentCart\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Resolve a WordPress user ID from a FluentCart customer object.
 *
 * @since 1.0.0
 *
 * @param object $customer
 *
 * @return int
 */
function automatorwp_fluentcart_get_user_id_from_customer( $customer ) {

    if( empty( $customer ) || ! is_object( $customer ) ) {
        return 0;
    }

    $email = sanitize_email( isset( $customer->email ) ? $customer->email : '' );

    if( empty( $email ) ) {
        return 0;
    }

    $wp_user = get_user_by( 'email', $email );

    return ( $wp_user instanceof WP_User ) ? $wp_user->ID : 0;

}

/**
 * Build a sanitized full display name from a FluentCart customer object.
 *
 * @since 1.0.0
 *
 * @param object $customer
 *
 * @return string
 */
function automatorwp_fluentcart_get_customer_name( $customer ) {

    if( empty( $customer ) || ! is_object( $customer ) ) {
        return '';
    }

    $first = sanitize_text_field( isset( $customer->first_name ) ? $customer->first_name : '' );
    $last  = sanitize_text_field( isset( $customer->last_name ) ? $customer->last_name : '' );

    return trim( $first . ' ' . $last );

}

/**
 * Resolve a WordPress user ID from a FluentCart subscription object.
 *
 * @since 1.0.0
 *
 * @param object $subscription
 *
 * @return int
 */
function automatorwp_fluentcart_get_user_id_from_subscription( $subscription ) {

    if( empty( $subscription ) || ! is_object( $subscription ) ) {
        return 0;
    }

    if( ! empty( $subscription->customer ) && is_object( $subscription->customer ) ) {
        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $subscription->customer );
        if( $user_id > 0 ) {
            return $user_id;
        }
    }

    if( ! empty( $subscription->parent_order_id ) && class_exists( '\FluentCart\App\Models\Order' ) ) {
        $order = \FluentCart\App\Models\Order::find( absint( $subscription->parent_order_id ) );
        if( $order && ! empty( $order->customer ) ) {
            return automatorwp_fluentcart_get_user_id_from_customer( $order->customer );
        }
    }

    return 0;

}