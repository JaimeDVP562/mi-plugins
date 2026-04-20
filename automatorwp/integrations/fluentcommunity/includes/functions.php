<?php
/**
 * Functions
 *
 * @package     AutomatorWP\FluentCommunity\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to spaces
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_fluentcommunity_options_cb_space( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any space', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $space_id ) {

            // Skip option none
            if( $space_id === $none_value ) {
                continue;
            }

            $options[$space_id] = automatorwp_fluentcommunity_get_space_title( $space_id );
        }
    }

    return $options;

}

/**
 * Get spaces
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_fluentcommunity_get_spaces() {

    $spaces = array();

    // Return Collection of Space objects
    $all_spaces = \FluentCommunity\App\Functions\Utility::getSpaces( );
    
    foreach ( $all_spaces as $space ){   

        $spaces[] = array(
            'id'    => $space['id'],
            'title'  => $space['title'],
        );         

    }

    return $spaces;

}

/**
 * Get the space title
 *
 * @since 1.0.0
 *
 * @param int $space_id
 *
 * @return string|null
 */
function automatorwp_fluentcommunity_get_space_title( $space_id ) {

    // Empty title if no ID provided
    if( absint( $space_id ) === 0 ) {
        return '';
    }

    $spaces = \FluentCommunity\App\Functions\Utility::getSpaces();

    foreach ( $spaces as $space ) {
        if ( absint( $space['id'] ) === absint( $space_id ) ) {
            return $space['title']; 
        }
    }

}

/**
 * Options callback for select2 fields assigned to courses
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_fluentcommunity_options_cb_course( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any course', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $course_id ) {

            // Skip option none
            if( $course_id === $none_value ) {
                continue;
            }

            $options[$course_id] = automatorwp_fluentcommunity_get_course_title( $course_id );
        }
    }

    return $options;

}

/**
 * Get courses
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_fluentcommunity_get_courses() {

    $courses = array();

    // Return Collection of Course objects
    $all_courses = \FluentCommunity\App\Functions\Utility::getCourses();
    
    foreach ( $all_courses as $course ){   

        $courses[] = array(
            'id'    => $course['id'],
            'title'  => $course['title'],
        );         

    }

    return $courses;

}

/**
 * Get the course title
 *
 * @since 1.0.0
 *
 * @param int $course_id
 *
 * @return string|null
 */
function automatorwp_fluentcommunity_get_course_title( $course_id ) {

    // Empty title if no ID provided
    if( absint( $course_id ) === 0 ) {
        return '';
    }

    $courses = \FluentCommunity\App\Functions\Utility::getCourses();

    foreach ( $courses as $course ) {
        if ( absint( $course['id'] ) === absint( $course_id ) ) {
            return $course['title']; 
        }
    }

}
