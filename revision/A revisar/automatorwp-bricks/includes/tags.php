<?php
/**
 * Tags registry for Bricks Builder
 *
 * @package     AutomatorWP\Bricks\Tags
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Bricks tags
 * * @since 1.0.0
 * @param array $tags The registered tags
 * @return array
 */
function automatorwp_bricks_get_tags( $tags ) {

    // Define the Bricks tag group
    $tags['bricks'] = array(
        'label' => 'Bricks Builder',
        'tags'  => array(
            'form_id' => array(
                'label'   => __( 'Form ID', 'automatorwp-bricks' ),
                'preview' => 'contact_form_1',
            ),
            'form_field' => array(
                'label'   => __( 'Form Field Value', 'automatorwp-bricks' ),
                'preview' => __( 'Value of the specified field', 'automatorwp-bricks' ),
                'type'    => 'text',
                // This allows the user to specify WHICH field ID to get
                'options' => array(
                    'field_id' => array(
                        'name' => __( 'Field ID:', 'automatorwp-bricks' ),
                        'desc' => __( 'Enter the ID of the Bricks form field (e.g. "email" or "form-field-123")', 'automatorwp-bricks' ),
                        'type' => 'text',
                    ),
                ),
            ),
        )
    );

    return $tags;
}
add_filter( 'automatorwp_get_tags', 'automatorwp_bricks_get_tags' );

/**
 * Replace Bricks tags with actual values
 * * @since 1.0.0
 */
function automatorwp_bricks_get_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {
    
    // Check if the trigger belongs to our integration
    $trigger_args = automatorwp_get_trigger( $trigger->type );

    if ( ! isset( $trigger_args['integration'] ) || $trigger_args['integration'] !== 'bricks' ) {
        return $replacement;
    }

    // Logic to replace tags
    switch( $tag_name ) {
        
        case 'form_id':
            // We retrieve the Form ID stored during the trigger execution
            $replacement = automatorwp_get_log_meta( $log->id, 'form_id', true );
            break;

        case 'form_field':
            // 1. Get the specific field ID requested by the user in the recipe
            $field_id = automatorwp_get_tag_option( $content, 'field_id' );
            
            // 2. Get all fields submitted (stored as an array in log meta)
            $fields = automatorwp_get_log_meta( $log->id, 'fields', true );

            // 3. Use our helper function from functions.php to find the value
            if ( ! empty( $field_id ) && ! empty( $fields ) ) {
                $value = automatorwp_bricks_get_field_value( $fields, $field_id );
                $replacement = ( $value !== false ) ? $value : $replacement;
            }
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_bricks_get_tag_replacement', 10, 6 );