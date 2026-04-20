<?php
/**
 * Admin Tools Page
 *
 * @package     ShortLinksPro\Admin\Tools
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// General
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/clicks-cleanup.php';

/**
 * Register tools page.
 *
 * @since  1.0.0
 *
 * @return void
 */
function shortlinkspro_register_tools_page() {

    $tabs = array();
    $boxes = array();

    $is_tools_page = ( isset( $_GET['page'] ) && $_GET['page'] === 'shortlinkspro_tools' );

    if( $is_tools_page ) {

        // Loop tools sections
        foreach( shortlinkspro_get_tools_sections() as $section_id => $section ) {

            $meta_boxes = array();

            /**
             * Filter: shortlinkspro_tools_{$section_id}_meta_boxes
             *
             * @param array $meta_boxes
             *
             * @return array
             */
            $meta_boxes = apply_filters( "shortlinkspro_tools_{$section_id}_meta_boxes", $meta_boxes );

            if( ! empty( $meta_boxes ) ) {

                // Loop tools section meta boxes
                foreach( $meta_boxes as $meta_box_id => $meta_box ) {

                    // Check meta box tabs
                    if( isset( $meta_box['tabs'] ) && ! empty( $meta_box['tabs'] ) ) {

                        // Loop meta box tabs
                        foreach( $meta_box['tabs'] as $tab_id => $tab ) {

                            $tab['id'] = $tab_id;

                            $meta_box['tabs'][$tab_id] = $tab;

                        }

                    }

                    // Only add tools meta box if has fields
                    if( isset( $meta_box['fields'] ) && ! empty( $meta_box['fields'] ) ) {

                        // Loop meta box fields
                        foreach( $meta_box['fields'] as $field_id => $field ) {

                            $field['id'] = $field_id;

                            $meta_box['fields'][$field_id] = $field;

                        }

                        $meta_box['id'] = $meta_box_id;

                        $meta_box['display_cb'] = false;
                        $meta_box['admin_menu_hook'] = false;
                        $meta_box['priority'] = 'high'; // Fixes issue with CMB2 2.9.0

                        $meta_box['show_on'] = array(
                            'key'   => 'options-page',
                            'value' => array( 'shortlinkspro_tools' ),
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

    $minimum_role = shortlinkspro_get_manager_capability();

    try {
        // Create the options page
        new Cmb2_Metatabs_Options( array(
            'key'      => 'shortlinkspro_tools',
            'class'    => 'shortlinkspro-page',
            'title'    => __( 'Tools', 'shortlinkspro' ),
            'topmenu'  => 'shortlinkspro',
            'cols'     => 1,
            'boxes'    => $boxes,
            'tabs'     => $tabs,
            'menuargs' => array(
                'menu_title'        => __( 'Tools', 'shortlinkspro' ),
                'capability'        => $minimum_role,
                'view_capability'   => $minimum_role,
            ),
            'savetxt' => false,
            'resettxt' => false,
        ) );
    } catch ( Exception $e ) {

    }

}
add_action( 'cmb2_admin_init', 'shortlinkspro_register_tools_page', 11 );

/**
 * Registered tools sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function shortlinkspro_get_tools_sections() {

    $shortlinkspro_tools_sections = array(
        'general' => array(
            'title' => __( 'General', 'shortlinkspro' ),
            'icon' => 'dashicons-admin-tools',
        ),
    );

    return apply_filters( 'shortlinkspro_tools_sections', $shortlinkspro_tools_sections );

}

/**
 * Adds a custom nonce on the tools page
 *
 * @since 1.0.0
 *
 * @param array  $cmb_id      The current box ID.
 * @param int    $object_id   The ID of the current object.
 * @param string $object_type The type of object you are working with.
 *                            Usually `post` (this applies to all post-types).
 *                            Could also be `comment`, `user` or `options-page`.
 * @param array  $cmb         This CMB2 object.
 */
function shortlinkspro_tools_nonce( $cmb_id, $object_id, $object_type, $cmb ) {

    global $shortlinkspro_tools_nonce;

    if( $object_id !== 'shortlinkspro_tools' ) {
        return;
    }

    if( $object_type !== 'options-page' ) {
        return;
    }

    if( $shortlinkspro_tools_nonce ) {
        return;
    }

    wp_nonce_field( 'shortlinkspro_admin' );

}
add_action( 'cmb2_after_form', 'shortlinkspro_tools_nonce', 10, 4 );