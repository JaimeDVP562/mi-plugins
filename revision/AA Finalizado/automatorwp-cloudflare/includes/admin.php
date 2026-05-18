<?php
/**
 * Admin Settings
 *
 * @package     AutomatorWP\Cloudflare\Admin
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the Cloudflare tab in AutomatorWP → Settings
 *
 * @since 1.0.0
 *
 * @param array $sections
 * @return array
 */
function automatorwp_cloudflare_settings_sections( $sections ) {

    $sections['cloudflare'] = array(
        'title' => __( 'Cloudflare', 'automatorwp-cloudflare' ),
        'icon'  => 'dashicons-cloud',
    );

    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_cloudflare_settings_sections' );

/**
 * Register the Cloudflare settings meta box and fields
 *
 * @since 1.0.0
 *
 * @param array $meta_boxes
 * @return array
 */
function automatorwp_cloudflare_settings_meta_boxes( $meta_boxes ) {

    $meta_boxes['cloudflare-settings'] = array(
        'title'  => __( 'Cloudflare Settings', 'automatorwp-cloudflare' ),
        'fields' => array(

            'cloudflare_api_token' => array(
                'name' => __( 'API Token:', 'automatorwp-cloudflare' ),
                'desc' => __( 'Create a token with "Cache Purge" permission from your <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">Cloudflare dashboard</a>.', 'automatorwp-cloudflare' ),
                'type' => 'text',
            ),

            'cloudflare_zone_id' => array(
                'name' => __( 'Zone ID:', 'automatorwp-cloudflare' ),
                'desc' => __( 'Found in the right sidebar of your domain\'s Overview page in Cloudflare.', 'automatorwp-cloudflare' ),
                'type' => 'text',
            ),

        ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_cloudflare_meta_boxes', 'automatorwp_cloudflare_settings_meta_boxes' );