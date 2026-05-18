<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Google_Sheets\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_google_sheets_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_google_sheets_';

    $client_id = sanitize_text_field( $_POST['client_id'] );
    $client_secret = sanitize_text_field( $_POST['client_secret'] );
   
    // Check parameters given
    if( empty( $client_id ) || empty( $client_secret ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Google Sheets', 'automatorwp-google-sheets' ) ) );
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client id and secret
    $settings[$prefix . 'client_id'] = $client_id;
    $settings[$prefix . 'client_secret'] = $client_secret;
    

    // Update settings
    update_option( 'automatorwp_settings', $settings );

    // Allows read/write access to the user's sheets and their properties.
    $scope = 'https://www.googleapis.com/auth/drive';

    // Displays accounts to get consent
    $prompt = 'select_account consent';
    $state = mb_substr($prefix, 0, -1);
    $admin_url = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-google_sheets');
    $redirect_url = 'https://accounts.google.com/o/oauth2/v2/auth?response_type=code&access_type=offline&include_granted_scopes=true&client_id=' . $client_id . '&redirect_uri=' . urlencode( $admin_url ) . '&state=' . urlencode( $state ) . '&scope=' . urlencode( $scope ) . '&prompt=' . urlencode( $prompt );

    // Return the redirect URL
    wp_send_json_success( array(
        'message' => __( 'Settings saved successfully, redirecting to Google Sheets...', 'automatorwp-google-sheets' ),
        'redirect_url' => $redirect_url
    ) );

}
add_action( 'wp_ajax_automatorwp_google_sheets_authorize',  'automatorwp_google_sheets_ajax_authorize' );


/**
 * Ajax function for selecting spreadsheets
 *
 * @since 1.0.0
 */
function automatorwp_google_sheets_ajax_get_spreadsheets() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $spreadsheets = automatorwp_google_sheets_get_spreadsheets();
    $results = array();

    // Parse spreadsheets results to match select2 results
    foreach ( $spreadsheets as $spreadsheet ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $spreadsheet['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => $spreadsheet['id'],
            'text' => $spreadsheet['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_google_sheets_get_spreadsheets', 'automatorwp_google_sheets_ajax_get_spreadsheets' );


/**
 * Ajax function for selecting workingsheets
 *
 * @since 1.0.0
 */
function automatorwp_google_sheets_ajax_get_worksheets() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    // Get Spreadsheet ID
    $spreadsheet_id = isset( $_REQUEST['table'] ) ? $_REQUEST['table'] : '';

    $worksheets = automatorwp_google_sheets_get_worksheets( $spreadsheet_id );

    $results = array();

    // Parse workingsheets results to match select2 results
    foreach ( $worksheets as $worksheet ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $worksheet['title'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }
        
        $results[] = array(
            'id' => $worksheet['id'],
            'text' => $worksheet['title']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_google_sheets_get_worksheets', 'automatorwp_google_sheets_ajax_get_worksheets' );