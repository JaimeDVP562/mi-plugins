<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Amelia\includes\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting single events
 *
 * @since 1.0.0
 */
function automatorwp_fluentbooking_ajax_get_single_events() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $events = automatorwp_fluentbooking_get_single_events( );

    $results = array();

    // Parse service results to match select2 results
    foreach ( $events as $event ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $event['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }
        
        $results[] = array(
            'id'    => $event['id'],
            'text'  => $event['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_fluentbooking_get_single_events', 'automatorwp_fluentbooking_ajax_get_single_events' );

/**
 * Ajax function for selecting team events
 *
 * @since 1.0.0
 */
function automatorwp_fluentbooking_ajax_get_team_events() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $events = automatorwp_fluentbooking_get_team_events( );

    $results = array();

    // Parse service results to match select2 results
    foreach ( $events as $event ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $event['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }
        
        $results[] = array(
            'id'    => $event['id'],
            'text'  => $event['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_fluentbooking_get_team_events', 'automatorwp_fluentbooking_ajax_get_team_events' );

