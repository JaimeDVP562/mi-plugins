<?php
/**
 * AJAX Functions
 *
 * @package     AutomatorWP\Asana\AJAX_Functions
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get projects from Asana for a select field.
 *
 * @since 1.0.0
 * @param stdClass $field The CMB2 field object.
 * @return array
 */
function automatorwp_asana_options_cb_projects( $field ) {
    $projects = automatorwp_asana_get_projects();
    $options = array( '' => __( 'Select a project', 'automatorwp-asana' ) );

    if ( ! empty( $projects ) ) {
        foreach ( $projects as $id => $name ) {
            $options[ $id ] = $name;
        }
    }

    return $options;
}

/**
 * Get users from Asana for a select field.
 *
 * @since 1.0.0
 * @param stdClass $field The CMB2 field object.
 * @return array
 */
function automatorwp_asana_options_cb_users( $field ) {
    $users = automatorwp_asana_get_users();
    $options = array( '' => __( 'Select an assignee', 'automatorwp-asana' ) );

    if ( ! empty( $users ) ) {
        foreach ( $users as $id => $name ) {
            $options[ $id ] = $name;
        }
    }

    return $options;
}
