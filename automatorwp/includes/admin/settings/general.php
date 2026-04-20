<?php
/**
 * Admin General Settings
 *
 * @package     AutomatorWP\Admin\Settings\General
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.3.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * General Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_settings_general_meta_boxes( $meta_boxes ) {

    $meta_boxes['general-settings'] = array(
        'title' => automatorwp_dashicon( 'admin-generic' ) . __( 'General Settings', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_general_settings_fields', array(
            'minimum_role' => array(
                'name'      => __( 'Minimum role to administer AutomatorWP', 'automatorwp' ),
                'desc'      => __( 'Minimum role a user needs to access to AutomatorWP management areas.', 'automatorwp' ),
                'type'      => 'select',
                'options' => automatorwp_get_allowed_manager_capabilities(),
            ),
            'auto_logs_cleanup_days' => array(
                'name'      => __( 'Automatic logs cleanup:', 'automatorwp' ),
                'desc'      => __( 'Enter the number of days you want to keep the logs. Leave empty to disable the automatic logs cleanup.', 'automatorwp' )
                . '<br>' . __( 'Automatic logs cleanup will remove unused logs older than the number of days entered keeping only the important logs entries.', 'automatorwp' ),
                'type'      => 'text',
            ),
            'disable_admin_bar_menu' => array(
                'name'      => __( 'Disable top bar menu:', 'automatorwp' ),
                'desc'      => __( 'Check this option to disable the AutomatorWP top bar menu.', 'automatorwp' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_general_meta_boxes', 'automatorwp_settings_general_meta_boxes' );

/**
 * Get capability required for AutomatorWP administration.
 *
 * @since  1.0.0
 *
 * @return string User capability.
 */
function automatorwp_get_manager_capability() {

    $minimum_role = automatorwp_get_option( 'minimum_role', 'manage_options' );    
    $allowed_capabilities = array_keys( automatorwp_get_allowed_manager_capabilities() );
    
    // Do not allow to bypass subscribers capability in any way
    $excluded_capabilities = array( 'read' );

    // Check if capability is allowed
    if ( ! in_array( $minimum_role, $allowed_capabilities ) || in_array( $minimum_role, $excluded_capabilities ) ) {
        // If not allowed, manually update the settings
        $update_capability = get_option( 'automatorwp_settings' );
        $update_capability['minimum_role'] = 'manage_options';
        update_option( 'automatorwp_settings',  $update_capability );

        // Set minimum role to manage_options
        $minimum_role = 'manage_options';
        
    }
    
    return $minimum_role;

}

/**
 * Allowed capabilities
 *
 * @since 6.0.0
 *
 * @return array
 */
function automatorwp_get_allowed_manager_capabilities() {

    if ( did_action( 'init' ) ) {
        $allowed_capabilities = array(
            'manage_options' => __( 'Administrator', 'automatorwp' ),
            'delete_others_posts' => __( 'Editor', 'automatorwp' ),
            'publish_posts' => __( 'Author', 'automatorwp' ), 
        );
    } else {
        $allowed_capabilities = array(
            'manage_options' => 'manage_options',
            'delete_others_posts' => 'delete_others_posts',
            'publish_posts' => 'publish_posts', 
        );
    }
    
    return apply_filters( 'automatorwp_allowed_manager_capabilities', $allowed_capabilities );
}