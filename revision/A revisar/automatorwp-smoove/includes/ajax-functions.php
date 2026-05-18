<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Smoove\Ajax_Functions
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
function automatorwp_smoove_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_smoove_";
    
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
add_action( 'wp_ajax_automatorwp_smoove_save_oauth_credentials', 'automatorwp_smoove_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_smoove_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'consumer_key'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success();

}
add_action( 'wp_ajax_automatorwp_smoove_delete_oauth_credentials', 'automatorwp_smoove_ajax_delete_oauth_credentials' );

/**
 * Ajax function for selecting boards
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_boards() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $boards = automatorwp_smoove_get_boards();

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
add_action( 'wp_ajax_automatorwp_smoove_get_boards', 'automatorwp_smoove_ajax_get_boards' );

/**
 * Ajax function for selecting labels
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_labels() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $labels = automatorwp_smoove_get_labels_from_board( $board_id );

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
add_action( 'wp_ajax_automatorwp_smoove_get_labels', 'automatorwp_smoove_ajax_get_labels' );

/**
 * Ajax function for selecting members
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_members() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $members = automatorwp_smoove_get_members_from_board( $board_id );


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
add_action( 'wp_ajax_automatorwp_smoove_get_members', 'automatorwp_smoove_ajax_get_members' );

/**
 * Ajax function for selecting checklists
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_checklists() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $card_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';
    
    $checklists = automatorwp_smoove_get_checklists_from_card( $card_id );

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
add_action( 'wp_ajax_automatorwp_smoove_get_checklists', 'automatorwp_smoove_ajax_get_checklists' );

/**
 * Ajax function for selecting lists from boards
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_lists_from_board() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get Board ID
    $board_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $lists = automatorwp_smoove_get_lists_from_board( $board_id );

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
add_action( 'wp_ajax_automatorwp_smoove_get_lists_from_board', 'automatorwp_smoove_ajax_get_lists_from_board' );

/**
 * Ajax function for selecting cards from list
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_get_cards_from_list() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get List ID
    $list_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $cards = automatorwp_smoove_get_cards_from_list( $list_id );

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
add_action( 'wp_ajax_automatorwp_smoove_get_cards_from_list', 'automatorwp_smoove_ajax_get_cards_from_list' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_smoove_ajax_authorize() {
    $prefix = 'automatorwp_smoove_';

    // Obtener y sanitizar la API Key
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';

    if (empty($api_key)) {
        wp_send_json_error(array('message' => __('API Key is required to connect with Smoove', 'automatorwp-smoove')));
        return;
    }

    $status = automatorwp_smoove_check_settings_status(['api_key' => $api_key]);

    if (empty($status)) {
        return;
    }

    $settings = get_option('automatorwp_settings');

    // Guardar API Key
    $settings[$prefix . 'api_key'] = $api_key;

    // Actualizar configuración
    update_option('automatorwp_settings', $settings);

    $admin_url = get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-smoove';

    wp_send_json_success(array(
        'message' => __('Successfully connected to Smoove', 'automatorwp-smoove'),
        'redirect_url' => $admin_url
    ));
}
add_action('wp_ajax_automatorwp_smoove_authorize', 'automatorwp_smoove_ajax_authorize');
