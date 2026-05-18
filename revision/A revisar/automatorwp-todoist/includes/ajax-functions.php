<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Todoist\Ajax_Functions
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
function automatorwp_todoist_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_todoist_";
    
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
add_action( 'wp_ajax_automatorwp_todoist_save_oauth_credentials', 'automatorwp_todoist_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_todoist_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_todoist_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'consumer_key'] = null;
    $credentials[$prefix . 'access_token'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success();

}
add_action( 'wp_ajax_automatorwp_todoist_delete_oauth_credentials', 'automatorwp_todoist_ajax_delete_oauth_credentials' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_todoist_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_todoist_';

    $consumer_key = sanitize_text_field( $_POST['consumer_key'] );

    if( empty( $consumer_key ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Todoist', 'automatorwp-todoist' ) ) );
        return;
    }
    
    $status = automatorwp_todoist_check_settings_status(['consumer_key' => $consumer_key]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API consumer_key and API access_token
    $settings[$prefix . 'consumer_key'] = $consumer_key;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-todoist';

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Todoist', 'automatorwp-todoist' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_todoist_authorize',  'automatorwp_todoist_ajax_authorize' );

/**
 * Ajax function for selecting project
 *
 * @since 1.0.0
 */
function automatorwp_todoist_ajax_get_projects() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $projects = automatorwp_todoist_get_projects();

    $results = array();

    foreach ( $projects as $project ) {

        $results[] = array(
            'id' => $project['id'],
            'text' => $project['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_todoist_get_projects', 'automatorwp_todoist_ajax_get_projects' );

/**
 * Ajax function for selecting project
 *
 * @since 1.0.0
 */
function automatorwp_todoist_ajax_get_tasks() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $projects = automatorwp_todoist_get_tasks();

    $results = array();

    foreach ( $projects as $project ) {

        $results[] = array(
            'id' => $project['id'],
            'text' => $project['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_todoist_get_tasks', 'automatorwp_todoist_ajax_get_tasks' );