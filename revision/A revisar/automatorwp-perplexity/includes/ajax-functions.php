<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Perplexity\Ajax_Functions
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action.
 * Validates the API key, saves it and redirects on success.
 *
 * @since 1.0.0
 * @since 1.1.0 Receives api_key from POST, tests it and saves on success.
 */
function automatorwp_perplexity_ajax_authorize() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API Key is required to connect with Perplexity.', 'automatorwp-perplexity' ) ) );
        return;
    }

    // Temporarily override the stored key so automatorwp_perplexity_api_request uses the submitted one
    add_filter( 'automatorwp_option_automatorwp_perplexity_api_key', function() use ( $api_key ) { return $api_key; } );

    $response = automatorwp_perplexity_api_request( 'sonar', array(
        array( 'role' => 'user', 'content' => 'Reply with exactly: "Connection successful"' ),
    ), array(), 1 );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        return;
    }

    // Save the API key to automatorwp_settings
    $settings = get_option( 'automatorwp_settings', array() );
    $settings['automatorwp_perplexity_api_key'] = $api_key;
    update_option( 'automatorwp_settings', $settings );

    wp_send_json_success( array(
        'message'      => __( 'Connected with Perplexity successfully.', 'automatorwp-perplexity' ),
        'redirect_url' => get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-perplexity',
    ) );
}
add_action( 'wp_ajax_automatorwp_perplexity_authorize', 'automatorwp_perplexity_ajax_authorize' );
