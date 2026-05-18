<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Keap\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler to save and validate OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_keap_ajax_authorize() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix       = 'automatorwp_keap_';
    $access_token = sanitize_text_field( $_POST['access_token'] );

    if ( empty( $access_token ) ) {
        wp_send_json_error( array(
            'message' => __( 'Access token is required to connect with Keap', 'automatorwp-keap' ),
        ) );
        return;
    }

    // Validate token against Keap API before saving
    $valid = automatorwp_keap_validate_credentials( $access_token );

    if ( ! $valid ) {
        wp_send_json_error( array(
            'message' => __( 'Could not connect to Keap. Please check your access token.', 'automatorwp-keap' ),
        ) );
        return;
    }

    // Save credentials
    $settings = get_option( 'automatorwp_settings', array() );
    $settings[ $prefix . 'access_token' ] = $access_token;
    $settings[ $prefix . 'access_valid'  ] = true;
    update_option( 'automatorwp_settings', $settings );

    $redirect_url = get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-keap';

    wp_send_json_success( array(
        'message'      => __( 'Connected to Keap successfully!', 'automatorwp-keap' ),
        'redirect_url' => $redirect_url,
    ) );
}
add_action( 'wp_ajax_automatorwp_keap_authorize', 'automatorwp_keap_ajax_authorize' );

/**
 * AJAX handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_keap_ajax_delete_oauth_credentials() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix   = 'automatorwp_keap_';
    $settings = get_option( 'automatorwp_settings', array() );

    unset( $settings[ $prefix . 'access_token' ] );
    unset( $settings[ $prefix . 'access_valid'  ] );

    update_option( 'automatorwp_settings', $settings );

    // Clear all Keap caches on disconnect
    automatorwp_keap_clear_cache();

    wp_send_json_success();
}
add_action( 'wp_ajax_automatorwp_keap_delete_oauth_credentials', 'automatorwp_keap_ajax_delete_oauth_credentials' );

/**
 * AJAX handler to get campaigns for UI selector
 *
 * @since 1.1.0
 */
function automatorwp_keap_ajax_get_campaigns() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $campaigns = automatorwp_keap_get_campaigns();
    $results   = array();

    foreach ( $campaigns as $campaign ) {
        $results[] = array(
            'id'   => $campaign['id'],
            'text' => isset( $campaign['name'] ) ? $campaign['name'] : 'Campaign ' . $campaign['id'],
        );
    }

    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_keap_get_campaigns', 'automatorwp_keap_ajax_get_campaigns' );

/**
 * AJAX handler to get sequences for a campaign
 *
 * @since 1.1.0
 */
function automatorwp_keap_ajax_get_campaign_sequences() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $campaign_id = isset( $_REQUEST['table'] ) ? absint( $_REQUEST['table'] ) : 0;

    if ( empty( $campaign_id ) ) {
        wp_send_json_success( array() );
        die;
    }

    $sequences = automatorwp_keap_get_campaign_sequences( $campaign_id );
    $results   = array();

    foreach ( $sequences as $sequence ) {
        $results[] = array(
            'id'   => $sequence['id'],
            'text' => isset( $sequence['name'] ) ? $sequence['name'] : 'Sequence ' . $sequence['id'],
        );
    }

    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_keap_get_campaign_sequences', 'automatorwp_keap_ajax_get_campaign_sequences' );

/**
 * AJAX handler to get tags for UI selector
 *
 * @since 1.1.0
 */
function automatorwp_keap_ajax_get_tags() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $tags    = automatorwp_keap_get_tags();
    $results = array();

    foreach ( $tags as $tag ) {
        $results[] = array(
            'id'   => $tag['id'],
            'text' => isset( $tag['name'] ) ? $tag['name'] : 'Tag ' . $tag['id'],
        );
    }

    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_keap_get_tags', 'automatorwp_keap_ajax_get_tags' );