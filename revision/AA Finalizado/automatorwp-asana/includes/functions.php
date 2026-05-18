<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Asana\Functions
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_asana_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_asana_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Helper function to get the Asana API URL.
 *
 * @since 1.0.0
 * @return string
 */
function automatorwp_asana_get_api_url() {
    return 'https://app.asana.com/api/1.0';
}

/**
 * Helper function to get the Asana API parameters.
 *
 * @since 1.0.0
 * @return array|false
 */
function automatorwp_asana_get_api() {
    $token = automatorwp_asana_get_option( 'access_token', '' );

    if ( empty( $token ) ) {
        return false;
    }

    return array(
        'url'   => automatorwp_asana_get_api_url(),
        'token' => $token,
    );
}

/**
 * Get projects from Asana.
 *
 * @since 1.0.0
 * @return array
 */
function automatorwp_asana_get_projects() {
    $api = automatorwp_asana_get_api();
    if ( ! $api ) {
        return array();
    }

    $response = wp_remote_get( $api['url'] . '/projects', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['token'],
            'Accept'        => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return array();
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! isset( $body['data'] ) ) {
        return array();
    }

    $projects = array();
    foreach ( $body['data'] as $project ) {
        $projects[ $project['gid'] ] = $project['name'];
    }

    return $projects;
}

/**
 * Get users from Asana.
 *
 * @since 1.0.0
 * @return array
 */
function automatorwp_asana_get_users() {
    $api = automatorwp_asana_get_api();
    if ( ! $api ) {
        return array();
    }

    // Getting users from the workspace. We might need a workspace ID, 
    // but Asana also allows 'me' for current user or /users for all users in accessible workspaces.
    $response = wp_remote_get( $api['url'] . '/users', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['token'],
            'Accept'        => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return array();
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! isset( $body['data'] ) ) {
        return array();
    }

    $users = array();
    foreach ( $body['data'] as $user ) {
        $users[ $user['gid'] ] = $user['name'];
    }

    return $users;
}

/**
 * Create a task in Asana.
 *
 * @since 1.0.0
 * 
 * @param array $args Task arguments.
 * @return bool|WP_Error
 */
function automatorwp_asana_create_task( $args ) {
    $api = automatorwp_asana_get_api();
    if ( ! $api ) {
        return new WP_Error( 'missing_api', __( 'Missing Asana API credentials.', 'automatorwp-asana' ) );
    }

    $default_args = array(
        'name'     => '',
        'notes'    => '',
        'projects' => array(),
    );

    $args = wp_parse_args( $args, $default_args );

    $body = array(
        'data' => $args,
    );

    $response = wp_remote_post( $api['url'] . '/tasks', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['token'],
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode( $body ),
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code < 200 || $code >= 300 ) {
        return new WP_Error( 'asana_api_error', sprintf( __( 'Asana API error: %s', 'automatorwp-asana' ), wp_remote_retrieve_body( $response ) ) );
    }

    return true;
}
