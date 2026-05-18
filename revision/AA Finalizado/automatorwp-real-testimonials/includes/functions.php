<?php
/**
 * Functions
 *
 * @package     AutomatorWP\RealTestimonials\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to testimonials
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_realtestimonials_options_cb_testimonial( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any testimonial', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $testimonial_id ) {

            // Skip option none
            if( $testimonial_id === $none_value ) {
                continue;
            }

            $options[$testimonial_id] = get_the_title( $testimonial_id );
        }
    }

    return $options;

}

/**
 * Get testimonial fields
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function automatorwp_realtestimonials_get_testimonial_fields( $post_id ) {

    $fields = array();

    $meta = get_post_custom( $post_id );

    // Loop all fields
    if ( $meta ) {
        foreach ( $meta as $key => $values ) {
            $fields[$key] = $values[0];
        }
    }

    $post = get_post( $post_id );
    if ( $post ) {
        $fields['testimonial_title']   = $post->post_title;
        $fields['testimonial_content'] = $post->post_content;
    }

    // Check for AutomatorWP 1.4.4
    if( function_exists( 'automatorwp_utilities_pull_array_values' ) ) {
        $fields = automatorwp_utilities_pull_array_values( $fields );
    }

    return $fields;

}

/**
 * Custom tags replacements
 *
 * @since 1.0.0
 *
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 *
 * @return string
 */
function automatorwp_realtestimonials_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        // Skip if trigger is not from this integration
        if( ! isset( $trigger_args['integration'] ) || strtolower( $trigger_args['integration'] ) !== 'realtestimonials' ) {
            continue;
        }

        // Get the last trigger log (where data for tags replacement will be get
        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $fields = ct_get_object_meta( $log->id, 'testimonial_fields', true );
        ct_reset_setup_table();

        // Skip if not fields
        if( ! is_array( $fields ) ) {
            continue;
        }

        // Look for testimonial field tags
        preg_match_all( "/\{t:" . $trigger->id . ":testimonial_field:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( isset( $matches[1] ) ) {
            foreach( $matches[1] as $index => $field_key ) {
                $trimmed_key = trim( $field_key );
                
                if( isset( $fields[$trimmed_key] ) ) {
                    // Reemplazamos la etiqueta completa original (incluyendo espacios si los hubiera)
                    $full_tag = $matches[0][$index];
                    $new_replacements[$full_tag] = $fields[$trimmed_key];
                }
            }
        }
    }

    if( ! empty( $new_replacements ) ) {
        $parsed_content = str_replace( array_keys( $new_replacements ), array_values( $new_replacements ), $parsed_content );
    }

    return $parsed_content;
}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_realtestimonials_parse_automation_tags', 10, 5 );