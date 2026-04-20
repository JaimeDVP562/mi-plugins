<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Campaign_Monitor\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_campaign_monitor_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'automatorwp' ) );
    }

    $prefix = 'automatorwp_campaign_monitor_';

    $url = automatorwp_campaign_monitor_get_url();
    $client_id = sanitize_text_field( $_POST['client_id'] );
    $api_key = sanitize_text_field( $_POST['api_key'] );

    // Check parameters given
    if( empty( $client_id  ) || empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'Client ID and API Key are required to connect with Campaign Monitor', 'automatorwp' ) ) );
        return;
    }

    // To get the first answer and check the connection
    $response = wp_remote_get( $url . 'clients.json', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api_key ),
        )
    ) );

    // Check for WP_Error
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        return;
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    // Incorrect API 
    if ( $response_code !== 200 ){
        $error_message = __( 'Please, check your credentials. Server response: ', 'automatorwp' ) . $response_code;
        if ( ! empty( $response_body ) ) {
            $error_message .= ' - ' . $response_body;
        }
        wp_send_json_error( array( 'message' => $error_message ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client url and API key
    $settings[$prefix . 'client_id'] = $client_id;
    $settings[$prefix . 'api_key'] = $api_key;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-campaign_monitor' );

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Campaign Monitor', 'automatorwp' ),
        'redirect_url' => $admin_url
    ) );
}

add_action( 'wp_ajax_automatorwp_campaign_monitor_authorize',  'automatorwp_campaign_monitor_ajax_authorize' );


/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_campaign_monitor_ajax_get_lists() {
    
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $lists = automatorwp_campaign_monitor_get_lists();
    
    $results = array();

    // Parse lists results to match select2 results
    foreach ( $lists as $list ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $list['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   =>  $list['id'],
            'text' => $list['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_campaign_monitor_get_lists', 'automatorwp_campaign_monitor_ajax_get_lists' );
