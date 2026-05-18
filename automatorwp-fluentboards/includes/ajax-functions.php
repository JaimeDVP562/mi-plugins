<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_fluentboard_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'automatorwp-fluentboard' ) );
    }

    $prefix = 'automatorwp_fluentboard_';

    $url = sanitize_text_field( $_POST['url'] );
    $key = sanitize_text_field( $_POST['key'] );

    // Check parameters given
    if( empty( $url ) || empty( $key ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with FluentBoard', 'automatorwp-fluentboard' ) ) );
        return;
    }

    // To get first answer and check the connection
    $response = wp_remote_get( $url . '/api/3', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $key,
            'Content-Type'  => 'application/json'
        ),
        'sslverify' => false
    ) );

    // Incorrect URL or API key
    if ( isset( $response->errors ) ){
        wp_send_json_error (array( 'message' => __( 'Please, check your credentials', 'automatorwp-fluentboard' ) ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save client url and API key
    $settings[$prefix . 'url'] = $url;
    $settings[$prefix . 'key'] = $key;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-fluentboard' );

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with FluentBoard', 'automatorwp-fluentboard' ),
        'redirect_url' => $admin_url
    ) );

}
add_action( 'wp_ajax_automatorwp_fluentboard_authorize',  'automatorwp_fluentboard_ajax_authorize' );


/**
 * Set the default URL value
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_fluentboard_ajax_refresh( ) {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_fluentboard_';

    // Get random characters for slug
    $slug = strtolower( wp_generate_password( 8, false ) );

    $settings = get_option( 'automatorwp_settings' );

    $settings[$prefix . 'webhook'] = get_rest_url() . 'fluentboard/webhooks/' . $slug;
    $settings[$prefix . 'slug'] = $slug;
    update_option( 'automatorwp_settings', $settings);

    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-fluentboard' );

    wp_send_json_success( array(
        'message' => __( 'Webhook URL refreshed', 'automatorwp-fluentboard' ),
        'redirect_url' => $admin_url
    ) );

}

add_action( 'wp_ajax_automatorwp_fluentboard_refresh',  'automatorwp_fluentboard_ajax_refresh' );


/**
 * Ajax function for selecting boards
 *
 * @since 1.0.0
 */
function automatorwp_fluentboards_ajax_get_boards() {

    if ( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error([ 'message' => 'Unauthorized' ]);
    }

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';
    $page   = isset($_REQUEST['page']) ? absint($_REQUEST['page']) : 1;

    $limit = 20;
    $offset = ($page - 1) * $limit;

    $table = $wpdb->prefix . 'fbs_boards';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, title 
             FROM {$table} 
             WHERE title LIKE %s 
             LIMIT %d OFFSET %d",
            '%' . $wpdb->esc_like($search) . '%',
            $limit,
            $offset
        )
    );

    $results = [];

    foreach ( $rows as $row ) {
        $results[] = [
            'id'   => (int) $row->id,
            'text' => $row->title
        ];
    }

    if ( function_exists('automatorwp_ajax_parse_extra_options') ) {
        $results = automatorwp_ajax_parse_extra_options( $results );
    }

    wp_send_json_success( $results );
    die;
}

add_action( 'wp_ajax_automatorwp_fluentboards_get_boards', 'automatorwp_fluentboards_ajax_get_boards' );

function automatorwp_fluentboards_ajax_get_users() {

    if ( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized' ) );
    }

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';

    $users = get_users( array(
        'number' => 20,
        'search' => $search ? '*' . $search . '*' : '',
        'search_columns' => array('user_login','user_email','display_name')
    ) );

    $results = array();

    foreach ( $users as $user ) {
        $results[] = array(
            'id'   => $user->ID,
            'text' => $user->display_name . ' (' . $user->user_email . ')'
        );
    }

    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( $results );
    die;
}

add_action(
    'wp_ajax_automatorwp_fluentboards_get_users',
    'automatorwp_fluentboards_ajax_get_users'
);