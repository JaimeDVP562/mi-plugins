<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Tripetto\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to forms
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_tripetto_options_cb_form( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any form', 'automatorwp-tripetto' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $field->escaped_value ) ) {
        $values = is_array( $field->escaped_value ) ? $field->escaped_value : array( $field->escaped_value );

        foreach( $values as $form_id ) {

            if( $form_id === $none_value ) continue;
            $options[$form_id] = automatorwp_tripetto_get_form_name( $form_id );
        }
    }

    return $options;
}

/**
 * Get the form name
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return string|null
 */
function automatorwp_tripetto_get_form_name( $form_id ) {

    // Empty name if no ID provided
    if( absint( $form_id ) === 0 ) {
        return '';
    }
    
    global $wpdb;

    $form = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}tripetto_forms WHERE id = %d",
            $form_id
        )
    );

    return ( $form ? $form->name : '' );

}

/**
 * Get form fields values
 *
 * @since 1.0.0
 *
 * @param array $dataset
 *
 * @return array
 */
function automatorwp_tripetto_get_form_fields_values( $dataset ) {
    $form_fields = array();

    // Verificar si dataset tiene la propiedad fields
    if ( ! isset( $dataset['fields'] ) && ! isset( $dataset->fields ) ) {
        return $form_fields;
    }

    // Obtener los campos (sea objeto o array)
    $fields = is_array( $dataset ) ? $dataset['fields'] : $dataset->fields;

    // Procesar cada campo
    foreach ( $fields as $field ) {
        // Asegurarse de que tenemos un name y un value
        if ( isset( $field['name'] ) || isset( $field->name ) ) {
            $name = isset( $field['name'] ) ? $field['name'] : $field->name;
            $value = '';

            // Obtener el valor (puede estar en diferentes propiedades)
            if ( isset( $field['value'] ) ) {
                $value = $field['value'];
            } elseif ( isset( $field->value ) ) {
                $value = $field->value;
            } elseif ( isset( $field['string'] ) ) {
                $value = $field['string'];
            } elseif ( isset( $field->string ) ) {
                $value = $field->string;
            }

            $form_fields[$name] = $value;
        }
    }

    // Check for AutomatorWP 1.4.4
    if( function_exists( 'automatorwp_utilities_pull_array_values' ) ) {
        $form_fields = automatorwp_utilities_pull_array_values( $form_fields );
    }

    return $form_fields;
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
function automatorwp_tripetto_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        // Skip if trigger is not from this integration
        if( $trigger_args['integration'] !== 'tripetto' ) {
            continue;
        }

        // Get the last trigger log (where data for tags replacement will be get
        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $form_fields = ct_get_object_meta( $log->id, 'form_fields', true );
        ct_reset_setup_table();

        // Skip if not form fields
        if( ! is_array( $form_fields ) ) {
            continue;
        }

        // Look for form field tags
        preg_match_all( "/\{t:" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                // Replace {t:ID:form_field:NAME} by the field value
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{t:' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

        // Look for form field tags
        preg_match_all( "/\{" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                // Replace {ID:form_field:NAME} by the field value
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

    }

    if( count( $new_replacements ) ) {

        $tags = array_keys( $new_replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $new_replacements, $parsed_content );

    }

    return $parsed_content;

}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_tripetto_parse_automation_tags', 10, 5 );