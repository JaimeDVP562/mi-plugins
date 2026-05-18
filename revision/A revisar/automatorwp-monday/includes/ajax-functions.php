<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Monday\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Handler to save OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_monday_";
    
    /* sanitize incoming data */
    $consumer_key = sanitize_text_field( $_POST["consumer_key"] );

    if( $consumer_key == '' ) {
        // return error one of the field missing
        wp_send_json_error();
    }else{
        $credentials = get_option( 'automatorwp_settings' );

        $credentials[$prefix . 'consumer_key'] = $consumer_key;
        $credentials = array_filter( $credentials );

        update_option( 'automatorwp_settings', $credentials );

        wp_send_json_success();
    }
}
add_action( 'wp_ajax_automatorwp_monday_save_oauth_credentials', 'automatorwp_monday_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_monday_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'consumer_key'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success();

}
add_action( 'wp_ajax_automatorwp_monday_delete_oauth_credentials', 'automatorwp_monday_ajax_delete_oauth_credentials' );

/**
 * Ajax function for selecting boards
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_get_boards() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $boards = automatorwp_monday_get_boards();

    $results = array();

    foreach ( $boards as $board ) {

        $results[] = array(
            'id' => $board['id'],
            'text' => $board['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_monday_get_boards', 'automatorwp_monday_ajax_get_boards' );

/**
 * Ajax function for selecting items
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_get_items() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get Board ID
    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '1860266842'; // FIXME: hardcoded board ID because the board ID is not being passed

    $items = automatorwp_monday_get_items( $board_id );

    $results = array();

    foreach ( $items as $item ) {

        $results[] = array(
            'id' => $item['id'],
            'text' => $item['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_monday_get_items', 'automatorwp_monday_ajax_get_items' );

/**
 * Ajax function for selecting users
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_get_users() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Get Board ID
    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '1860266842'; // FIXME: hardcoded board ID because the board ID is not being passed

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $users = automatorwp_monday_get_users();

    $results = array();

    foreach ( $users as $user ) {

        $results[] = array(
            'id' => $user['id'],
            'text' => $user['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_monday_get_users', 'automatorwp_monday_ajax_get_users' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_monday_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_monday_';

    $consumer_key = sanitize_text_field( $_POST['consumer_key'] );

    if( empty( $consumer_key ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Monday', 'automatorwp-monday' ) ) );
        return;
    }
    
    $status = automatorwp_monday_check_settings_status(['consumer_key' => $consumer_key]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API consumer_key
    $settings[$prefix . 'consumer_key'] = $consumer_key;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-monday';

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Monday', 'automatorwp-monday' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_monday_authorize',  'automatorwp_monday_ajax_authorize' );