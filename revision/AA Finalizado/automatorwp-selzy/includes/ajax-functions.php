<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Selzy\Ajax_Functions
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
function automatorwp_selzy_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_selzy_';

    $url = automatorwp_selzy_get_url();
    $token = sanitize_text_field( $_POST['token'] );
   
    // Check parameters given
    if( empty( $token ) ) {
        wp_send_json_error( array( 'message' => __( 'API Token is required to connect with Selzy', 'automatorwp-selzy' ) ) );
        return;
    }

    // To get first answer and check the connection
    $response = wp_remote_get( $url . '/getCampaigns?&api_key='.$token, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );

    // Incorrect API token
    if ( isset( $response['response']['code'] ) && $response['response']['code'] !== 200 ){
        wp_send_json_error (array( 'message' => __( 'Please, check your credentials', 'automatorwp-selzy' ) ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client url and API key
    $settings[$prefix . 'token'] = $token;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-selzy';
   
    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Selzy', 'automatorwp-selzy' ),
        'redirect_url' => $admin_url
    ) );

}
add_action( 'wp_ajax_automatorwp_selzy_authorize',  'automatorwp_selzy_ajax_authorize' );

/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_selzy_ajax_get_lists() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $lists = automatorwp_selzy_get_lists();
    
    $results = array();

    // Parse lists results to match select2 results
    foreach ( $lists as $list ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $list['title'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   =>  $list['id'],
            'text' => $list['title']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_selzy_get_lists', 'automatorwp_selzy_ajax_get_lists' );


/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_selzy_ajax_get_tags() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $tags = automatorwp_selzy_get_tags();
    
    $results = array();

    // Parse tags results to match select2 results
    foreach ( $tags as $tag ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $tag['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   =>  $tag['id'],
            'text' => $tag['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_selzy_get_tags', 'automatorwp_selzy_ajax_get_tags' );