<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the registration fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_user_wishlist_get_registration_fields() {

    return array(
        'product',
        'loop',
        
    );

}

/**
 * Helper function to get an registration fields label
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_user_wishlist_get_registration_field_label( $field ) {

    $labels = array(
        'product' => __( 'ProductID', 'automatorwp' ),
        'loop' => __( 'Loop', 'automatorwp' ),


     );

    return isset( $labels[$field] ) ? $labels[$field] : '';
}


/**
 * Helper function to get an registration field preview
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_user_wishlist_get_registration_field_preview( $field ) {

    $previews = array(
        'product' => __( ' Product 1', 'automatorwp' ),
        'loop' => __( ' loop ', 'automatorwp' ),
    );

    return isset( $previews[$field] ) ? $previews[$field] : '';

}
