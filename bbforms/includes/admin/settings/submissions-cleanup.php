<?php
/**
 * Submissions Cleanup Settings
 *
 * @package     BBForms\Admin\Settings\Submissions_Cleanup_Settings
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
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
function bbforms_settings_submissions_cleanup_meta_boxes( $meta_boxes ) {

    $meta_boxes['submissions_cleanup_settings'] = array(
        'title' => bbforms_dashicon( 'trash' ) . __( 'Automatic Submissions Cleanup', 'bbforms' ),
        'fields' => apply_filters( 'bbforms_submissions_cleanup_settings_fields', array(
            'auto_submissions_cleanup' => array(
                'name'      => __( 'Submissions Auto-Cleanup', 'bbforms' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => __( 'Check this option to automatically delete old submissions. You will be able to enter the number of days you want to keep the submissions stored.', 'bbforms' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'auto_submissions_cleanup_days' => array(
                'name'      => __( 'Days Keeping Submissions', 'bbforms' ),
                'type'      => 'text',
                'default'      => '90',
                'tooltip'   => __( 'Enter the number of days you want to keep submissions stored. This will remove submissions older than the number of days entered.', 'bbforms' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'auto_submissions_cleanup' => 'checked'
                ),
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'bbforms_settings_submissions_meta_boxes', 'bbforms_settings_submissions_cleanup_meta_boxes' );