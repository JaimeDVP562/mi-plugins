<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\AWeber\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_aweber_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'automatorwp' ) );
    }

    $prefix = 'automatorwp_aweber_';

    $client_id = sanitize_text_field( $_POST['client_id'] );
    $client_secret = sanitize_text_field( $_POST['client_secret'] );
   
    // Check parameters given
    if( empty( $client_id ) || empty( $client_secret ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with AWeber', 'automatorwp' ) ) );
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client id and secret
    $settings[$prefix . 'client_id'] = $client_id;
    $settings[$prefix . 'client_secret'] = $client_secret;
    

    // Update settings
    update_option( 'automatorwp_settings', $settings );

    // Scopes to read and write
    $scope = 'account.read list.read list.write subscriber.read subscriber.write';

    $admin_url = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-aweber');
    $redirect_url = 'https://auth.aweber.com/oauth2/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=' . urlencode( $admin_url ) . '&scope=' . urlencode( $scope );

    // Return the redirect URL
    wp_send_json_success( array(
        'message' => __( 'Settings saved successfully, redirecting to AWeber...', 'automatorwp' ),
        'redirect_url' => $redirect_url
    ) );

}
add_action( 'wp_ajax_automatorwp_aweber_authorize',  'automatorwp_aweber_ajax_authorize' );

/**
 * Ajax function for selecting accounts
 *
 * @since 1.0.0
 */
function automatorwp_aweber_ajax_get_accounts() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $accounts = automatorwp_aweber_get_accounts();
    
    $results = array();

    // Parse accounts results to match select2 results
    foreach ( $accounts as $account ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $account['company'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => $account['id'],
            'text' => $account['company']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_aweber_get_accounts', 'automatorwp_aweber_ajax_get_accounts' );

/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_aweber_ajax_get_lists() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    // Get account ID
    $account_id = isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '';

    $lists = automatorwp_aweber_get_lists( $account_id );
    
    $results = array();

    // Parse accounts results to match select2 results
    foreach ( $lists as $list ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $list['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => $list['id'],
            'text' => $list['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_aweber_get_lists', 'automatorwp_aweber_ajax_get_lists' );
