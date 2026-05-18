<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting variable products
 *
 * @since 1.0.0
 */
function automatorwp_woocommerce_ajax_get_variable_products() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;

    // Get the results
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID AS id, p.post_title AS text
         FROM {$wpdb->posts} AS p
         LEFT JOIN {$wpdb->term_relationships} as tr ON ( p.ID = tr.object_id )
         WHERE tr.term_taxonomy_id IN ( SELECT term_id FROM {$wpdb->terms} WHERE slug IN ('variable') )
			AND p.post_type = 'product'
			AND p.post_status = 'publish'
			AND p.post_title LIKE %s
         ORDER BY p.post_title ASC",
        "%%{$search}%%"
    ) );

    // Parse extra options
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_woocommerce_get_variable_products', 'automatorwp_woocommerce_ajax_get_variable_products' );

/**
 * Ajax function for selecting variable products
 *
 * @since 1.0.0
 */
function automatorwp_woocommerce_ajax_get_variable_subscriptions() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;

    // Get the results
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID AS id, p.post_title AS text
         FROM {$wpdb->posts} AS p
         LEFT JOIN {$wpdb->term_relationships} as tr ON ( p.ID = tr.object_id )
         WHERE tr.term_taxonomy_id IN ( SELECT term_id FROM {$wpdb->terms} WHERE slug IN ('variable','subscription','variable-subscription') )
			AND p.post_type = 'product'
			AND p.post_status = 'publish'
			AND p.post_title LIKE %s
         ORDER BY p.post_title ASC",
        "%%{$search}%%"
    ) );

    // Parse extra options
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_woocommerce_get_variable_subscriptions', 'automatorwp_woocommerce_ajax_get_variable_subscriptions' );

/**
 * Ajax function for selecting product variations
 *
 * @since 1.0.0
 */
function automatorwp_woocommerce_ajax_get_product_variations() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $results = array();

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
    $product_id = isset( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : 0;
   
    // Bail if not event ID provided
    if( $product_id === 0 ) {
        wp_send_json_success( array(
            array(
                'id' => 'any',
                'text' => __( 'Please, choose a variable product first.', 'automatorwp-woocommerce' )
            )
        ) );
        die;
    }

    $product = wc_get_product( $product_id );
	$variations = $product->get_available_variations();

    // Bail if event does not have tickets
    if ( empty( $variations ) ) {
        wp_send_json_success( array(
            array(
                'id' => 'any',
                'text' => __( 'No variations found for this product.', 'automatorwp-woocommerce' )
            )
        ) );
        die;
    }

    foreach ( $variations as $variation ) {
		
		$attributes = array();
		
		foreach( $variation['attributes'] as $attribute ) {
            if( ! empty( $attribute ) ) {
                $attributes[] = $attribute;
            }
        }
		
        $results[] = array(
            'id' => $variation['variation_id'],
            'text'  => implode( ', ', $attributes ),
        );
    }

    // Parse extra options
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_woocommerce_get_product_variations', 'automatorwp_woocommerce_ajax_get_product_variations' );
