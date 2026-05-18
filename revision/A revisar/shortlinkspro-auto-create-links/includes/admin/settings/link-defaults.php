<?php
/**
 * Admin Link Defaults Settings
 *
 * @package     ShortLinksPro\Admin\Settings\Link_Defaults
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
function shortlinkspro_settings_link_defaults_meta_boxes( $meta_boxes ) {

    $meta_boxes['link_defaults_settings'] = array(
        'title' => shortlinkspro_dashicon( 'admin-links' ) . __( 'Default Link Options', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_link_defaults_settings_fields', array(
            'redirect_type' => array(
                'name'      => __( 'Redirect Type', 'shortlinkspro' ),
                'type'      => 'select',
                'options'   => shortlinkspro_redirect_types(),
                'tooltip'   => __( 'Set the default redirect type to new links.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'link_options' => array(
                'name'      => __( 'Link Options', 'shortlinkspro' ),
                'type'      => 'multicheck_inline',
                'classes'      => 'cmb2-switch',
                'options' => array(
                    'nofollow' => __( 'No Follow', 'shortlinkspro' ) . cmb_tooltip_get_html( __( 'Enable "No Follow" by default to new links. This will add the nofollow and noindex parameters in the HTTP response headers when enabled.', 'shortlinkspro' ) ),
                    'sponsored' => __( 'Sponsored', 'shortlinkspro' ) . cmb_tooltip_get_html( __( 'Enable "Sponsored" by default to new links. This will add the sponsored parameter in the HTTP response headers when enabled.', 'shortlinkspro' ) ),
                    'parameter_forwarding' => __( 'Parameter Forwarding', 'shortlinkspro' ) . cmb_tooltip_get_html( __( 'Enable "Parameter Forwarding" by default to new links. This will forward parameters passed to links when enabled.', 'shortlinkspro' ) ),
                    'tracking' => __( 'Tracking', 'shortlinkspro' ) . cmb_tooltip_get_html( __( 'Enable "Tracking" by default to new links. This will enable clicks tracking when enabled.', 'shortlinkspro' ) ),
                ),
                'select_all_button' => false,
                'default' => array( 'nofollow', 'tracking' ),
                'tooltip'   => __( 'Set the default link options to new links.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'slug_prefix' => array(
                'name'      => __( 'Slug Prefix', 'shortlinkspro' ),
                'desc'      => __( 'Preview:', 'shortlinkspro' ) . ' ' . site_url('/') . '<strong class="shortlinkspro-slug-prefix-preview"></strong>' . 'example-link',
                'type'      => 'text',
                'options'   => shortlinkspro_redirect_types(),
                'tooltip'   => __( 'Set a default slug prefix to new links. Optional.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'slug_length' => array(
                'name'      => __( 'Slug Length', 'shortlinkspro' ),
                'desc'      => __( 'Preview:', 'shortlinkspro' ) . ' ' . site_url('/') . '<span class="shortlinkspro-slug-prefix-preview"></span>' . '<strong class="shortlinkspro-slug-length-preview"></strong>',
                'type'      => 'text',
                'options'   => shortlinkspro_redirect_types(),
                'tooltip'   => __( 'Set a default slug length.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'attributes' => array(
                    'type' => 'number',
                    'min' => 1,
                    'step' => 1,
                ),
                'default' => '4',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_settings_general_meta_boxes', 'shortlinkspro_settings_link_defaults_meta_boxes' );