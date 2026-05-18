<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Smoove\Admin
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

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
function automatorwp_smoove_get_option( $option_name, $default = false ) {
    $prefix = 'automatorwp_smoove_';
    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_smoove_settings_sections( $automatorwp_settings_sections ) {
    $automatorwp_settings_sections['smoove'] = array(
            'title' => __( 'Smoove', 'automatorwp-smoove' ),
            'icon'  => 'dashicons-admin-generic',
    );
    return $automatorwp_settings_sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_smoove_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_smoove_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_smoove_';

    $meta_boxes['automatorwp-smoove-settings'] = array(
            'title'  => automatorwp_dashicon( 'admin-generic' ) . __( 'Smoove API Settings', 'automatorwp-smoove' ),
            'fields' => apply_filters( 'automatorwp_smoove_settings_fields', array(
                    $prefix . 'api_key' => array(
                            'name' => __( 'API Key', 'automatorwp-smoove' ),
                            'desc' => __( 'Enter your Smoove API key. You can find this in your Smoove account settings.', 'automatorwp-smoove' ),
                            'type' => 'text',
                    ),
            ) ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_smoove_meta_boxes', 'automatorwp_smoove_settings_meta_boxes' );