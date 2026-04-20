<?php
/**
 * AJAX Functions for Google Groups
 *
 * @package     AutomatorWP\GoogleGroups\Ajax_Functions
 * @since       1.0.0
 */

// Exit if accessed directly 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler to retrieve Google Groups for Select2 fields
 */
function automatorwp_googlegroups_ajax_get_groups() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Forbidden', 'automatorwp-googlegroups' ) ) );
    }

    $groups   = automatorwp_googlegroups_get_groups();
    $results = array();

    if ( ! empty( $groups ) ) {
        foreach ( $groups as $email => $name ) {
            $results[] = array(
                'id'   => $email,
                'text' => sprintf( '%s <%s>', $name, $email ),
            );
        }
    }

    $results = automatorwp_ajax_parse_extra_options( $results );
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_automatorwp_googlegroups_get_groups', 'automatorwp_googlegroups_ajax_get_groups' );


/**
 * AJAX handler to retrieve members for a specific Google Group (stub)
 */
function automatorwp_googlegroups_ajax_get_members() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    $group = isset( $_REQUEST['group'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['group'] ) ) : '';

    $members = array();
    // In a real implementation, call automatorwp_googlegroups_get_members( $group )

    $results = array();
    if ( ! empty( $members ) ) {
        foreach ( $members as $email => $name ) {
            $results[] = array(
                'id'   => $email,
                'text' => sprintf( '%s <%s>', $name, $email ),
            );
        }
    }

    $results = automatorwp_ajax_parse_extra_options( $results );
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_automatorwp_googlegroups_get_members', 'automatorwp_googlegroups_ajax_get_members' );
