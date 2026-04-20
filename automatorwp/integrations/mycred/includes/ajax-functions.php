<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\MyCred\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting point types
 *
 * @since 1.0.0
 */
function automatorwp_mycred_ajax_get_point_types() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $point_types = automatorwp_mycred_get_point_types();
    
    $results = array();

    // Parse point_types results to match select2 results
    foreach ( $point_types as $point_type ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $point_type['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => strval($point_type['id']),
            'text' => $point_type['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_mycred_get_point_types', 'automatorwp_mycred_ajax_get_point_types' );