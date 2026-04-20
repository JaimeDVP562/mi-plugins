<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\MyCred\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get point types from MyCred
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_mycred_get_point_types( ) {

    $options = array();

    $point_types = mycred_get_types();
    
    foreach ( $point_types as $point_type => $label ){

        $options[] = array(
            'id'    => $point_type,
            'name'  => $label,
        );
        
    }

    return $options;

}

/**
 * Get point type from MyCred
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_mycred_options_cb_point_type( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any point type', 'automatorwp-pro' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $point_type_id ) {

            // Skip option none
            if( $point_type_id === $none_value ) {
                continue;
            }

            $options[$point_type_id] = automatorwp_mycred_get_point_type_name( $point_type_id );
        }
    }

    return $options;

}

/**
* Get the point type name
*
* @since 1.0.0
* 
* @param string $point_type_id
*
* @return array
*/
function automatorwp_mycred_get_point_type_name( $point_type_id ) {

    $point_type = mycred_get_point_type( $point_type_id );
    
    return $point_type->singular;
    
}