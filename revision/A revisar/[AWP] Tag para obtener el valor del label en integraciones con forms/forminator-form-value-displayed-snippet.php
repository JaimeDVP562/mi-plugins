<?php
/**
 * AutomatorWP - Forminator Form Field Displayed Value Tag
 * 
 * Integración completa para obtener valores mostrados (labels) de campos Forminator.
 * Puede ser parte de una nueva integración: automatorwp-forminator/includes/tags.php
 * 
 * Uso: {forminator_field_displayed:FIELD_ID} o {ID:forminator_field_displayed:FIELD_ID}
 */

if( !defined( 'ABSPATH' ) ) exit;

// ============================================================================
// OPCIÓN 1: INTEGRACIÓN COMO AUTOMATORWP ADDON
// ============================================================================
// Si se crea un addon completo automatorwp-forminator

/**
 * Register Forminator form field displayed value tags
 * 
 * Hook: automatorwp_init (priority 20)
 */
function automatorwp_forminator_register_tags() {
    
    if( ! function_exists( 'automatorwp_register_tag' ) ) {
        return;
    }
    
    // Register the global tag for getting any Forminator field displayed value
    automatorwp_register_tag( 'forminator_field_displayed', array(
        'integration'   => 'forminator',
        'label'         => __( 'Forminator Field Displayed Value', 'automatorwp-forminator' ),
        'schema'        => 'forminator_field_displayed:{field_id}',
        'preview'       => __( 'Displayed value (label) of a Forminator field. For choice fields (checkbox, radio, dropdown), returns the label instead of the internal value.', 'automatorwp-forminator' ),
        'function'      => 'automatorwp_forminator_tag_replacement',
        'parse_content' => 'automatorwp_forminator_parse_tag_content',
    ) );
}
add_action( 'automatorwp_init', 'automatorwp_forminator_register_tags', 20 );

/**
 * Parse trigger-specific Forminator field displayed value tags
 * 
 * Hook: automatorwp_parse_automation_tags (priority 10)
 */
function automatorwp_forminator_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {
    
    $new_replacements = array();
    
    // Get automation triggers
    $triggers = automatorwp_get_automation_triggers( $automation_id );
    
    foreach( $triggers as $trigger ) {
        
        $trigger_args = automatorwp_get_trigger( $trigger->type );
        
        // Skip if trigger is not from Forminator integration
        if( $trigger_args['integration'] !== 'forminator' ) {
            continue;
        }
        
        // Get the last trigger log
        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );
        
        if( ! $log ) {
            continue;
        }
        
        ct_setup_table( 'automatorwp_logs' );
        $form_id = ct_get_object_meta( $log->id, 'form_id', true );
        $form_fields = ct_get_object_meta( $log->id, 'form_fields', true );
        ct_reset_setup_table();
        
        // Skip if not form fields data
        if( ! is_array( $form_fields ) ) {
            continue;
        }
        
        // Look for trigger-specific field displayed tags
        // Pattern: {t:TRIGGER_ID:forminator_field_displayed:FIELD_ID}
        preg_match_all( "/\{t:" . $trigger->id . ":forminator_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {
            
            foreach( $matches[1] as $field_id ) {
                
                if( isset( $form_fields[$field_id] ) ) {
                    $field_value = $form_fields[$field_id];
                    $displayed_value = automatorwp_forminator_get_field_displayed_value( 
                        $form_id, 
                        $field_id, 
                        $field_value 
                    );
                    
                    $new_replacements['{t:' . $trigger->id . ':forminator_field_displayed:' . $field_id . '}'] = $displayed_value;
                }
            }
        }
        
        // Look for alternative pattern without trigger prefix
        // Pattern: {ID:forminator_field_displayed:FIELD_ID}
        preg_match_all( "/\{" . $trigger->id . ":forminator_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {
            
            foreach( $matches[1] as $field_id ) {
                
                if( isset( $form_fields[$field_id] ) ) {
                    $field_value = $form_fields[$field_id];
                    $displayed_value = automatorwp_forminator_get_field_displayed_value( 
                        $form_id, 
                        $field_id, 
                        $field_value 
                    );
                    
                    $new_replacements['{' . $trigger->id . ':forminator_field_displayed:' . $field_id . '}'] = $displayed_value;
                }
            }
        }
    }
    
    if( count( $new_replacements ) ) {
        $tags = array_keys( $new_replacements );
        $parsed_content = str_replace( $tags, $new_replacements, $parsed_content );
    }
    
    return $parsed_content;
}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_forminator_parse_automation_tags', 10, 5 );

/**
 * Get the displayed value (label) of a Forminator field
 * 
 * For fields with options (checkbox, radio, dropdown), maps the internal value
 * to its displayed label. For other fields, returns the value as-is.
 * 
 * @param int       $form_id        The Forminator form ID
 * @param string    $field_id       The field ID
 * @param mixed     $field_value    The field value (internal value)
 * @return string|array             The displayed value/label
 */
function automatorwp_forminator_get_field_displayed_value( $form_id, $field_id, $field_value ) {
    
    // If value is empty, return as-is
    if( empty( $field_value ) ) {
        return $field_value;
    }
    
    // Get the form data
    $form = automatorwp_forminator_get_form( $form_id );
    
    if( ! $form ) {
        return $field_value;
    }
    
    // Get field configuration
    $field_config = automatorwp_forminator_get_field_config( $form, $field_id );
    
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
                $displayed_values[] = automatorwp_forminator_find_option_label( $value, $options );
            }
            
            return implode( ', ', array_filter( $displayed_values ) );
        } else {
            // Single value
            return automatorwp_forminator_find_option_label( $field_value, $options );
        }
    }
    
    // For non-choice fields, return value as-is
    return $field_value;
}

/**
 * Get a Forminator form data
 * 
 * @param int $form_id The form ID
 * @return array|false Form data or false if not found
 */
function automatorwp_forminator_get_form( $form_id ) {
    
    // Forminator stores forms as posts with post_type 'forminator_forms'
    $form_post = get_post( $form_id );
    
    if( ! $form_post || $form_post->post_type !== 'forminator_forms' ) {
        return false;
    }
    
    // Get form meta data
    $form_data = get_post_meta( $form_id, 'forminator_form_settings', true );
    
    if( ! $form_data ) {
        return false;
    }
    
    return $form_data;
}

/**
 * Get field configuration from Forminator form
 * 
 * @param array     $form_data  The form data
 * @param string    $field_id   The field ID to find
 * @return array|false          Field configuration or false if not found
 */
function automatorwp_forminator_get_field_config( $form_data, $field_id ) {
    
    if( ! isset( $form_data['fields'] ) || ! is_array( $form_data['fields'] ) ) {
        return false;
    }
    
    foreach( $form_data['fields'] as $field ) {
        
        // Forminator stores field ID in 'element_id' or 'id'
        $current_field_id = isset( $field['element_id'] ) ? $field['element_id'] : 
                            ( isset( $field['id'] ) ? $field['id'] : '' );
        
        if( $current_field_id === $field_id ) {
            return $field;
        }
    }
    
    return false;
}

/**
 * Find the label for a given value in Forminator field options
 * 
 * @param mixed     $value      The internal value to find
 * @param array     $options    The options list
 * @return string               The label if found, otherwise returns the value
 */
function automatorwp_forminator_find_option_label( $value, $options ) {
    
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
            $option_value = isset( $option['value'] ) ? $option['value'] : 
                            ( isset( $option['key'] ) ? $option['key'] : null );
            $option_label = isset( $option['label'] ) ? $option['label'] : 
                            ( isset( $option['text'] ) ? $option['text'] : $option_value );
        } elseif( is_object( $option ) ) {
            // Object format
            $option_value = isset( $option->value ) ? $option->value : 
                            ( isset( $option->key ) ? $option->key : null );
            $option_label = isset( $option->label ) ? $option->label : 
                            ( isset( $option->text ) ? $option->text : $option_value );
        }
        
        if( $option_value !== null && ( $option_value === $value || $option_value == $value ) ) {
            return $option_label ? $option_label : $value;
        }
    }
    
    // Value not found in options, return as-is
    return $value;
}

/**
 * Tag content parser (for AutomatorWP integration)
 * 
 * @param string $tag_name The tag name
 * @param mixed  $tag_content The tag content/parameter
 * @return string Parsed tag name
 */
function automatorwp_forminator_parse_tag_content( $tag_name, $tag_content ) {
    // Simple pass-through for this tag
    return $tag_content;
}

/**
 * Direct tag replacement (for global usage)
 * 
 * @param string $replacement The replacement text
 * @param string $tag_name The tag name
 * @param array  $replacements All replacements
 * @return string The replacement value
 */
function automatorwp_forminator_tag_replacement( $replacement, $tag_name, $replacements ) {
    // Tag replacement is handled via the parse_automation_tags hook
    return $replacement;
}
