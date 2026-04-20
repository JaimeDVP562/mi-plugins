<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Drip\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_drip_';

    $key = sanitize_text_field( $_POST['key'] );
    $secret = sanitize_text_field( $_POST['secret'] );
   
    // Check parameters given
    if( empty( $key ) || empty( $secret ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Drip', 'automatorwp-drip' ) ) );
        return;
    }

    $status_secret = automatorwp_drip_check_api_secret( $secret );
    $status_key = automatorwp_drip_check_api_key( );

    if ( empty( $status_secret ) || empty ( $status_key ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API key and API secret
    $settings[$prefix . 'key'] = $key;
    $settings[$prefix . 'secret'] = $secret;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip';
   
    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Drip', 'automatorwp-drip' ),
        'redirect_url' => $admin_url
    ) );


}
add_action( 'wp_ajax_automatorwp_drip_authorize',  'automatorwp_drip_ajax_authorize' );

/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_drip_ajax_get_tags() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $tags = automatorwp_drip_get_tags();
    
    $results = array();

    // Parse tags results to match select2 results
    foreach ( $tags as $tag ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $tag ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id' => $tag,
            'text' => $tag
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
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

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $campaigns = automatorwp_drip_get_campaigns();

    $results = array();

    // Parse campaigns results to match select2 results
    foreach ( $campaigns as $campaign ) {

        if( ! is_array( $campaign ) ) {
            continue;
        }

        $campaign_id = isset( $campaign['id'] ) ? $campaign['id'] : '';
        $campaign_name = isset( $campaign['name'] ) ? $campaign['name'] : $campaign_id;

        if( empty( $campaign_id ) ) {
            continue;
        }

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $campaign_name ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id' => $campaign_id,
            'text' => $campaign_name
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_drip_get_campaigns', 'automatorwp_drip_ajax_get_campaigns' );