<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\RealTestimonials\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting testimonials
 *
 * @since 1.0.0
 */
function automatorwp_realtestimonials_ajax_get_testimonial() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';

    $results = array();

    // Get testimonials
    $args = array(
        'post_type'      => 'spt_testimonial',
        'posts_per_page' => 10,
        's'              => $search,
        'post_status'    => array( 'publish', 'pending', 'draft' )
    );

    $testimonials = get_posts( $args );

    foreach( $testimonials as $testimonial ) {
        $results[] = array(
            'id'   => $testimonial->ID,
            'text' => $testimonial->post_title,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_realtestimonials_get_testimonial', 'automatorwp_realtestimonials_ajax_get_testimonial', 5 );