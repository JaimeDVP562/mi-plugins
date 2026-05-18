<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Breakdance\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Check if a post is built with Breakdance
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return bool
 */
function automatorwp_breakdance_is_breakdance_page( $post_id ) {

    return ! empty( get_post_meta( $post_id, '_breakdance_data', true ) );

}

/**
 * Get form fields values from Breakdance form submission
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function automatorwp_breakdance_get_form_fields_values( $fields ) {

    $form_fields = array();

    foreach ( $fields as $field_name => $field_value ) {
        // Sanitize field name to prevent injection attacks
        $sanitized_field_name = sanitize_key( $field_name );
        
        // Sanitize field value based on type
        if ( is_array( $field_value ) ) {
            $form_fields[$sanitized_field_name] = array_map( 'sanitize_text_field', $field_value );
        } else {
            $form_fields[$sanitized_field_name] = sanitize_text_field( $field_value );
        }
    }

    // Check for AutomatorWP 1.4.4
    $form_fields = automatorwp_utilities_pull_array_values( $form_fields );

    return $form_fields;

}