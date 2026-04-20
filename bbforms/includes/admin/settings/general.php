<?php
/**
 * Admin General Settings
 *
 * @package     BBForms\Admin\Settings\General
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
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
function bbforms_settings_general_meta_boxes( $meta_boxes ) {

    $meta_boxes['general_settings'] = array(
        'title' => bbforms_dashicon( 'admin-generic' ) . __( 'General Settings', 'bbforms' ),
        'fields' => apply_filters( 'bbforms_general_settings_fields', array(
            'minimum_role' => array(
                'name'      => __( 'Minimum Access Role', 'bbforms' ),
                'type'      => 'select',
                'options'   => bbforms_get_allowed_manager_capabilities(),
                'tooltip'   => __( 'Minimum role a user needs to access to BBForms management areas.', 'bbforms' ),
                'label_cb'  => 'cmb_tooltip_label_cb',
            ),
            'editor_theme' => array(
                'name'      => __( 'Editor Theme', 'bbforms' ),
                'tooltip'   => __( 'Set the BBForms editor theme. By default is set to dark to reduce eye strain.', 'bbforms' ),
                'label_cb'  => 'cmb_tooltip_label_cb',
                'type'      => 'select',
                'options'   => array(
                    'dark'  => __( 'Dark', 'bbforms' ),
                    'light' => __( 'Light', 'bbforms' ),
                ),
            ),
            'disable_admin_bar_menu' => array(
                'name'      => __( 'Disable Top Bar', 'bbforms' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => __( 'Check this option to disable the BBForms top bar menu.', 'bbforms' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'bbforms_settings_general_meta_boxes', 'bbforms_settings_general_meta_boxes' );

/**
 * Get capability required for administration.
 *
 * @since  1.0.0
 *
 * @return string User capability.
 */
function bbforms_get_manager_capability() {

    $minimum_role = bbforms_get_option( 'minimum_role', 'manage_options' );
    $allowed_capabilities = array_keys( bbforms_get_allowed_manager_capabilities() );

    // Do not allow to bypass subscribers capability in any way
    $excluded_capabilities = array( 'read' );

    // Check if capability is allowed
    if ( ! in_array( $minimum_role, $allowed_capabilities ) || in_array( $minimum_role, $excluded_capabilities ) ) {
        // If not allowed, manually update the settings
        $update_capability = get_option( 'bbforms_settings' );
        $update_capability['minimum_role'] = 'manage_options';
        update_option( 'bbforms_settings',  $update_capability );

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
function bbforms_get_allowed_manager_capabilities() {

    if( did_action( 'init' ) ) {
        $allowed_capabilities = array(
            'manage_options' => __( 'Administrator', 'bbforms' ),
            'delete_others_posts' => __( 'Editor', 'bbforms' ),
            'publish_posts' => __( 'Author', 'bbforms' ),
        );
    } else {
        $allowed_capabilities = array(
            'manage_options' => 'manage_options',
            'delete_others_posts' => 'delete_others_posts',
            'publish_posts' => 'publish_posts',
        );
    }

    return apply_filters( 'bbforms_allowed_manager_capabilities', $allowed_capabilities );
}