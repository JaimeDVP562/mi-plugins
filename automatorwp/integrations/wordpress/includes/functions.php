<?php
/**
 * Functions
 *
 * @package     AutomatorWP\WordPress\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
* Get taxonomies
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_wordpress_get_taxonomies() {

    $taxonomies = array();

    $all_taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
    
    foreach ( $all_taxonomies as $taxonomy ) {
    
        $taxonomies[] = array(
            'id' => $taxonomy->name,
            'name' => $taxonomy->labels->name,
        );

    }

    return $taxonomies;
}

/**
 * Get taxonomy
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_wordpress_options_cb_taxonomy( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any taxonomy', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $taxonomy_id ) {

            // Skip option none
            if( $taxonomy_id === $none_value ) {
                continue;
            }

            $options[$taxonomy_id] = automatorwp_wordpress_get_taxonomy_name( $taxonomy_id );
        }
    }

    return $options;

}

/**
* Get the taxonomy name
*
* @since 1.0.0
* 
* @param string $taxonomy_id
*
* @return array
*/
function automatorwp_wordpress_get_taxonomy_name( $taxonomy_id ) {

    // Empty title if no ID provided
    if( empty( $taxonomy_id ) ) {
        return '';
    }

    $taxonomy = get_taxonomy( $taxonomy_id );
    $taxonomy_name = $taxonomy->labels->name;

    return $taxonomy_name;

}

/**
 * Get terms
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_wordpress_options_cb_term( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any term', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    $taxonomy_id = ct_get_object_meta( $field->object_id, 'taxonomy', true );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $term_id ) {

            // Skip option none
            if( $term_id === $none_value ) {
                continue;
            }

            $options[$term_id] = automatorwp_wordpress_get_term_name( $taxonomy_id, $term_id );
        }
    }

    return $options;

}

/**
* Get the terms
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_wordpress_get_terms( $taxonomy_id ) {

    $terms = array();

    
    $terms_taxonomy = get_terms(array(
        'taxonomy' => $taxonomy_id,
        'hide_empty' => false,
    ));

    // Add terms to array
    foreach ($terms_taxonomy as $term) {
        $terms[] = array(
            'id' => $term->term_id,
            'name' => $term->name,
        );
    }

    return $terms;
}

/**
* Get the term name
*
* @since 1.0.0
* 
* @param string $term_id
*
* @return array
*/
function automatorwp_wordpress_get_term_name( $taxonomy_id, $term_id ) {

    // Empty title if no ID provided
    if( absint( $term_id ) === 0 ) {
        return '';
    }

    $term = get_term( $term_id );
    $term_name = $term->name;

    return $term_name;

}
