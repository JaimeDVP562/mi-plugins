<?php
/**
 * AutomatorWP - MetForm Field Displayed Value Tag
 * 
 * Extensión para: automatorwp-metform/includes/functions.php
 * 
 * Agrega soporte para obtener valores mostrados (labels) de campos MetForm.
 * 
 * Uso: {t:TRIGGER_ID:metform_field_displayed:FIELD_NAME} o 
 *      {TRIGGER_ID:metform_field_displayed:FIELD_NAME}
 */

if( !defined( 'ABSPATH' ) ) exit;

// ============================================================================
// 1. EXTENSIÓN DEL PARSER DE TAGS - Agregar a functions.php
// ============================================================================

/**
 * Custom tags replacements para MetForm - Valor mostrado de campos
 * 
 * Esta función se agrega al mismo hook que automatorwp_metform_parse_automation_tags
 * pero con una prioridad diferente para procesar tags de valores mostrados.
 *
 * @since 1.0.0
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 * @return string
 */
function automatorwp_metform_parse_automation_tags_displayed( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        // Skip if trigger is not from this integration
        if( $trigger_args['integration'] !== 'metform' ) {
            continue;
        }

        // Get the last trigger log (where data for tags replacement will be get)
        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $form_id = ct_get_object_meta( $log->id, 'form_id', true );
        $form_fields = ct_get_object_meta( $log->id, 'form_fields', true );
        ct_reset_setup_table();

        // Skip if not form fields
        if( ! is_array( $form_fields ) ) {
            continue;
        }

        // Look for form field displayed value tags - trigger specific with "t:" prefix
        preg_match_all( "/\{t:" . $trigger->id . ":metform_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                
                if( isset( $form_fields[$field_name] ) ) {
                    $field_value = $form_fields[$field_name];
                    $displayed_value = automatorwp_metform_get_field_displayed_value( 
                        $form_id, 
                        $field_name, 
                        $field_value 
                    );
                    $new_replacements['{t:' . $trigger->id . ':metform_field_displayed:' . $field_name . '}'] = $displayed_value;
                }
            }

        }

        // Look for form field displayed value tags - without "t:" prefix
        preg_match_all( "/\{" . $trigger->id . ":metform_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                
                if( isset( $form_fields[$field_name] ) ) {
                    $field_value = $form_fields[$field_name];
                    $displayed_value = automatorwp_metform_get_field_displayed_value( 
                        $form_id, 
                        $field_name, 
                        $field_value 
                    );
                    $new_replacements['{' . $trigger->id . ':metform_field_displayed:' . $field_name . '}'] = $displayed_value;
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
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_metform_parse_automation_tags_displayed', 11, 5 );

// ============================================================================
// 2. FUNCIONES AUXILIARES - Agregar a functions.php
// ============================================================================

/**
 * Get the displayed value (label) of a MetForm field
 * 
 * For fields with options (checkbox, radio, select), maps the internal value
 * to its displayed label. For other fields, returns the value as-is.
 * 
 * @since 1.0.0
 * @param int       $form_id        The MetForm form ID
 * @param string    $field_name     The field name/key
 * @param mixed     $field_value    The field value (internal value)
 * @return string|array             The displayed value/label
 */
function automatorwp_metform_get_field_displayed_value( $form_id, $field_name, $field_value ) {
    
    // If value is empty, return as-is
    if( empty( $field_value ) ) {
        return $field_value;
    }
    
    // Get the form configuration
    $form_config = automatorwp_metform_get_form_config( $form_id );
    
    if( ! $form_config ) {
        return $field_value;
    }
    
    // Get field configuration
    $field_config = automatorwp_metform_get_field_config( $form_config, $field_name );
    
    if( ! $field_config ) {
        return $field_value;
    }
    
    $field_type = isset( $field_config['type'] ) ? $field_config['type'] : '';
    
    // Handle choice-type fields
    $choice_types = array( 'radio', 'checkbox', 'select', 'multiselect', 'dropdown' );
    
    if( in_array( $field_type, $choice_types ) ) {
        
        $options = isset( $field_config['options'] ) ? $field_config['options'] : array();
        
        // Handle array values (multiselect, multiple checkboxes)
        if( is_array( $field_value ) ) {
            $displayed_values = array();
            
            foreach( $field_value as $value ) {
                $displayed_values[] = automatorwp_metform_find_option_label( $value, $options );
            }
            
            return implode( ', ', array_filter( $displayed_values ) );
        } else {
            // Single value
            return automatorwp_metform_find_option_label( $field_value, $options );
        }
    }
    
    // For non-choice fields, return value as-is
    return $field_value;
}

/**
 * Get MetForm form configuration
 * 
 * @since 1.0.0
 * @param int $form_id The form ID
 * @return array|false Form configuration or false if not found
 */
function automatorwp_metform_get_form_config( $form_id ) {
    
    // MetForm stores forms as posts with post_type 'metform_form'
    $form_post = get_post( $form_id );
    
    if( ! $form_post || $form_post->post_type !== 'metform_form' ) {
        return false;
    }
    
    // Get form meta data
    $form_data = get_post_meta( $form_id, 'metform_form_data', true );
    
    if( ! $form_data ) {
        return false;
    }
    
    // MetForm may have the config in 'fields' or 'form_data'
    if( isset( $form_data['fields'] ) ) {
        return $form_data;
    } elseif( isset( $form_data['form_data'] ) ) {
        return $form_data['form_data'];
    }
    
    return $form_data;
}

/**
 * Get field configuration from MetForm form
 * 
 * @since 1.0.0
 * @param array     $form_config    The form configuration
 * @param string    $field_name     The field name/key to find
 * @return array|false              Field configuration or false if not found
 */
function automatorwp_metform_get_field_config( $form_config, $field_name ) {
    
    if( ! isset( $form_config['fields'] ) || ! is_array( $form_config['fields'] ) ) {
        return false;
    }
    
    foreach( $form_config['fields'] as $field ) {
        
        // MetForm stores field name in 'name' property
        $current_field_name = isset( $field['name'] ) ? $field['name'] : '';
        
        if( $current_field_name === $field_name ) {
            return $field;
        }
    }
    
    return false;
}

/**
 * Find the label for a given value in MetForm field options
 * 
 * @since 1.0.0
 * @param mixed     $value      The internal value to find
 * @param array     $options    The options list
 * @return string               The label if found, otherwise returns the value
 */
function automatorwp_metform_find_option_label( $value, $options ) {
    
    if( ! is_array( $options ) ) {
        return $value;
    }
    
    foreach( $options as $option ) {
        
        // Parse option based on format
        $option_value = null;
        $option_label = null;
        
        if( is_string( $option ) ) {
            // Simple string format
            $option_value = $option;
            $option_label = $option;
        } elseif( is_array( $option ) ) {
            // Array format
            $option_value = isset( $option['value'] ) ? $option['value'] : null;
            $option_label = isset( $option['label'] ) ? $option['label'] : 
                            ( isset( $option['text'] ) ? $option['text'] : null );
        } elseif( is_object( $option ) ) {
            // Object format
            $option_value = isset( $option->value ) ? $option->value : null;
            $option_label = isset( $option->label ) ? $option->label : 
                            ( isset( $option->text ) ? $option->text : null );
        }
        
        if( $option_value !== null && ( $option_value === $value || $option_value == $value ) ) {
            return $option_label ? $option_label : $value;
        }
    }
    
    // Value not found in options, return as-is
    return $value;
}
