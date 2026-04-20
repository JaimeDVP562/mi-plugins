<?php
/**
 * Admin Settings Page
 *
 * @package     ShortLinksPro\Admin\Settings
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once SHORTLINKSPRO_DIR . 'includes/admin/settings/general.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/settings/link-defaults.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/settings/clicks-rules.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/settings/clicks-cleanup.php';

/**
 * Register Settings using the WP Settings API.
 *
 * @since  1.0.0
 *
 * @return void
 */
function shortlinkspro_register_settings() {

	register_setting( 'shortlinkspro_settings', 'shortlinkspro_settings', array(
        'type' => 'array',
        'sanitize_callback' => 'shortlinkspro_settings_sanitization_cb',
    ) );

}
add_action( 'admin_init', 'shortlinkspro_register_settings' );
add_action( 'rest_api_init', 'shortlinkspro_register_settings' );

/**
 * Sanitization callback for the settings.
 *
 * @since  1.0.0
 *
 * @return void
 */
function shortlinkspro_settings_sanitization_cb( $settings ) {

    global $shortlinkspro_settings_sanitized;

    if( $shortlinkspro_settings_sanitized === true ) {
        return $settings;
    }

    $shortlinkspro_settings_sanitized = true;

    return map_deep( $settings, 'sanitize_text_field' );

}

/**
 * Register settings page.
 *
 * @since  1.0.0
 *
 * @return void
 */
function shortlinkspro_register_settings_page() {

    $tabs = array();
    $boxes = array();

    $is_settings_page = ( isset( $_GET['page'] ) && $_GET['page'] === 'shortlinkspro_settings' );

    if( $is_settings_page ) {

        // Loop settings sections
        foreach( shortlinkspro_get_settings_sections() as $section_id => $section ) {

            $meta_boxes = array();

            /**
             * Filter: shortlinkspro_settings_{$section_id}_meta_boxes
             *
             * @param array $meta_boxes
             *
             * @return array
             */
            $meta_boxes = apply_filters( "shortlinkspro_settings_{$section_id}_meta_boxes", $meta_boxes );

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
                            'value' => array( 'shortlinkspro_settings' ),
                        );

                        $box = new_cmb2_box( $meta_box );

                        $box->object_type( 'options-page' );

                        $boxes[] = $box;

                    }
                }

                $tabs[] = array(
                    'id'    => $section_id,
                    'title' => ( ( isset( $section['icon'] ) ) ? '<i class="dashicons ' . $section['icon'] . '"></i>' : '' ) . $section['title'],
                    'desc'  => '',
                    'boxes' => array_keys( $meta_boxes ),
                );
            }
        }

    }

    try {
        // Create the options page
        new Cmb2_Metatabs_Options( array(
            'key'      => 'shortlinkspro_settings',
            'class'    => 'shortlinkspro-page',
            'title'    => __( 'Settings', 'shortlinkspro' ),
            'topmenu'  => 'shortlinkspro',
            'cols'     => 1,
            'boxes'    => $boxes,
            'tabs'     => $tabs,
            'menuargs' => array(
                'menu_title' => __( 'Settings', 'shortlinkspro' ),
                'capability'        => 'manage_options',
                'view_capability'   => 'manage_options',
            ),
            'savetxt' => __( 'Save Settings', 'shortlinkspro' ),
            'resettxt' => __( 'Reset Settings', 'shortlinkspro' ),
        ) );
    } catch ( Exception $e ) {

    }

}
add_action( 'cmb2_admin_init', 'shortlinkspro_register_settings_page', 12 );

/**
 * Registered settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function shortlinkspro_get_settings_sections() {

    $shortlinkspro_settings_sections = array(
        'general' => array(
            'title' => __( 'Settings', 'shortlinkspro' ),
            'icon' => 'dashicons-admin-settings',
        ),
        'clicks_tracking' => array(
            'title' => __( 'Click Tracking', 'shortlinkspro' ),
            'icon' => 'dashicons-chart-bar',
        ),
    );

    return apply_filters( 'shortlinkspro_settings_sections', $shortlinkspro_settings_sections );

}
