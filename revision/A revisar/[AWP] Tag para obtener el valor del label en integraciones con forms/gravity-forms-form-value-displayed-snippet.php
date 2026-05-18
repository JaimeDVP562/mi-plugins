<?php
/**
 * AutomatorWP - Gravity Forms Field Displayed Value Tag
 * 
 * Integración completa para obtener valores mostrados (labels) de campos Gravity Forms.
 * Puede ser parte de una nueva integración: automatorwp-gravity-forms/includes/tags.php
 * 
 * Uso: {gf_field_displayed:FIELD_ID} o {ID:gf_field_displayed:FIELD_ID}
 */

if( !defined( 'ABSPATH' ) ) exit;

// ============================================================================
// INTEGRACIÓN COMO AUTOMATORWP ADDON
// ============================================================================

/**
 * Register Gravity Forms field displayed value tags
 * 
 * Hook: automatorwp_init (priority 20)
 */
function automatorwp_gravity_forms_register_tags() {
    
    if( ! function_exists( 'automatorwp_register_tag' ) ) {
        return;
    }
    
    // Register the global tag for getting any Gravity Forms field displayed value
    automatorwp_register_tag( 'gf_field_displayed', array(
        'integration'   => 'gravity_forms',
        'label'         => __( 'Gravity Forms Field Displayed Value', 'automatorwp-gravity-forms' ),
        'schema'        => 'gf_field_displayed:{field_id}',
        'preview'       => __( 'Displayed value (label) of a Gravity Forms field. For choice fields (checkbox, radio, dropdown, multi-select), returns the label instead of the internal value.', 'automatorwp-gravity-forms' ),
        'function'      => 'automatorwp_gravity_forms_tag_replacement',
        'parse_content' => 'automatorwp_gravity_forms_parse_tag_content',
    ) );
}
add_action( 'automatorwp_init', 'automatorwp_gravity_forms_register_tags', 20 );

/**
 * Parse trigger-specific Gravity Forms field displayed value tags
 * 
 * Hook: automatorwp_parse_automation_tags (priority 10)
 */
function automatorwp_gravity_forms_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {
    
    $new_replacements = array();
    
    // Get automation triggers
    $triggers = automatorwp_get_automation_triggers( $automation_id );
    
    foreach( $triggers as $trigger ) {
        
        $trigger_args = automatorwp_get_trigger( $trigger->type );
        
        // Skip if trigger is not from Gravity Forms integration
        if( $trigger_args['integration'] !== 'gravity_forms' ) {
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
        $entry_id = ct_get_object_meta( $log->id, 'entry_id', true );
        ct_reset_setup_table();
        
        // Skip if not form fields data
        if( ! is_array( $form_fields ) ) {
            continue;
        }
        
        // Look for trigger-specific field displayed tags
        // Pattern: {t:TRIGGER_ID:gf_field_displayed:FIELD_ID}
        preg_match_all( "/\{t:" . $trigger->id . ":gf_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {
            
            foreach( $matches[1] as $field_id ) {
                
                if( isset( $form_fields[$field_id] ) ) {
                    $field_value = $form_fields[$field_id];
                    $displayed_value = automatorwp_gravity_forms_get_field_displayed_value( 
                        $form_id, 
                        $field_id, 
                        $field_value,
                        $entry_id
                    );
                    
                    $new_replacements['{t:' . $trigger->id . ':gf_field_displayed:' . $field_id . '}'] = $displayed_value;
                }
            }
        }
        
        // Look for alternative pattern without trigger prefix
        // Pattern: {ID:gf_field_displayed:FIELD_ID}
        preg_match_all( "/\{" . $trigger->id . ":gf_field_displayed:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {
            
            foreach( $matches[1] as $field_id ) {
                
                if( isset( $form_fields[$field_id] ) ) {
                    $field_value = $form_fields[$field_id];
                    $displayed_value = automatorwp_gravity_forms_get_field_displayed_value( 
                        $form_id, 
                        $field_id, 
                        $field_value,
                        $entry_id
                    );
                    
                    $new_replacements['{' . $trigger->id . ':gf_field_displayed:' . $field_id . '}'] = $displayed_value;
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
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_gravity_forms_parse_automation_tags', 10, 5 );

/**
 * Get the displayed value (label) of a Gravity Forms field
 * 
 * For fields with options (checkbox, radio, dropdown, multi-select), maps the internal value
 * to its displayed label. For other fields, returns the value as-is.
 * 
 * @param int       $form_id        The Gravity Forms form ID
 * @param int       $field_id       The field ID
 * @param mixed     $field_value    The field value (internal value)
 * @param int       $entry_id       Optional entry ID for more context
 * @return string|array             The displayed value/label
 */
function automatorwp_gravity_forms_get_field_displayed_value( $form_id, $field_id, $field_value, $entry_id = 0 ) {
    
    // If value is empty, return as-is
    if( empty( $field_value ) ) {
        return $field_value;
    }
    
    // Check if Gravity Forms is active
    if( ! class_exists( 'GFFormsModel' ) ) {
        return $field_value;
    }
    
    // Get the form object
    $form = GFFormsModel::get_form_meta( $form_id );
    
    if( ! $form || ! is_array( $form ) ) {
        return $field_value;
    }
    
    // Get field configuration
    $field = GFFormsModel::get_field( $form, $field_id );
    
    if( ! $field ) {
        return $field_value;
    }
    
    $field_type = RGFormsModel::get_field_type( $field );
    
    // Handle choice-type fields
    $choice_types = array( 'radio', 'checkbox', 'select', 'multiselect', 'list' );
    
    if( in_array( $field_type, $choice_types ) ) {
        
        $choices = isset( $field->choices ) ? $field->choices : array();
        
        // Handle array values (multiselect, multiple checkboxes)
        if( is_array( $field_value ) ) {
            $displayed_values = array();
            
            foreach( $field_value as $value ) {
                $displayed_values[] = automatorwp_gravity_forms_find_choice_label( $value, $choices );
            }
            
            return implode( ', ', array_filter( $displayed_values ) );
        } else {
            // Single value
            return automatorwp_gravity_forms_find_choice_label( $field_value, $choices );
        }
    }
    
    // Handle product fields (product choices with price)
    if( in_array( $field_type, array( 'product', 'option' ) ) ) {
        
        $choices = isset( $field->choices ) ? $field->choices : array();
        
        if( is_array( $field_value ) ) {
            $displayed_values = array();
            
            foreach( $field_value as $value ) {
                $displayed_values[] = automatorwp_gravity_forms_find_choice_label( $value, $choices );
            }
            
            return implode( ', ', array_filter( $displayed_values ) );
        } else {
            return automatorwp_gravity_forms_find_choice_label( $field_value, $choices );
        }
    }
    
    // For non-choice fields, return value as-is
    return $field_value;
}

/**
 * Find the label for a given value in Gravity Forms field choices
 * 
 * @param mixed     $value      The internal value to find
 * @param array     $choices    The choices list
 * @return string               The label if found, otherwise returns the value
 */
function automatorwp_gravity_forms_find_choice_label( $value, $choices ) {
    
    if( ! is_array( $choices ) ) {
        return $value;
    }
    
    foreach( $choices as $choice ) {
        
        // Parse choice based on format
        $choice_value = null;
        $choice_label = null;
        
        if( is_array( $choice ) ) {
            // Array format (typical in GF)
            $choice_value = isset( $choice['value'] ) ? $choice['value'] : null;
            $choice_label = isset( $choice['text'] ) ? $choice['text'] : null;
        } elseif( is_object( $choice ) ) {
            // Object format
            $choice_value = isset( $choice->value ) ? $choice->value : null;
            $choice_label = isset( $choice->text ) ? $choice->text : null;
        }
        
        if( $choice_value !== null && ( $choice_value === $value || $choice_value == $value ) ) {
            return $choice_label ? $choice_label : $value;
        }
    }
    
    // Value not found in choices, check for array value format (multiple items)
    // Some GF fields store as "value:label" pair
    if( strpos( $value, ':' ) !== false ) {
        $parts = explode( ':', $value, 2 );
        return isset( $parts[1] ) ? $parts[1] : $value;
    }
    
    // Value not found, return as-is
    return $value;
}

/**
 * Tag content parser (for AutomatorWP integration)
 * 
 * @param string $tag_name The tag name
 * @param mixed  $tag_content The tag content/parameter
 * @return string Parsed tag name
 */
function automatorwp_gravity_forms_parse_tag_content( $tag_name, $tag_content ) {
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
function automatorwp_gravity_forms_tag_replacement( $replacement, $tag_name, $replacements ) {
    // Tag replacement is handled via the parse_automation_tags hook
    return $replacement;
}
