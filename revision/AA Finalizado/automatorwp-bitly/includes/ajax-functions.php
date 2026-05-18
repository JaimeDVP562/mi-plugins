<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Bitly\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ajax handler to check and authorize the Bitly API key
 *
 * @since 1.0.0
 */
function automatorwp_bitly_ajax_authorize() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API key is required to connect with Bitly', 'automatorwp-bitly' ) ) );
        return;
    }

    $is_valid = automatorwp_bitly_check_api_key( $api_key );

    if ( $is_valid ) {
        wp_send_json_success( array( 'message' => __( 'Successfully connected with Bitly', 'automatorwp-bitly' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Could not connect with Bitly. Please check your API key', 'automatorwp-bitly' ) ) );
    }

}
add_action( 'wp_ajax_automatorwp_bitly_authorize', 'automatorwp_bitly_ajax_authorize' );