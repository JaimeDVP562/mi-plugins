<?php
/**
 * Functions
 *
 * Shared helper functions used by trigger and action classes.
 * This file must be loaded before any trigger or action file.
 *
 * @package     AutomatorWP\Integrations\FluentCart\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// USER RESOLUTION
// =============================================================================

/**
 * Resolve a WordPress user ID from a FluentCart customer object.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_get_user_id_from_customer( $customer ) {
    if ( empty( $customer ) || ! is_object( $customer ) ) {
        return 0;
    }
    $email = sanitize_email( $customer->email ?? '' );
    if ( empty( $email ) ) {
        return 0;
    }
    $wp_user = get_user_by( 'email', $email );
    return ( $wp_user instanceof WP_User ) ? $wp_user->ID : 0;
}

/**
 * Build a sanitized full display name from a FluentCart customer object.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_get_customer_name( $customer ) {
    if ( empty( $customer ) || ! is_object( $customer ) ) {
        return '';
    }
    $first = sanitize_text_field( $customer->first_name ?? '' );
    $last  = sanitize_text_field( $customer->last_name  ?? '' );
    return trim( $first . ' ' . $last );
}

/**
 * Resolve a WordPress user ID from a FluentCart subscription object.
 * Tries customer relation first, then falls back to parent order.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_get_user_id_from_subscription( $subscription ) {
    if ( empty( $subscription ) || ! is_object( $subscription ) ) {
        return 0;
    }
    if ( ! empty( $subscription->customer ) && is_object( $subscription->customer ) ) {
        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $subscription->customer );
        if ( $user_id > 0 ) {
            return $user_id;
        }
    }
    if ( ! empty( $subscription->parent_order_id ) && class_exists( '\FluentCart\App\Models\Order' ) ) {
        $order = \FluentCart\App\Models\Order::find( absint( $subscription->parent_order_id ) );
        if ( $order && ! empty( $order->customer ) ) {
            return automatorwp_fluentcart_get_user_id_from_customer( $order->customer );
        }
    }
    return 0;
}

// =============================================================================
// TAG PARSING
// =============================================================================

/**
 * Parse an AutomatorWP tag value and apply the Anti-Numeric Patch.
 * Uses automatorwp_parse_automation_tags() confirmed in this core version.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_parse_and_sanitize( $raw_value, $action, $user_id, $event ) {
    $parsed = automatorwp_parse_automation_tags( $raw_value, $action->automation_id, $user_id );
    // Anti-Numeric Patch: revert if raw was not numeric but parsed result is.
    if ( is_numeric( $parsed ) && ! is_numeric( $raw_value ) ) {
        $parsed = $raw_value;
    }
    return $parsed;
}

// =============================================================================
// ERROR LOGGING
// =============================================================================

/**
 * Write a prefixed error message to the WordPress debug log.
 * Only writes when WP_DEBUG_LOG is enabled.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_log_error( $message, $context = array() ) {
    if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
        return;
    }
    $log = '[AutomatorWP FluentCart] ' . $message;
    if ( ! empty( $context ) ) {
        $log .= ' | ' . wp_json_encode( $context );
    }
    error_log( $log );
}