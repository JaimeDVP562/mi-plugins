<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Keap\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler to save OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_keap_ajax_save_oauth_credentials() {

	// Security check
	check_ajax_referer( 'automatorwp_keap_authorize_nonce', 'nonce' );

	$prefix = "automatorwp_keap_";

	/* sanitize incoming data */
	$access_token = isset( $_POST["access_token"] ) ? sanitize_text_field( $_POST["access_token"] ) : '';

	if ( empty( $access_token ) ) {
		wp_send_json_error( array( 'message' => __( 'Access Token is required.', 'automatorwp-keap' ) ) );
	} else {

		// Validates against API
		$valid = automatorwp_keap_check_settings_status( array( 'access_token' => $access_token ) );

		if ( ! $valid ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Access Token or API connection failed.', 'automatorwp-keap' ) ) );
		}

		$credentials = get_option( 'automatorwp_settings' );

		$credentials[ $prefix . 'access_token' ] = $access_token;
		$credentials[ $prefix . 'access_valid' ] = true; // Mark as valid
		$credentials                             = array_filter( $credentials );

		update_option( 'automatorwp_settings', $credentials );

		// Reload to same tab
        $redirect = add_query_arg( array( 'page' => 'automatorwp_settings', 'tab' => 'opt-tab-keap' ), admin_url( 'admin.php' ) );

		wp_send_json_success( array(
            'message' => __( 'Credentials saved successfully!', 'automatorwp-keap' ),
            'redirect_url' => $redirect
        ) );
	}
}

add_action( 'wp_ajax_automatorwp_keap_save_oauth_credentials', 'automatorwp_keap_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_keap_ajax_delete_oauth_credentials() {

	// Security check
    // This button might be using the same nonce
	check_ajax_referer( 'automatorwp_keap_authorize_nonce', 'nonce' );

	$prefix                                  = "automatorwp_keap_";
	$credentials                             = get_option( 'automatorwp_settings' );
	$credentials[ $prefix . 'access_token' ] = null;
	$credentials[ $prefix . 'access_valid' ] = null;
	$credentials                             = array_filter( $credentials );

	update_option( 'automatorwp_settings', $credentials );

    $redirect = add_query_arg( array( 'page' => 'automatorwp_settings', 'tab' => 'opt-tab-keap' ), admin_url( 'admin.php' ) );

	wp_send_json_success( array(
        'message' => __( 'Credentials deleted.', 'automatorwp-keap' ),
        'redirect_url' => $redirect
    ) );

}

add_action( 'wp_ajax_automatorwp_keap_delete_oauth_credentials', 'automatorwp_keap_ajax_delete_oauth_credentials' );

/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_keap_ajax_get_tags() {

	// Security check, forces to die if not security passed
	check_ajax_referer( 'automatorwp_admin', 'nonce' );

	global $wpdb;

	// Pull back the search string
	$search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

	$tags = automatorwp_keap_get_tags();

	$results = array();

	foreach ( $tags as $tag ) {

		// Simple search filter
		if ( ! empty( $search ) ) {
			if ( stripos( $tag['name'], $search ) === false ) {
				continue;
			}
		}

		$results[] = array(
			'id'   => $tag['id'],
			'text' => $tag['name']
		);
	}

	// Prepend option none
	$results = automatorwp_ajax_parse_extra_options( $results );

	// Return our results
	wp_send_json_success( $results );
	die;

}

add_action( 'wp_ajax_automatorwp_keap_get_tags', 'automatorwp_keap_ajax_get_tags' );
