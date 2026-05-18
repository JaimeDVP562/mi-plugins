<?php
/**
 * Admin General Settings
 *
 * @package     ShortLinksPro\Admin\Settings\General
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
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
function shortlinkspro_settings_general_meta_boxes( $meta_boxes ) {

    $meta_boxes['general_settings'] = array(
        'title' => shortlinkspro_dashicon( 'admin-generic' ) . __( 'General Settings', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_general_settings_fields', array(
            'minimum_role' => array(
                'name'      => __( 'Minimum Access Role', 'shortlinkspro' ),
                'type'      => 'select',
                'options'   => shortlinkspro_get_allowed_manager_capabilities(),
                'tooltip'   => __( 'Minimum role a user needs to access to ShortLinks Pro management areas.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'disable_admin_bar_menu' => array(
                'name'      => __( 'Disable Top Bar', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => __( 'Check this option to disable the ShortLinks Pro top bar menu.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_settings_general_meta_boxes', 'shortlinkspro_settings_general_meta_boxes' );

/**
 * Get capability required for administration.
 *
 * @since  1.0.0
 *
 * @return string User capability.
 */
function shortlinkspro_get_manager_capability() {

    $minimum_role = shortlinkspro_get_option( 'minimum_role', 'manage_options' );
    $allowed_capabilities = array_keys( shortlinkspro_get_allowed_manager_capabilities() );

    // Do not allow to bypass subscribers capability in any way
    $excluded_capabilities = array( 'read' );

    // Check if capability is allowed
    if ( ! in_array( $minimum_role, $allowed_capabilities ) || in_array( $minimum_role, $excluded_capabilities ) ) {
        // If not allowed, manually update the settings
        $update_capability = get_option( 'shortlinkspro_settings' );
        $update_capability['minimum_role'] = 'manage_options';
        update_option( 'shortlinkspro_settings',  $update_capability );

        // Set minimum role to manage_options
        $minimum_role = 'manage_options';

    }

    return $minimum_role;

}

/**
 * Allowed capabilities
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_get_allowed_manager_capabilities() {

    if( did_action( 'init' ) ) {
        $allowed_capabilities = array(
            'manage_options' => __( 'Administrator', 'shortlinkspro' ),
            'delete_others_posts' => __( 'Editor', 'shortlinkspro' ),
            'publish_posts' => __( 'Author', 'shortlinkspro' ),
        );
    } else {
        $allowed_capabilities = array(
            'manage_options' => 'manage_options',
            'delete_others_posts' => 'delete_others_posts',
            'publish_posts' => 'publish_posts',
        );
    }

    return apply_filters( 'shortlinkspro_allowed_manager_capabilities', $allowed_capabilities );
}