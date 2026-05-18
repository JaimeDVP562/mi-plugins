<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Slack\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_slack_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_slack_';

    $url = automatorwp_slack_get_url();
    $token = sanitize_text_field( $_POST['token'] );
   
    // Check parameters given
    if( empty( $token ) ) {
        wp_send_json_error( array( 'message' => __( 'API Token is required to connect with Slack', 'automatorwp-slack' ) ) );
        return;
    }

    // To get first answer and check the connection
    $response = wp_remote_get( $url . '/users.list', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    // Incorrect API token
    if ( isset( $response['ok'] ) && $response['ok'] !== true ){
        wp_send_json_error (array( 'message' => __( 'Please, check your credentials', 'automatorwp-slack' ) ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client url and API key
    $settings[$prefix . 'token'] = $token;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-slack';
   
    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Slack', 'automatorwp-slack' ),
        'redirect_url' => $admin_url
    ) );

}
add_action( 'wp_ajax_automatorwp_slack_authorize',  'automatorwp_slack_ajax_authorize' );


/**
 * Ajax function for selecting channels
 *
 * @since 1.0.0
 */
function automatorwp_slack_ajax_get_channels() {
    
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';
    
    // Get Channels
    $channels = automatorwp_slack_get_channels();
        
    $results = array();

    // Parse channels to match select2 results
    foreach ( $channels as $channel ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $channel['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   =>  $channel['id'] ,
            'text' => $channel['name']
        );
    }


    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_slack_get_channels', 'automatorwp_slack_ajax_get_channels' );


/**
 * Ajax function for selecting users
 *
 * @since 1.0.0
 */
function automatorwp_slack_ajax_get_users() {
    
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';
   
    // Get Channel ID
    $channel_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';
   
    // Get Users
    $users = automatorwp_slack_get_users( $channel_id );
    
    $results = array();

    // Parse users results to match select2 results
    foreach ( $users as $user ) {
        
        if( ! empty( $search ) ) {
            if( strpos( strtolower( $user['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => strval( $user['id'] ),
            'text' => $user['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );
    
    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_slack_get_users', 'automatorwp_slack_ajax_get_users' );


/**
 * Ajax function for selecting threads
 *
 * @since 1.0.0
 */
function automatorwp_slack_ajax_get_threads() {
        
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get Channel ID
    $channel_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';
    
    // Get Threads
    $threads = automatorwp_slack_get_threads( $channel_id );
    
    $results = array();

    // Parse spaces results to match select2 results
    foreach ( $threads as $thread ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $thread['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => strval( $thread['id'] ),
            'text' => $thread['text']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_slack_get_threads', 'automatorwp_slack_ajax_get_threads' );



/**
 * Ajax function for selecting channels
 *
 * @since 1.0.0
 */
function automatorwp_slack_ajax_get_reactions() {
    
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';
    
    // Get Channels
    $channels = automatorwp_slack_get_reactions();
      
    $results = array();

    // Parse channels to match select2 results
    foreach ( $channels as $channel ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $channel['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => strval($channel['id']),
            'text' => $channel['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );
    

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_slack_get_reactions', 'automatorwp_slack_ajax_get_reactions' );



?>