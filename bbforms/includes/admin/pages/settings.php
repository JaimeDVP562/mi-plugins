<?php
/**
 * Admin Settings Page
 *
 * @package     BBForms\Admin\Settings
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once BBFORMS_DIR . 'includes/admin/settings/general.php';
require_once BBFORMS_DIR . 'includes/admin/settings/submissions-cleanup.php';
require_once BBFORMS_DIR . 'includes/admin/settings/form-messages.php';
require_once BBFORMS_DIR . 'includes/admin/settings/error-messages.php';

/**
 * Register Settings using the WP Settings API.
 *
 * @since  1.0.0
 *
 * @return void
 */
function bbforms_register_settings() {

	register_setting( 'bbforms_settings', 'bbforms_settings', array(
        'type' => 'array',
        'sanitize_callback' => 'bbforms_settings_sanitization_cb',
    ) );

}
add_action( 'admin_init', 'bbforms_register_settings' );
add_action( 'rest_api_init', 'bbforms_register_settings' );

/**
 * Sanitization callback for the settings.
 *
 * @since  1.0.0
 *
 * @return void
 */
function bbforms_settings_sanitization_cb( $settings ) {

    global $bbforms_settings_sanitized;

    if( $bbforms_settings_sanitized === true ) {
        return $settings;
    }

    $bbforms_settings_sanitized = true;

    return map_deep( $settings, 'sanitize_text_field' );

}

/**
 * Register settings page.
 *
 * @since  1.0.0
 *
 * @return void
 */
function bbforms_register_settings_page() {

    $tabs = array();
    $boxes = array();

    $is_settings_page = bbforms_is_page( 'bbforms_settings' );

    if( $is_settings_page ) {

        // Loop settings sections
        foreach( bbforms_get_settings_sections() as $section_id => $section ) {

            $meta_boxes = array();

            /**
             * Filter: bbforms_settings_{$section_id}_meta_boxes
             *
             * @param array $meta_boxes
             *
             * @return array
             */
            $meta_boxes = apply_filters( "bbforms_settings_{$section_id}_meta_boxes", $meta_boxes );

            if( ! empty( $meta_boxes ) ) {

                // Loop settings section meta boxes
                foreach( $meta_boxes as $meta_box_id => $meta_box ) {

                    // Check meta box tabs
                    if( isset( $meta_box['tabs'] ) && ! empty( $meta_box['tabs'] ) ) {

                        // Loop meta box tabs
                        foreach( $meta_box['tabs'] as $tab_id => $tab ) {

                            $tab['id'] = $tab_id;

                            $meta_box['tabs'][$tab_id] = $tab;

                        }

                    }

                    // Only add settings meta box if has fields
                    if( isset( $meta_box['fields'] ) && ! empty( $meta_box['fields'] ) ) {

                        // Loop meta box fields
                        foreach( $meta_box['fields'] as $field_id => $field ) {

                            $field['id'] = $field_id;

                            // Support for group fields
                            if( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {

                                foreach( $field['fields'] as $group_field_id => $group_field ) {

                                    $field['fields'][$group_field_id]['id'] = $group_field_id;

                                }

                            }

                            $meta_box['fields'][$field_id] = $field;

                        }

                        $meta_box['id'] = $meta_box_id;

                        $meta_box['display_cb'] = false;
                        $meta_box['admin_menu_hook'] = false;
                        $meta_box['priority'] = 'high'; // Fixes issue with CMB2 2.9.0

                        $meta_box['show_on'] = array(
                            'key'   => 'options-page',
                            'value' => array( 'bbforms_settings' ),
                        );

                        $box = new_cmb2_box( $meta_box );

                        $box->object_type( 'options-page' );

                        $boxes[] = $box;

                    }
                }

                $tabs[] = array(
                    'id'    => $section_id,
                    'title' => ( ( isset( $section['icon'] ) ) ? '<i class="dashicons ' . esc_attr( $section['icon'] ) . '"></i>' : '' ) . esc_html( $section['title'] ),
                    'desc'  => '',
                    'boxes' => array_keys( $meta_boxes ),
                );
            }
        }

    }

    try {
        // Create the options page
        new Cmb2_Metatabs_Options( array(
            'key'      => 'bbforms_settings',
            'class'    => 'bbforms-page',
            'title'    => __( 'Settings', 'bbforms' ),
            'topmenu'  => 'bbforms',
            'cols'     => 1,
            'boxes'    => $boxes,
            'tabs'     => $tabs,
            'menuargs' => array(
                'menu_title' => __( 'Settings', 'bbforms' ),
                'capability'        => 'manage_options',
                'view_capability'   => 'manage_options',
            ),
            'savetxt' => __( 'Save Settings', 'bbforms' ),
            'resettxt' => __( 'Reset Settings', 'bbforms' ),
        ) );
    } catch ( Exception $e ) {

    }

}
add_action( 'cmb2_admin_init', 'bbforms_register_settings_page', 12 );

/**
 * Registered settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function bbforms_get_settings_sections() {

    $bbforms_settings_sections = array(
        'general' => array(
            'title' => __( 'Settings', 'bbforms' ),
            'icon' => 'dashicons-admin-settings',
        ),
        'submissions' => array(
            'title' => __( 'Submissions', 'bbforms' ),
            'icon' => 'dashicons-upload',
        ),
        'messages' => array(
            'title' => __( 'Messages', 'bbforms' ),
            'icon' => 'dashicons-admin-comments',
        ),
    );

    return apply_filters( 'bbforms_settings_sections', $bbforms_settings_sections );

}
