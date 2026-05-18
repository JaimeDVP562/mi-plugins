<?php
/**
 * Ajax Functions (Select2 Dropdowns)
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Ajax_Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Populate the Addressbook dropdown via AJAX
 */
function automatorwp_sendpulse_ajax_list_addressbooks() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $result = automatorwp_sendpulse_request( 'GET', '/addressbooks' );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    $items = array();
    if ( isset( $result['data'] ) && is_array( $result['data'] ) ) {
        $items = $result['data'];
    } elseif ( is_array( $result ) ) {
        $items = isset( $result[0] ) ? $result : array();
    }

    $results = array();
    foreach ( $items as $item ) {
        if ( isset( $item['id'] ) ) {
            $name = isset( $item['name'] ) ? $item['name'] : $item['id'];
            $results[] = array( 'id' => $item['id'], 'text' => $name );
        }
    }

    if ( function_exists( 'automatorwp_ajax_parse_extra_options' ) ) {
        $results = automatorwp_ajax_parse_extra_options( $results );
    }

    wp_send_json_success( array( 'results' => $results ) );
}
add_action( 'wp_ajax_automatorwp_sendpulse_list_addressbooks', 'automatorwp_sendpulse_ajax_list_addressbooks' );