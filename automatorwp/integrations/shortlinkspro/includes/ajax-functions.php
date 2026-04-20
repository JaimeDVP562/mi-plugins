<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\ShortLinksPro\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting links
 *
 * @since 1.0.0
 */
function automatorwp_shortlinkspro_ajax_get_links() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $results = array();

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    $links = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, title
        FROM {$ct_table->db->table_name}"
    ) );

    ct_reset_setup_table();
    
    foreach( $links as $link ) {

        if($link->title === '') $link->title = '(no title)';

        $results[] = array(
            'id' => $link->id,
            'text' => $link->title,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_shortlinkspro_get_links', 'automatorwp_shortlinkspro_ajax_get_links', 5 );

/**
 * Ajax function for selecting categories
 *
 * @since 1.0.0
 */
function automatorwp_shortlinkspro_ajax_get_categories() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $results = array();
    
    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_link_categories' );

    $categories = $wpdb->get_results( $wpdb->prepare(
        "SELECT *
        FROM {$ct_table->db->table_name}"
    ) );

    ct_reset_setup_table();
    
    foreach( $categories as $category ) {

        //if($category->name === '') $category->name = '(no title)';

        $results[] = array(
            'id' => $category->id,
            'text' => $category->name,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_shortlinkspro_get_categories', 'automatorwp_shortlinkspro_ajax_get_categories', 5 );

/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_shortlinkspro_ajax_get_tags() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $results = array();
    
    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_link_tags' );

    $tags = $wpdb->get_results( $wpdb->prepare(
        "SELECT *
        FROM {$ct_table->db->table_name}"
    ) );

    ct_reset_setup_table();
    
    foreach( $tags as $tag ) {
        $results[] = array(
            'id' => $tag->id,
            'text' => $tag->name,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_shortlinkspro_get_tags', 'automatorwp_shortlinkspro_ajax_get_tags', 5 );