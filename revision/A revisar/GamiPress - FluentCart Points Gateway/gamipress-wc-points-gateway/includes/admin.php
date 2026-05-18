<?php
/**
 * Admin
 *
 * @package GamiPress\WooCommerce\Points_Gateway\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Points Gateway Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_wc_points_gateway_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-wc-points-gateway-license'] = array(
        'title' => __( 'WooCommerce Points Gateway', 'gamipress-wc-points-gateway' ),
        'fields' => array(
            'gamipress_wc_points_gateway_license' => array(
                'name' => __( 'License', 'gamipress-wc-points-gateway' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_WC_POINTS_GATEWAY_FILE,
                'item_name' => 'WooCommerce Points Gateway',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_wc_points_gateway_licenses_meta_boxes' );

/**
 * WC Points Gateway automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_wc_points_gateway_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-wc-points-gateway'] = __( 'WooCommerce Points Gateway', 'gamipress-wc-points-gateway' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_wc_points_gateway_automatic_updates' );