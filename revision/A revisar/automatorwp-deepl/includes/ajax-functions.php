<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\DeepL\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler to verify and save DeepL credentials
 *
 * @since 1.0.0
 */
function automatorwp_deepl_ajax_authorize() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix  = 'automatorwp_deepl_';
    $api_key = sanitize_text_field( isset( $_POST['api_key'] ) ? $_POST['api_key'] : '' );

    // Bail if no API key provided
    if ( empty( $api_key ) ) {
        wp_send_json_error( array(
            'message' => __( 'API Key is required to connect with DeepL.', 'automatorwp-deepl' ),
        ) );
        return;
    }

    // Detect plan and set correct base URL
    $base_url = ( substr( $api_key, -3 ) === ':fx' )
        ? 'https://api-free.deepl.com'
        : 'https://api.deepl.com';

    // Test connection via /v2/usage endpoint
    $response = wp_remote_get( $base_url . '/v2/usage', array(
        'headers'   => array(
            'Authorization' => 'DeepL-Auth-Key ' . $api_key,
        ),
        'timeout'   => 45,
        'sslverify' => false, // Required for localhost/XAMPP
    ) );

    // Capture WP errors without crashing
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array(
            'message' => $response->get_error_message(),
        ) );
        return;
    }

    $code = wp_remote_retrieve_response_code( $response );

    if ( $code !== 200 ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid API Key. Please check your credentials.', 'automatorwp-deepl' ),
        ) );
        return;
    }

    // Parse usage data for confirmation message
    $body          = json_decode( wp_remote_retrieve_body( $response ), true );
    $count         = isset( $body['character_count'] ) ? number_format( $body['character_count'] ) : '—';
    $limit         = isset( $body['character_limit'] ) ? number_format( $body['character_limit'] ) : '—';
    $plan_label    = ( substr( $api_key, -3 ) === ':fx' ) ? 'Free' : 'Pro';

    // Save credentials
    $settings = get_option( 'automatorwp_settings' );
    $settings[ $prefix . 'api_key' ] = $api_key;
    update_option( 'automatorwp_settings', $settings );

    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-deepl' );

    wp_send_json_success( array(
        'message'      => sprintf(
            __( 'Connected with DeepL (%s plan). Usage: %s / %s characters this month.', 'automatorwp-deepl' ),
            $plan_label,
            $count,
            $limit
        ),
        'redirect_url' => $admin_url,
    ) );

}
add_action( 'wp_ajax_automatorwp_deepl_authorize', 'automatorwp_deepl_ajax_authorize' );
