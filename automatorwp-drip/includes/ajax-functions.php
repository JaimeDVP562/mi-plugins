<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Drip\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_authorize() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_drip_';

    $key    = isset( $_POST['key'] )    ? sanitize_text_field( $_POST['key'] )    : '';
    $secret = isset( $_POST['secret'] ) ? sanitize_text_field( $_POST['secret'] ) : '';

    // Bail if required fields are empty
    if ( empty( $key ) || empty( $secret ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Drip', 'automatorwp-drip' ) ) );
        return;
    }

    $status_key = automatorwp_drip_check_api_key( $key, $secret );

    if ( empty( $status_key ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save Account ID and API Key
    $settings[ $prefix . 'key' ]    = $key;
    $settings[ $prefix . 'secret' ] = $secret;

    update_option( 'automatorwp_settings', $settings );

    $admin_url = get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip';

    wp_send_json_success( array(
        'message'      => __( 'Correct data to connect with Drip', 'automatorwp-drip' ),
        'redirect_url' => $admin_url,
    ) );

}
add_action( 'wp_ajax_automatorwp_drip_authorize', 'automatorwp_drip_ajax_authorize' );

/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_get_tags() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';

    $tags    = automatorwp_drip_get_tags();
    $results = array();

    // Parse tags into select2 format, optionally filtered by search
    if ( is_array( $tags ) ) {
        foreach ( $tags as $tag ) {

            if ( ! empty( $search ) && strpos( strtolower( $tag ), strtolower( $search ) ) === false ) {
                continue;
            }

            $results[] = array(
                'id'   => $tag,
                'text' => $tag,
            );
        }
    }

    // Prepend blank option
    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_drip_get_tags', 'automatorwp_drip_ajax_get_tags' );

/**
 * Ajax function for selecting campaigns
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_get_campaigns() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';

    $campaigns = automatorwp_drip_get_campaigns();
    $results   = array();

    // Parse campaigns into select2 format, optionally filtered by search
    foreach ( $campaigns as $campaign_id => $campaign_name ) {

        if ( ! empty( $search ) && strpos( strtolower( $campaign_name ), strtolower( $search ) ) === false ) {
            continue;
        }

        $results[] = array(
            'id'   => $campaign_id,
            'text' => $campaign_name,
        );
    }

    // Prepend blank option
    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_drip_get_campaigns', 'automatorwp_drip_ajax_get_campaigns' );

/**
 * Ajax function for selecting workflows
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_get_workflows() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';

    $workflows = automatorwp_drip_get_workflows();
    $results   = array();

    foreach ( $workflows as $workflow_id => $workflow_name ) {

        if ( ! empty( $search ) && strpos( strtolower( $workflow_name ), strtolower( $search ) ) === false ) {
            continue;
        }

        $results[] = array(
            'id'   => $workflow_id,
            'text' => $workflow_name,
        );
    }

    // Prepend blank option
    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_drip_get_workflows', 'automatorwp_drip_ajax_get_workflows' );
