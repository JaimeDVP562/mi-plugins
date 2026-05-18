<?php
/**
 * Admin
 *
 * @package GamiPress\Credly\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_CREDLY_DIR . 'includes/admin/settings.php';
require_once GAMIPRESS_CREDLY_DIR . 'includes/admin/meta-boxes.php';

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
function gamipress_credly_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_credly_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * Plugin Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_credly_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-credly-license'] = array(
        'title' => __( 'GamiPress Credly', 'gamipress-credly' ),
        'fields' => array(
            'gamipress_credly_license' => array(
                'name' => __( 'License', 'gamipress-credly' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_CREDLY_FILE,
                'item_name' => 'Credly',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_credly_licenses_meta_boxes' );

/**
 * Plugin automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_credly_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-credly'] = __( 'Credly', 'gamipress-credly' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_credly_automatic_updates' );