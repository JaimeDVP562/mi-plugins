<?php
/**
 * Form Messages Settings
 *
 * @package     BBForms\Admin\Settings\Form_Messages_Settings
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
function bbforms_settings_form_messages_meta_boxes( $meta_boxes ) {

    $messages = bbforms_get_form_messages();
    $labels = bbforms_get_form_messages_labels();
    $desc = bbforms_get_form_messages_desc();

    $fields = array();

    foreach( $messages as $key => $message ) {
        $fields[$key] = array(
            'name'      => ( isset( $labels[$key] ) ? $labels[$key] : $key ),
            'desc'      => __( 'Default: ', 'bbforms' ) . '"' . wp_kses_post( $message ) . '"',
            'type'      => 'text',
            'tooltip'   => ( isset( $desc[$key] ) ? $desc[$key] : '' ),
            'label_cb' => 'cmb_tooltip_label_cb',
        );
    }

    $meta_boxes['form_messages_notice'] = array(
        'title' => ' ',
        'fields' => apply_filters( 'bbforms_form_messages_notice_fields', array(
            'form_messages_info' => array(
                'save_field' => false,
                'type' => 'title',
                'render_row_cb' => 'bbforms_form_messages_info_row'
            )
        ) )
    );

    $meta_boxes['form_messages_settings'] = array(
        'title' => bbforms_dashicon( 'editor-table' ) . __( 'Form Messages', 'bbforms' ),
        'fields' => apply_filters( 'bbforms_form_messages_settings_fields', $fields )
    );

    return $meta_boxes;

}
add_filter( 'bbforms_settings_messages_meta_boxes', 'bbforms_settings_form_messages_meta_boxes' );

function bbforms_form_messages_info_row() {

    ?>
    <div class="bbforms-notice bbforms-notice-info">
        <?php esc_html_e( 'IMPORTANT: Overriding messages will make them no-translatable since WordPress is only able to translate messages inside the plugin\'s code.', 'bbforms'); ?>
    </div>
    <?php

}