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
function automatorwp_user_registration_get_registration_fields() {

    return array(
        'user_login',
        'user_email',
        'first_name',
        'last_name',
        'nickname',
        'display_name',
        'user_url',
        'description',
        'user_profile' ,
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
function automatorwp_user_registration_get_registration_field_label( $field ) {

    $labels = array(
        'username' => __( 'Username', 'automatorwp' ),
        'email' => __( 'Email', 'automatorwp' ),
        'firstname'=> __( 'FirstName','automatorwp'),
        'last_name' => __( 'Last Name', 'automatorwp'),
        'nickname' => __( 'Nickname', 'automatorwp'),
        'display_name' => __( 'Display Name', 'automatorwp'),
        'user_url' => __( 'Website URL', 'automatorwp'),
        'description' => __('User Bios',  'automatorwp'),
        'user_profile'=> __( 'User who has updated his profile', 'automatorwp'),


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
function automatorwp_user_registration_get_registration_field_preview( $field ) {

    $previews = array(
        'username' => __( 'AutomatorWP', 'automatorwp' ),
        'email' => __( 'contact@automatorwp.com', 'automatorwp' ),
        'firstname' => __( 'Autormator', 'automatorwp'),
        'last_name' => __( 'Doe', 'automatorwp'),
        'nickname' => __( 'johnny', 'automatorwp'),
        'display_name' => __( 'John D', 'automatorwp'),
        'user_url' => __( 'https://example.com', 'automatorwp'),
        'description' => __('User description',  'automatorwp'),
        'user_profile'=> __( 'User who has updated his profile', 'automatorwp'),

    );

    return isset( $previews[$field] ) ? $previews[$field] : '';

}
