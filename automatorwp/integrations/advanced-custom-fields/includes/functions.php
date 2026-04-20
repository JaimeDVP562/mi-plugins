<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Advance_Custom_Fields\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get acf fields related to posts
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_advanced_custom_fields_options_cb_fields_posts( ) {

    $options = array(
        'any' => __( 'any field', 'automatorwp-advanced-custom-fields' ),
    );
    
    // Get all post types
    $all_post_types = automatorwp_advanced_custom_fields_get_post_types_field_groups( 'post' );
    
    foreach( $all_post_types as $group ) {
    
        // Get fields from group
        $all_acf_fields = acf_get_fields( $group['ID'] );

        foreach ( $all_acf_fields as $acf_fields ){

            $options[$acf_fields['name']] = $acf_fields['label'];
        }

    }
    
    return $options;

}

/**
* Get all post field groups 
*
* @return array 
*/
function automatorwp_advanced_custom_fields_get_post_types_field_groups( $post_type ) {

	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		return array();
	}

	$groups_post = array();

    switch ( $post_type ){
        case 'post':
            $groups_types = array(
                'post_type',
                'post_template',
                'post_status',
                'post_format',
                'post_category',
                'post_taxonomy',
                'post',
            );
            break;
    }

	$groups = acf_get_field_groups();

	foreach ( $groups as $group ) {

		if ( ! empty( $group['location'] ) ) {

			foreach ( $group['location'] as $locations ) {

				foreach ( $locations as $location ) {

                        if ( ! in_array( $location['param'], $groups_types )) {
                            continue;
    
                        } else {
                            $groups_post[] = $group;
                        }

				}

			}

		}
        
	}

	return $groups_post;

}