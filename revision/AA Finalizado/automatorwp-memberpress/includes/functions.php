<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\MemberPress\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the member fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_memberpress_get_member_fields() {

    return array(
        'ID',
        'user_email',
        'first_name',
        'last_name',
    );

}

/**
 * Helper function to get an member field label
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_memberpress_get_member_field_label( $field ) {

    $labels = array(
        'ID' => __( 'ID', 'automatorwp-memberpress' ),
        'user_email' => __( 'Email', 'automatorwp-memberpress' ),
        'first_name' => __( 'First name', 'automatorwp-memberpress' ),
        'last_name' => __( 'Last name', 'automatorwp-memberpress' ),
    );

    return isset( $labels[$field] ) ? $labels[$field] : '';

}

/**
 * Helper function to get an member field preview
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_memberpress_get_member_field_preview( $field ) {

    $previews = array(
        'ID' => __( '123', 'automatorwp-memberpress' ),
        'user_email' => __( 'contact@automatorwp.com', 'automatorwp-memberpress' ),
        'first_name' => __( 'AutomatorWP', 'automatorwp-memberpress' ),
        'last_name' => __( 'Plugin', 'automatorwp-memberpress' ),
    );

    return isset( $previews[$field] ) ? $previews[$field] : '';

}

