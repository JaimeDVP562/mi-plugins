<?php

/**
 * Bricks Integration Functions
 *
 * @package     AutomatorWP\Bricks\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get available Bricks forms
 * * Useful for populating dropdowns in AutomatorWP triggers.
 *
 * @since 1.0.0
 *
 * @return array List of form IDs and names [id => name]
 */
function automatorwp_bricks_get_forms() {

    $forms = array();
    
    // We query Bricks templates or posts that might contain forms
    // or simply return a descriptive placeholder if manual ID entry is preferred.
    $args = array(
        'post_type'      => array( 'page', 'bricks_template' ),
        'posts_per_page' => -1,
        'fields'         => 'ids'
    );

    $posts = get_posts( $args );

    foreach ( $posts as $post_id ) {
        // Bricks stores data in meta. This is a simplified approach to identify forms.
        $bricks_data = get_post_meta( $post_id, '_bricks_page_content_2', true );
        
        if ( is_array( $bricks_data ) ) {
            // Recursive search for 'form' elements could be implemented here
            $forms[$post_id] = sprintf( __( 'Page/Template: %s', 'automatorwp-bricks' ), get_the_title( $post_id ) );
        }
    }

    return $forms;
}

/**
 * Bridge function to catch Bricks Form submissions
 * * Hooks into Bricks native custom action to trigger AutomatorWP events.
 *
 * @since 1.0.0
 *
 * @param Bricks\Forms\Form $form The Bricks form instance
 */
add_action( 'bricks/form/custom_action', 'automatorwp_bricks_form_submission_bridge', 10, 1 );

function automatorwp_bricks_form_submission_bridge( $form ) {

    $settings = $form->get_settings();
    $fields   = $form->get_fields();
    
    $form_id = isset( $settings['formId'] ) ? $settings['formId'] : '';

    if ( empty( $form_id ) ) {
        return;
    }

    $user_id = get_current_user_id();
    do_action( 'automatorwp_bricks_form_submitted_event', $user_id, $form_id, $fields );
}

/**
 * Helper function to extract a specific field value from a Bricks submission
 * * Useful for mapping Bricks fields to AutomatorWP tags.
 *
 * @since 1.0.0
 *
 * @param array  $fields     The fields array from Bricks
 * @param string $field_id   The specific field ID to look for
 *
 * @return string|false
 */
function automatorwp_bricks_get_field_value( $fields, $field_id ) {

    if ( ! is_array( $fields ) ) {
        return false;
    }

    foreach ( $fields as $field ) {
        if ( isset( $field['id'] ) && $field['id'] === $field_id ) {
            return isset( $field['value'] ) ? $field['value'] : '';
        }
    }

    return false;
}

/**
 * Check if Bricks Builder is active
 *
 * @since 1.0.0
 *
 * @return bool
 */
function automatorwp_bricks_is_active() {

    return defined( 'BRICKS_VERSION' );
}