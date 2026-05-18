<?php
/**
 * Functions
 *
 * @package     AutomatorWP\EasyCart\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;



/**
 * Get the product title
 *
 * @since 1.0.0
 *
 * @param int $product_id
 *
 * @return string|null
 */
function automatorwp_easycart_get_product_title( $product_id ) {
    global $wpdb;

    if( absint( $product_id ) === 0 ) {
        return '';
    }

    $table_name = 'ec_product';
    $column_id = 'product_id';
    $column_title = 'title'; 

    $product_title = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT {$column_title} FROM {$table_name} WHERE {$column_id} = %d",
            $product_id
        )
    );


    return ( $product_title ? $product_title : '' );
}

/**
 * Options callback for select2 fields assigned to EasyCart products
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_easycart_options_cb_product( $field ) {


    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any product', 'automatorwp-easycart' ); 
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $product_id ) { 

            if( $product_id === $none_value ) {
                continue;
            }

            $options[$product_id] = automatorwp_easycart_get_product_title( $product_id );
        }
    }

    return $options;

}


/**
 * Get EasyCart item (product) fields values (esto sería para si tienes campos personalizados de producto o atributos)
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function automatorwp_easycart_get_item_fields_values( $fields ) {

    $item_fields = array(); 
    foreach ( $fields as $field_name => $field_value ) {

        if( is_array( $field_value ) ) {

            foreach ( $field_value as $subfield_name => $subfield_value ) {
                if( is_string( $subfield_name ) ) {
                    $item_fields[$subfield_name] = $subfield_value;
                }
            }

        } else {
            $item_fields[$field_name] = $field_value;
        }
    }

    if( function_exists( 'automatorwp_utilities_pull_array_values' ) ) {
        $item_fields = automatorwp_utilities_pull_array_values( $item_fields );
    }

    return $item_fields;

}

/**
 * Custom tags replacements for EasyCart products
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
function automatorwp_easycart_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) { 

    $new_replacements = array();

    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        if( $trigger_args['integration'] !== 'easycart' ) {
            continue;
        }

        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $item_fields = ct_get_object_meta( $log->id, 'item_fields', true );
        ct_reset_setup_table();

        if( ! is_array( $item_fields ) ) {
            continue;
        }

        preg_match_all( "/\{t:" . $trigger->id . ":product_field:\s*(.?)\s\}/", $parsed_content, $matches ); 

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                if( isset( $item_fields[$field_name] ) ) {
                    $new_replacements['{t:' . $trigger->id . ':product_field:' . $field_name . '}'] = $item_fields[$field_name];
                }
            }

        }

        preg_match_all( "/\{" . $trigger->id . ":product_field:\s*(.?)\s\}/", $parsed_content, $matches ); 

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                if( isset( $item_fields[$field_name] ) ) {
                    $new_replacements['{' . $trigger->id . ':product_field:' . $field_name . '}'] = $item_fields[$field_name];
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
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_easycart_parse_automation_tags', 10, 5 );