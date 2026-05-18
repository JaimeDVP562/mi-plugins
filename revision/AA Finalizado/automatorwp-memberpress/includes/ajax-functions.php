<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\MemberPress\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting one-time memberships
 *
 * @since 1.0.0
 */
function automatorwp_memberpress_ajax_get_lifetime_memberships() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $results = array();

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;

    $posts = get_posts( array(
        's'                 => $search,
        'post_type'         => 'memberpressproduct',
        'posts_per_page'    => 20,
        'page'              => $page,
        'post_status'       => 'publish',
        'meta_query'        => array(
            array(
                'key'       => '_mepr_product_period_type',
                'value'     => 'lifetime',
                'compare'   => '=',
            )
        )
    ) );

    foreach ( $posts as $post ) {

        // Results should meet Select2 structure
        $results[] = array(
            'id' => $post->ID,
            'text' => $post->post_title,
        );

    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_memberpress_get_lifetime_memberships', 'automatorwp_memberpress_ajax_get_lifetime_memberships' );

/**
 * Ajax function for selecting recurring memberships
 *
 * @since 1.0.0
 */
function automatorwp_memberpress_ajax_get_recurring_memberships() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $results = array();

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;

    $posts = get_posts( array(
        's'                 => $search,
        'post_type'         => 'memberpressproduct',
        'posts_per_page'    => 20,
        'page'              => $page,
        'post_status'       => 'publish',
        'meta_query'        => array(
            array(
                'key'       => '_mepr_product_period_type',
                'value'     => 'lifetime',
                'compare'   => '!=',
            )
        )
    ) );

    foreach ( $posts as $post ) {

        // Results should meet Select2 structure
        $results[] = array(
            'id' => $post->ID,
            'text' => $post->post_title,
        );

    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_memberpress_get_recurring_memberships', 'automatorwp_memberpress_ajax_get_recurring_memberships' );