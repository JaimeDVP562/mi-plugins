<?php
/**
 * Clicks Cleanup Settings
 *
 * @package     ShortLinksPro\Admin\Settings\Clicks_Cleanup_Settings
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
function shortlinkspro_settings_clicks_cleanup_meta_boxes( $meta_boxes ) {

    $meta_boxes['clicks_cleanup_settings'] = array(
        'title' => shortlinkspro_dashicon( 'trash' ) . __( 'Automatic Clicks Cleanup', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_clicks_cleanup_settings_fields', array(
            'auto_clicks_cleanup' => array(
                'name'      => __( 'Clicks Auto-Cleanup', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => __( 'Check this option to automatically delete old click hits. You will be able to enter the number of days you want to keep the click hits stored.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'auto_clicks_cleanup_days' => array(
                'name'      => __( 'Days Keeping Clicks', 'shortlinkspro' ),
                'type'      => 'text',
                'default'      => '90',
                'tooltip'   => __( 'Enter the number of days you want to keep click hits stored. This will remove click hits older than the number of days entered.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'auto_clicks_cleanup' => 'checked'
                ),
            ),
            'clicks_cleanup_link' => array(
                'name'      => __( 'the tools page', 'shortlinkspro' ),
                /* translators: %s: Link to tools page. */
                'desc'      => __( 'You can cleanup old clicks manually from %s.', 'shortlinkspro' ),
                'type'      => 'title',
                'render_row_cb' => 'shortlinkspro_clicks_cleanup_link',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_settings_clicks_tracking_meta_boxes', 'shortlinkspro_settings_clicks_cleanup_meta_boxes' );

/**
 * Helper function to render the clicks cleanup link
 *
 * @param $field_args
 * @param $field
 */
function shortlinkspro_clicks_cleanup_link( $field_args, $field ) {
    $name = $field->args( 'name' );
    $desc = $field->args( 'desc' );
    $link = '<a href="' . admin_url( 'admin.php?page=shortlinkspro_tools' ) . '" class="shortlinkspro-clicks-cleanup-link">' . $name . '</a>';

    echo '<div class="cmb-row shortlinkspro-clicks-cleanup-row">'
        . sprintf( $desc , $link )
        . '</div>';
}