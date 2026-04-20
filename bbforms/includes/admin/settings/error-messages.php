<?php
/**
 * Error Messages Settings
 *
 * @package     BBForms\Admin\Settings\Error_Messages_Settings
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
function bbforms_settings_error_messages_meta_boxes( $meta_boxes ) {

    $messages = bbforms_get_error_messages();
    $labels = bbforms_get_error_messages_labels();
    $desc = bbforms_get_error_messages_desc();

    $fields = array();

    foreach( $messages as $key => $message ) {
        $fields[$key] = array(
            'name'      => ( isset( $labels[$key] ) ? $labels[$key] : $key ),
            'desc'      => __( 'Default: ', 'bbforms' ) . '"' . $message . '"',
            'type'      => 'text',
            'tooltip'   => ( isset( $desc[$key] ) ? $desc[$key] : '' ),
            'label_cb' => 'cmb_tooltip_label_cb',
        );
    }

    $meta_boxes['error_messages_settings'] = array(
        'title' => bbforms_dashicon( 'welcome-comments' ) . __( 'Fields Error Messages', 'bbforms' ),
        'fields' => apply_filters( 'bbforms_error_messages_settings_fields', $fields )
    );

    return $meta_boxes;

}
add_filter( 'bbforms_settings_messages_meta_boxes', 'bbforms_settings_error_messages_meta_boxes' );