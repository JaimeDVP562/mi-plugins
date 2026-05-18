<?php
/**
 * Rest API
 *
 * @package  AutomatorWP\Dailybot\Rest_API
 * @author   AutomatorWP
 * @since    1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register REST endpoint to receive Dailybot webhook events
 *
 * @since 1.0.0
 */
function automatorwp_dailybot_rest_api_init() {
    register_rest_route( 'dailybot/v1', '/webhooks', array(
        'methods'             => 'POST',
        'callback'            => 'automatorwp_dailybot_rest_api_cb',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'automatorwp_dailybot_rest_api_init' );

/**
 * Handle incoming Dailybot webhook requests
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request
 *
 * @return WP_REST_Response
 */
function automatorwp_dailybot_rest_api_cb( $request ) {

    $params = $request->get_params();

    if ( empty( $params ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => __( 'No parameters received.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) ), 400 );
    }

    // Sanitize all params
    $params = map_deep( $params, 'sanitize_text_field' );

    // Resolve WordPress user from email if present
    $user_id = 0;
    if ( ! empty( $params['email'] ) ) {
        $wp_user = get_user_by( 'email', sanitize_email( $params['email'] ) );
        $user_id = $wp_user ? $wp_user->ID : 0;
    }

    // Fire trigger based on invitation status
    if ( isset( $params['results']['status'] ) ) {

        if ( $params['results']['status'] === 'resolved' ) {
            do_action( 'automatorwp_dailybot_invitation_accepted', $params, $user_id );
        }

        if ( $params['results']['status'] === 'pending' ) {
            do_action( 'automatorwp_dailybot_invitation_created', $params, $user_id );
        }
    }

    return new WP_REST_Response( array( 'success' => true ), 200 );
}