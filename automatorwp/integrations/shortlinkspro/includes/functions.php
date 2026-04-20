<?php
/**
 * Functions
 *
 * @package     AutomatorWP\ShortLinksPro\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to links
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_shortlinkspro_options_cb_link( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any link', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $link_id ) {

            // Skip option none
            if( $link_id === $none_value ) {
                continue;
            }

            $options[$link_id] = shortlinkspro_get_link_title( $link_id );
        }
    }
    
    return $options;
}

/**
 * Options callback for select2 fields assigned to categories
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_shortlinkspro_options_cb_category( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any category', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $category_id ) {

            // Skip option none
            if( $category_id === $none_value ) {
                continue;
            }

            $options[$category_id] = shortlinkspro_get_link_category_name( $category_id );
        }
    }
    
    return $options;
}

/**
 * Options callback for select2 fields assigned to tags
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_shortlinkspro_options_cb_tag( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $tag_id ) {

            // Skip option none
            if( $tag_id === $none_value ) {
                continue;
            }

            $options[$tag_id] = shortlinkspro_get_link_tag_name( $tag_id );
        }
    }
    
    return $options;
}

/**
 * Get categories related to link
 *
 * @since 1.0.0
 *
 * @param int   $link_id
 *
 * @return object
 */
function automatorwp_shortlinkspro_get_categories_from_link( $link_id ) {

    global $wpdb;

    // Get categories related to link
    ct_setup_table( 'shortlinkspro_link_categories_relationships' );
    $categories = ct_get_object_terms( $link_id );
    ct_reset_setup_table();

    return $categories;
    
}

/**
 * Get tags related to link
 *
 * @since 1.0.0
 *
 * @param int   $link_id
 *
 * @return object
 */
function automatorwp_shortlinkspro_get_tags_from_link( $link_id ) {

    global $wpdb;

    // Get tags related to link
    ct_setup_table( 'shortlinkspro_link_tags_relationships' );
    $tags = ct_get_object_terms( $link_id );
    ct_reset_setup_table();

    return $tags;
    
}