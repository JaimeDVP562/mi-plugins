<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\IContact\Ajax_Functions
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
function automatorwp_iContact_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_iContact_";
    
    /* sanitize incoming data */
    $consumer_key = sanitize_text_field( $_POST["consumer_key"] );
    $access_token = sanitize_text_field( $_POST["access_token"] );

    if( $consumer_key == '' && $access_token == '' ) {
        // return error one of the field missing
        wp_send_json_error();
    }else{
        $credentials = get_option( 'automatorwp_settings' );

        $credentials[$prefix . 'consumer_key'] = $consumer_key;
        $credentials[$prefix . 'access_token'] = $access_token;
        $credentials = array_filter( $credentials );

        update_option( 'automatorwp_settings', $credentials );

        wp_send_json_success();
    }
}
add_action( 'wp_ajax_automatorwp_iContact_save_oauth_credentials', 'automatorwp_iContact_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_iContact_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'consumer_key'] = null;
    $credentials[$prefix . 'access_token'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success();

}
add_action( 'wp_ajax_automatorwp_iContact_delete_oauth_credentials', 'automatorwp_iContact_ajax_delete_oauth_credentials' );

/**
 * Ajax function for selecting boards
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_boards() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $boards = automatorwp_iContact_get_boards();

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
add_action( 'wp_ajax_automatorwp_iContact_get_boards', 'automatorwp_iContact_ajax_get_boards' );

/**
 * Ajax function for selecting labels
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_labels() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $labels = automatorwp_iContact_get_labels_from_board( $board_id );

    $results = array();
    
    foreach ( $labels as $label ) {

        $results[] = array(
            'id' => $label['id'],
            'text' => $label['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_iContact_get_labels', 'automatorwp_iContact_ajax_get_labels' );

/**
 * Ajax function for selecting members
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_members() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $members = automatorwp_iContact_get_members_from_board( $board_id );


    foreach ( $members as $member ) {

        $results[] = array(
            'id' => $member['id'],
            'text' => $member['name']
        );
    }
    
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_iContact_get_members', 'automatorwp_iContact_ajax_get_members' );

/**
 * Ajax function for selecting checklists
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_checklists() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $card_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';
    
    $checklists = automatorwp_iContact_get_checklists_from_card( $card_id );

    foreach ( $checklists as $checklist ) {

        $results[] = array(
            'id' => $checklist['id'],
            'text' => $checklist['name']
        );
    }
    
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_iContact_get_checklists', 'automatorwp_iContact_ajax_get_checklists' );

/**
 * Ajax function for selecting lists from boards
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_lists_from_board() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get Board ID
    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $lists = automatorwp_iContact_get_lists_from_board( $board_id );

    $results = array();

    foreach( $lists as $list ) {

        $results[] = array(
            'id' => $list['id'],
            'text' => $list['name']
        );

    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_iContact_get_lists_from_board', 'automatorwp_iContact_ajax_get_lists_from_board' );

/**
 * Ajax function for selecting cards from list
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_get_cards_from_list() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get List ID
    $list_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $cards = automatorwp_iContact_get_cards_from_list( $list_id );

    $results = array();

    foreach( $cards as $card ) {

        $results[] = array(
            'id' => $card['id'],
            'text' => $card['name']
        );

    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_iContact_get_cards_from_list', 'automatorwp_iContact_ajax_get_cards_from_list' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_iContact_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_iContact_';

    $consumer_key = sanitize_text_field( $_POST['consumer_key'] );
    $access_token = sanitize_text_field( $_POST['access_token'] );

    if( empty( $consumer_key ) || empty( $access_token ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with IContact', 'automatorwp-iContact' ) ) );
        return;
    }
    
    $status = automatorwp_iContact_check_settings_status(['consumer_key' => $consumer_key, 'access_token' => $access_token]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API consumer_key and API access_token
    $settings[$prefix . 'consumer_key'] = $consumer_key;
    $settings[$prefix . 'access_token'] = $access_token;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-iContact';

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with IContact', 'automatorwp-iContact' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_iContact_authorize',  'automatorwp_iContact_ajax_authorize' );