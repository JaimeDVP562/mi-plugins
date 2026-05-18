<?php
/**
 * BBForms Form Field Displayed Value Tag
 * 
 * Snippet para agregar a: TAREA BBFORMS/bbforms/includes/tags.php
 * 
 * Este snippet agrega soporte para obtener el valor mostrado (label) de campos
 * con opciones (checkbox, radio, select, multiselect) en lugar del valor interno.
 * 
 * Uso: {form.field_displayed:nombre_del_campo}
 */

// ============================================================================
// 1. REGISTRO DE LA TAG EN bbforms_get_tags()
// ============================================================================
// Agregar esta entrada al array de tags en la función bbforms_get_tags():

// En la sección 'form' > 'tags', agregar:
/*
$tags['form']['tags']['form.field_displayed:FIELD_NAME'] = array(
    'label'     => __( 'Field displayed value', 'bbforms' ),
    'type'      => 'text',
    'preview'   => __( 'Field displayed value (label), replace "FIELD_NAME" by the field name. For choice fields (radio, checkbox, select), returns the label instead of the value.', 'bbforms' ),
);
*/

// ============================================================================
// 2. PROCESAMIENTO EN bbforms_get_tag_replacement()
// ============================================================================
// Agregar este código en la función bbforms_get_tag_replacement(), 
// en la sección de "form tags", después del switch principal:

/*
// form.field_displayed:FIELD_NAME tag - Get the displayed label value
if( bbforms_starts_with( $tag_name, 'form.field_displayed:' ) ) {
    
    $field_name = explode(':', $tag_name)[1];
    
    if( isset( $form_fields[$field_name] ) ) {
        $field_value = $form_fields[$field_name];
        $replacement = bbforms_get_field_display_value( $form, $field_name, $field_value );
    }
}
*/

// ============================================================================
// 3. FUNCIÓN AUXILIAR - Agregar a bbforms/includes/functions.php
// ============================================================================

/**
 * Get the displayed value (label) of a form field
 * 
 * For fields with options (radio, checkbox, select), maps the internal value
 * to its displayed label. For other fields, returns the value as-is.
 * 
 * @since 1.0.0
 * @param stdClass  $form           The form object
 * @param string    $field_name     The field name/key
 * @param mixed     $field_value    The field value (internal value)
 * @return string|array             The displayed value/label
 */
function bbforms_get_field_display_value( $form, $field_name, $field_value ) {
    
    // If value is empty, return as-is
    if( empty( $field_value ) ) {
        return $field_value;
    }
    
    // Try to get the field from form configuration
    // Adjust this based on how BBForms stores field configuration
    if( ! isset( $form->fields_config ) ) {
        return $field_value;
    }
    
    $field_config = null;
    
    // Find field configuration by name
    foreach( $form->fields_config as $field ) {
        if( isset( $field->name ) && $field->name === $field_name ) {
            $field_config = $field;
            break;
        }
    }
    
    // If no field config found, return value as-is
    if( ! $field_config ) {
        return $field_value;
    }
    
    $field_type = isset( $field_config->type ) ? $field_config->type : '';
    
    // Handle choice-type fields (radio, checkbox, select, multiselect)
    $choice_types = array( 'radio', 'checkbox', 'select', 'multiselect' );
    
    if( in_array( $field_type, $choice_types ) ) {
        
        $options = isset( $field_config->options ) ? $field_config->options : array();
        
        // Handle array values (multiselect, multiple checkboxes)
        if( is_array( $field_value ) ) {
            $displayed_values = array();
            
            foreach( $field_value as $value ) {
                $displayed_values[] = bbforms_find_option_label( $value, $options );
            }
            
            return implode( ', ', $displayed_values );
        } else {
            // Single value
            return bbforms_find_option_label( $field_value, $options );
        }
    }
    
    // For non-choice fields, return value as-is
    return $field_value;
}

/**
 * Find the label for a given value in field options
 * 
 * @since 1.0.0
 * @param mixed     $value      The internal value to find
 * @param array     $options    The options list
 * @return string               The label if found, otherwise returns the value
 */
function bbforms_find_option_label( $value, $options ) {
    
    if( ! is_array( $options ) ) {
        return $value;
    }
    
    foreach( $options as $option ) {
        
        // Options can be stored as objects or arrays
        $option_value = null;
        $option_label = null;
        
        if( is_object( $option ) ) {
            $option_value = isset( $option->value ) ? $option->value : null;
            $option_label = isset( $option->label ) ? $option->label : null;
        } elseif( is_array( $option ) ) {
            $option_value = isset( $option['value'] ) ? $option['value'] : null;
            $option_label = isset( $option['label'] ) ? $option['label'] : null;
        }
        
        if( $option_value === $value || $option_value == $value ) {
            return $option_label ? $option_label : $value;
        }
    }
    
    // Value not found in options, return as-is
    return $value;
}

/**
 * Helper function to check if string starts with prefix
 * (if bbforms_starts_with doesn't exist)
 * 
 * @since 1.0.0
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
if( ! function_exists( 'bbforms_starts_with' ) ) {
    function bbforms_starts_with( $haystack, $needle ) {
        return ( strpos( $haystack, $needle ) === 0 );
    }
}
