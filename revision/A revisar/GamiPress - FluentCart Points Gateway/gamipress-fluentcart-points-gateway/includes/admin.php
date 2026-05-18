<?php
/**
 * Admin
 *
 * @package GamiPress\FluentCart\Points_Gateway\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * FluentCart Points Gateway Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_fluentcart_points_gateway_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-fluentcart-points-gateway-license'] = array(
        'title' => __( 'FluentCart Points Gateway', 'gamipress-fluentcart-points-gateway' ),
        'fields' => array(
            'gamipress_fluentcart_points_gateway_license' => array(
                'name' => __( 'License', 'gamipress-fluentcart-points-gateway' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_FC_POINTS_GATEWAY_FILE,
                'item_name' => 'FluentCart Points Gateway',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_fluentcart_points_gateway_licenses_meta_boxes' );

/**
 * FluentCart Points Gateway automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_fluentcart_points_gateway_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-fluentcart-points-gateway'] = __( 'FluentCart Points Gateway', 'gamipress-fluentcart-points-gateway' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_fluentcart_points_gateway_automatic_updates' );
