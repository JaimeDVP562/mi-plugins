<?php
/**
 * Clicks Rules Settings
 *
 * @package     ShortLinksPro\Admin\Settings\Clicks_Rules_Settings
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
function shortlinkspro_settings_click_rules_meta_boxes( $meta_boxes ) {

    $meta_boxes['click_rules_settings'] = array(
        'title' => shortlinkspro_dashicon( 'shield-alt' ) . __( 'Rules', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_click_rules_settings_fields', array(
            'excluded_ips' => array(
                'name'      => __( 'Excluded IP Addresses', 'shortlinkspro' ),
                'type'      => 'text',
                'tooltip'   => __( 'Enter a comma-separated list of IP addresses or IP ranges you want to exclude from click tracking. Example: 192.168.0.1, 192.168.2.1, 192.168.*.*', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'exclude_robots' => array(
                'name'      => __( 'Exclude Robots', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => __( 'Check this option to exclude known robots from click tracking.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_settings_clicks_tracking_meta_boxes', 'shortlinkspro_settings_click_rules_meta_boxes' );