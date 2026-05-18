<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\FluentCart\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler to verify FluentCart connection status
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_ajax_check_status() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    if( ! defined( 'FLUENTCART_PLUGIN_PATH' ) ) {
        wp_send_json_error( array( 'message' => __( 'FluentCart is not active.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    $version = defined( 'FLUENTCART_VERSION' ) ? FLUENTCART_VERSION : 'Unknown';

    wp_send_json_success( array(
        'message' => sprintf( __( 'FluentCart v%s is active and connected.', 'automatorwp-fluentcart' ), $version ),
        'version' => $version,
    ) );

}
add_action( 'wp_ajax_automatorwp_fluentcart_check_status', 'automatorwp_fluentcart_ajax_check_status' );

/**
 * AJAX handler to get FluentCart orders for Select2 selectors
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_ajax_get_orders() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    if( ! class_exists( '\FluentCart\App\Models\Order' ) ) {
        wp_send_json_error( array( 'message' => __( 'Order model not available.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    $search = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
    $page   = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $limit  = 20;

    $query = \FluentCart\App\Models\Order::query()->orderBy( 'id', 'DESC' )->limit( $limit )->offset( ( $page - 1 ) * $limit );

    if( ! empty( $search ) && is_numeric( $search ) ) {
        $query->where( 'id', absint( $search ) );
    }

    $results = array();

    foreach( $query->get() as $order ) {
        $results[] = array(
            'id'   => absint( $order->id ),
            'text' => sprintf(
                __( 'Order #%1$d — %2$s — %3$s', 'automatorwp-fluentcart' ),
                absint( $order->id ),
                sanitize_text_field( isset( $order->payment_status ) ? $order->payment_status : '' ),
                sanitize_text_field( isset( $order->total ) ? $order->total : '0' )
            ),
        );
    }

    wp_send_json_success( array(
        'results'    => $results,
        'pagination' => array( 'more' => count( $results ) === $limit )
    ) );

}
add_action( 'wp_ajax_automatorwp_fluentcart_get_orders', 'automatorwp_fluentcart_ajax_get_orders' );

/**
 * AJAX handler to get FluentCart subscriptions for Select2 selectors
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_ajax_get_subscriptions() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    if( ! class_exists( '\FluentCart\App\Models\Subscription' ) ) {
        wp_send_json_error( array( 'message' => __( 'Subscription model not available.', 'automatorwp-fluentcart' ) ) );
        return;
    }

    $search = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
    $page   = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $limit  = 20;

    $query = \FluentCart\App\Models\Subscription::query()->orderBy( 'id', 'DESC' )->limit( $limit )->offset( ( $page - 1 ) * $limit );

    if( ! empty( $search ) && is_numeric( $search ) ) {
        $query->where( 'id', absint( $search ) );
    }

    $results = array();

    foreach( $query->get() as $sub ) {
        $results[] = array(
            'id'   => absint( $sub->id ),
            'text' => sprintf(
                __( 'Subscription #%1$d — %2$s', 'automatorwp-fluentcart' ),
                absint( $sub->id ),
                sanitize_text_field( isset( $sub->status ) ? $sub->status : '' )
            ),
        );
    }

    wp_send_json_success( array(
        'results'    => $results,
        'pagination' => array( 'more' => count( $results ) === $limit )
    ) );

}
add_action( 'wp_ajax_automatorwp_fluentcart_get_subscriptions', 'automatorwp_fluentcart_ajax_get_subscriptions' );