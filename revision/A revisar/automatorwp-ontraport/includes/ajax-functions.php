<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Ontraport\Ajax_Functions
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
function automatorwp_ontraport_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_ontraport_";
    
    /* sanitize incoming data */
    $app_id = sanitize_text_field( $_POST["app_id"] );
    $api_key = sanitize_text_field( $_POST["api_key"] );

    if( $app_id == '' && $api_key == '' ) {
        // return error one of the field missing
        wp_send_json_error();
    }else{
        $credentials = get_option( 'automatorwp_settings' );

        $credentials[$prefix . 'app_id'] = $app_id;
        $credentials[$prefix . 'api_key'] = $api_key;
        $credentials = array_filter( $credentials );

        update_option( 'automatorwp_settings', $credentials );

        wp_send_json_success();
    }
}
add_action( 'wp_ajax_automatorwp_ontraport_save_oauth_credentials', 'automatorwp_ontraport_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_ontraport_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'app_id'] = null;
    $credentials[$prefix . 'api_key'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success();

}
add_action( 'wp_ajax_automatorwp_ontraport_delete_oauth_credentials', 'automatorwp_ontraport_ajax_delete_oauth_credentials' );

/**
 * Ajax function for selecting boards
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_boards() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $boards = automatorwp_ontraport_get_boards();

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
add_action( 'wp_ajax_automatorwp_ontraport_get_boards', 'automatorwp_ontraport_ajax_get_boards' );

/**
 * Ajax function for selecting labels
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_labels() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $labels = automatorwp_ontraport_get_labels_from_board( $board_id );

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
add_action( 'wp_ajax_automatorwp_ontraport_get_labels', 'automatorwp_ontraport_ajax_get_labels' );

/**
 * Ajax function for selecting members
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_members() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $members = automatorwp_ontraport_get_members_from_board( $board_id );


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
add_action( 'wp_ajax_automatorwp_ontraport_get_members', 'automatorwp_ontraport_ajax_get_members' );

/**
 * Ajax function for selecting checklists
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_checklists() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $card_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';
    
    $checklists = automatorwp_ontraport_get_checklists_from_card( $card_id );

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
add_action( 'wp_ajax_automatorwp_ontraport_get_checklists', 'automatorwp_ontraport_ajax_get_checklists' );

/**
 * Ajax function for selecting lists from boards
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_lists_from_board() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get Board ID
    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $lists = automatorwp_ontraport_get_lists_from_board( $board_id );

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
add_action( 'wp_ajax_automatorwp_ontraport_get_lists_from_board', 'automatorwp_ontraport_ajax_get_lists_from_board' );

/**
 * Ajax function for selecting cards from list
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_cards_from_list() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get List ID
    $list_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $cards = automatorwp_ontraport_get_cards_from_list( $list_id );

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
add_action( 'wp_ajax_automatorwp_ontraport_get_cards_from_list', 'automatorwp_ontraport_ajax_get_cards_from_list' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_ontraport_';

    $app_id = sanitize_text_field( $_POST['app_id'] );
    $api_key = sanitize_text_field( $_POST['api_key'] );

    if( empty( $app_id ) || empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Ontraport', 'automatorwp-ontraport' ) ) );
        return;
    }
    
    $status = automatorwp_ontraport_check_settings_status(['app_id' => $app_id, 'api_key' => $api_key]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API Api_key and API App_id
    $settings[$prefix . 'app_id'] = $app_id;
    $settings[$prefix . 'api_key'] = $api_key;
    

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-ontraport';

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Ontraport', 'automatorwp-ontraport' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_ontraport_authorize',  'automatorwp_ontraport_ajax_authorize' );
/**
 * Ajax function for selecting contacts
 *
 * @since 1.0.0
 */
function automatorwp_ontraport_ajax_get_contacts() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $contacts = automatorwp_ontraport_get_contacts();

    $results = array();

    foreach ( $contacts as $contact ) {

        $results[] = array(
            'id' => $contact['id'],
            'text' => $contact['firstname']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_ontraport_get_contacts', 'automatorwp_ontraport_ajax_get_contacts' );
