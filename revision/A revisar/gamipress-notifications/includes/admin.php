<?php
/**
 * Admin
 *
 * @package     GamiPress\Notifications\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Update existing notifications page title to Spanish
 *
 * @since 1.6.0
 */
/*function gamipress_notifications_update_page_title() {
    $page_id = get_option( 'gamipress_notifications_page_id' );
    
    if ( $page_id ) {
        $page = get_post( $page_id );
        if ( $page ) {
            $update_args = array( 'ID' => $page_id );
            
            // Update title if it's still in English
            if ( $page->post_title === 'My Notifications' ) {
                $update_args['post_title'] = __( 'Mis notificaciones', 'gamipress-notifications' );
            }
            
            // Ensure content only has the shortcode
            if ( strpos( $page->post_content, '[gamipress_notifications' ) === false || 
                 strpos( $page->post_content, 'Mis notificaciones' ) !== false ) {
                $update_args['post_content'] = '[gamipress_notifications limit="50"]';
            }
            
            if ( count( $update_args ) > 1 ) {
                wp_update_post( $update_args );
            }
        }
    } else {
        // Try to find by slug
        $existing_page = get_page_by_path( 'my-notifications' );
        if ( $existing_page ) {
            $update_args = array( 'ID' => $existing_page->ID );
            
            if ( $existing_page->post_title === 'My Notifications' ) {
                $update_args['post_title'] = __( 'Mis notificaciones', 'gamipress-notifications' );
            }
            
            if ( strpos( $existing_page->post_content, '[gamipress_notifications' ) === false || 
                 strpos( $existing_page->post_content, 'Mis notificaciones' ) !== false ) {
                $update_args['post_content'] = '[gamipress_notifications limit="50"]';
            }
            
            if ( count( $update_args ) > 1 ) {
                wp_update_post( $update_args );
            }
            
            update_option( 'gamipress_notifications_page_id', $existing_page->ID );
        }
    }
}
//add_action( 'init', 'gamipress_notifications_update_page_title', 5 );
*/

/**
 * Process shortcodes in the notifications page
 *
 * @since 1.6.0
 */
function gamipress_notifications_process_page_content( $content ) {
    global $post;
    
    if ( ! $post ) {
        return $content;
    }
    
    $page_id = get_option( 'gamipress_notifications_page_id' );
    
    if ( $page_id && $post->ID === (int) $page_id && is_main_query() && ! is_admin() ) {
        // Process shortcodes
        $content = do_shortcode( $content );
    }
    
    return $content;
}
add_filter( 'the_content', 'gamipress_notifications_process_page_content', 9 );

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
function gamipress_notifications_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_notifications_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * GamiPress Notifications Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_notifications_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_notifications_';

    // Setup achievement fields
    $achievement_fields = array();

    $original_achievement_fields = GamiPress()->shortcodes['gamipress_achievement']->fields;

    unset( $original_achievement_fields['id'] );

    foreach( $original_achievement_fields as $achievement_field_id => $achievement_field ) {

        if( $achievement_field['type'] === 'checkbox' && isset( $achievement_field['default'] ) ) {
            unset( $achievement_field['default'] );
        }

        $achievement_fields[$prefix . $achievement_field_id] = $achievement_field;
    }

    // Setup rank fields
    $rank_fields = array();

    $original_rank_fields = GamiPress()->shortcodes['gamipress_rank']->fields;

    unset( $original_rank_fields['id'] );

    foreach( $original_rank_fields as $rank_field_id => $rank_field ) {

        if( $rank_field['type'] === 'checkbox' && isset( $rank_field['default'] ) ) {
            unset( $rank_field['default'] );
        }

        // Need to add the 'rank_' prefix for avoid issues with achievement fields names
        $rank_fields[$prefix . 'rank_' . $rank_field_id] = $rank_field;
    }

    // Audio settings
    $audio_query_args = array(
        'type' => array(
            'audio/midi',
            'audio/mpeg',
            'audio/x-aiff',
            'audio/x-pn-realaudio',
            'audio/x-pn-realaudio-plugin',
            'audio/x-realaudio',
            'audio/x-wav',
        ),
    );

    $meta_boxes['gamipress-notifications-settings'] = array(
        'title' => gamipress_dashicon( 'admin-comments' ) . __( 'Notifications', 'gamipress-notifications' ),
        'fields' => apply_filters( 'gamipress_notifications_settings_fields', array_merge( array(

            // Notification settings

            $prefix . 'life' => array(
                'name' => __( 'Life', 'gamipress-notifications' ),
                'desc' => __( 'Number of days an user notification is saved before being automatically deleted.', 'gamipress-notifications' ),
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number'
                ),
                'default' => 1,
            ),
            $prefix . 'disable_live_checks' => array(
                'name' => __( 'Disable live checks', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to completely disable live checks. This will disable the live notification feature but also will reduce server resources consumption.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'admin_bar_menu_label' => array(
                'name' => __( 'Admin Bar Menu Label', 'gamipress-notifications' ),
                'desc' => __( 'Text to show in the admin bar notification menu (bell icon).', 'gamipress-notifications' ),
                'type' => 'text',
                'default' => __( 'Notifications', 'gamipress-notifications' ),
            ),
            $prefix . 'mark_as_read_delay' => array(
                'name' => __( 'Mark as read delay (seconds)', 'gamipress-notifications' ),
                'desc' => __( 'Number of seconds to wait before marking shown notifications as read. 0 means instant mark.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '0',
                ),
                'default' => 0,
            ),
            $prefix . 'click_to_hide' => array(
                'name' => __( 'Click to hide', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to allow user hide the notification clicking on it.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'auto_hide' => array(
                'name' => __( 'Auto-hide', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to automatically hide the notification after the configured delay.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'auto_hide_delay' => array(
                'name' => __( 'Auto-hide Delay', 'gamipress-notifications' ),
                'desc' => __( 'Delay in milliseconds to automatically hide a notification (1,000 milliseconds = 1 second).', 'gamipress-notifications' ),
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number'
                ),
                'default' => 5000,
            ),
            $prefix . 'sound_title' => array(
                'name' => gamipress_dashicon( 'format-audio' ) . ' ' . __( 'Sounds Configuration', 'gamipress-notifications' ),
                //'desc' => __( 'Configure how the achievement automatic output will be displayed', 'gamipress-notifications' ),
                'type' => 'title',
            ),
            $prefix . 'show_sound' => array(
                'name'    => __( 'Show notification sound effect', 'gamipress-notifications' ),
                'desc'    => __( 'Upload, choose or paste the URL of the notification sound to play when a notification gets displayed.', 'gamipress-notifications' ),
                'type'    => 'file',
                'text'    => array(
                    'add_upload_file_text' => __( 'Add or Upload Audio', 'gamipress-notifications' ),
                ),
                'query_args' => $audio_query_args,
            ),
            $prefix . 'hide_sound' => array(
                'name'    => __( 'Hide notification sound effect', 'gamipress-notifications' ),
                'desc'    => __( 'Upload, choose or paste the URL of the notification sound to play when a notification gets hidden (by the user or automatically).', 'gamipress-notifications' ),
                'type'    => 'file',
                'text'    => array(
                    'add_upload_file_text' => __( 'Add or Upload Audio', 'gamipress-notifications' ),
                ),
                'query_args' => $audio_query_args,
            ),
            $prefix . 'style_title' => array(
                'name' => gamipress_dashicon( 'admin-customizer' ) . ' ' . __( 'Style Configuration', 'gamipress-notifications' ),
                //'desc' => __( 'Configure how the achievement automatic output will be displayed', 'gamipress-notifications' ),
                'type' => 'title',
            ),
            $prefix . 'position' => array(
                'name' => __( 'Position', 'gamipress-notifications' ),
                'desc' => __( 'Check the position where you want to place the notifications.', 'gamipress-notifications' ),
                'type' => 'radio',
                'inline' => true,
                'options' => array(
                    'top left'      => '',
                    'top center'    => '',
                    'top right'     => '',
                    'left middle'   => '',
                    'right middle'  => '',
                    'bottom left'   => '',
                    'bottom center' => '',
                    'bottom right'  => '',
                ),
                'default' => 'bottom right'
            ),
            $prefix . 'width' => array(
                'name' => __( 'Width', 'gamipress-notifications' ),
                'desc' => __( 'px', 'gamipress-notifications' )
                . '<br>' . __( 'Set the notification maximum width in pixels. A recommended size could be a value between 300 and 600. Leave blank for no maximum.', 'gamipress-notifications' )
                . '<br>' . __( '<strong>Important:</strong> On mobile screens notifications width will be adapted automatically to the screen size.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '0',
                ),
            ),
            $prefix . 'background_color' => array(
                'name' => __( 'Background Color', 'gamipress-notifications' ),
                'desc' => __( 'Set the notification background color.', 'gamipress-notifications' ),
                'type' => 'colorpicker',
                'options' => array( 'alpha' => true ),
            ),
            $prefix . 'title_color' => array(
                'name' => __( 'Title Color', 'gamipress-notifications' ),
                'desc' => __( 'Set the text color of the notification title.', 'gamipress-notifications' ),
                'type' => 'colorpicker',
                'options' => array( 'alpha' => true ),
            ),
            $prefix . 'title_font_size' => array(
                'name' => __( 'Title Font Size', 'gamipress-notifications' ),
                'desc' => __( 'px', 'gamipress-notifications' )
                . '<br>' .__( 'Set the font size of the notification title. Leave blank to keep your theme font size.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '1',
                ),
            ),
            $prefix . 'text_color' => array(
                'name' => __( 'Text Color', 'gamipress-notifications' ),
                'desc' => __( 'Set the text color of the notification content.', 'gamipress-notifications' ),
                'type' => 'colorpicker',
                'options' => array( 'alpha' => true ),
            ),
            $prefix . 'text_font_size' => array(
                'name' => __( 'Text Font Size', 'gamipress-notifications' ),
                'desc' => __( 'px', 'gamipress-notifications' )
                . '<br>' .__( 'Set the font size of the notification content. Leave blank to keep your theme font size.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '1',
                ),
            ),
            $prefix . 'link_color' => array(
                'name' => __( 'Link Color', 'gamipress-notifications' ),
                'desc' => __( 'Set the text color of the notification link.', 'gamipress-notifications' ),
                'type' => 'colorpicker',
                'options' => array( 'alpha' => true ),
            ),
            $prefix . 'border_width' => array(
                'name' => __( 'Border Width', 'gamipress-notifications' ),
                'desc' => __( 'px', 'gamipress-notifications' )
                . '<br>' .__( 'Set the width of the notification\'s border.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '0',
                ),
            ),
            $prefix . 'border_color' => array(
                'name' => __( 'Border Color', 'gamipress-notifications' ),
                'desc' => __( 'Set the text color of the notification\'s border.', 'gamipress-notifications' ),
                'type' => 'colorpicker',
                'options' => array( 'alpha' => true ),
            ),
            $prefix . 'border_radius' => array(
                'name' => __( 'Border Radius', 'gamipress-notifications' ),
                'desc' => __( 'px', 'gamipress-notifications' )
                . '<br>' .__( 'Set the radius of the notification\'s border.', 'gamipress-notifications' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '0',
                ),
            ),

            // Achievement notification settings

            $prefix . 'disable_achievements' => array(
                'name' => __( 'Disable achievements completion notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new achievements.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'achievement_title_pattern' => array(
                'name' => __( 'Achievement Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New achievement notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'achievement_content_pattern' => array(
                'name' => __( 'Achievement Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New achievement notification content pattern to be shown after the achievement (leave blank to hide it). Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_achievement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),
            $prefix . 'achievement_template_args_title' => array(
                'name' => __( 'Achievement Output Configuration', 'gamipress-notifications' ),
                'desc' => __( 'Configure how the achievement automatic output will be displayed', 'gamipress-notifications' ),
                'type' => 'title',
            ),

            // Step notification settings

            $prefix . 'disable_steps' => array(
                'name' => __( 'Disable steps completion notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new completed steps.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'step_title_pattern' => array(
                'name' => __( 'Step Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New step completed notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'step_content_pattern' => array(
                'name' => __( 'Step Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New step completed notification content pattern. Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_step_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points award notification settings

            $prefix . 'disable_points_awards' => array(
                'name' => __( 'Disable points award notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new points awards.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'points_award_title_pattern' => array(
                'name' => __( 'Points Award Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New points award notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'points_award_content_pattern' => array(
                'name' => __( 'Points Award Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New points award notification content pattern. Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_points_award_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points deducts notification settings

            $prefix . 'disable_points_deducts' => array(
                'name' => __( 'Disable points deduction notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new points deduction.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'points_deduct_title_pattern' => array(
                'name' => __( 'Points Deduct Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New points deduction notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'points_deduct_content_pattern' => array(
                'name' => __( 'Points Award Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New points deduction notification content pattern. Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_points_deduct_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Rank notification settings

            $prefix . 'disable_ranks' => array(
                'name' => __( 'Disable rank reached notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new rank reached.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'rank_title_pattern' => array(
                'name' => __( 'Rank Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New rank notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'rank_content_pattern' => array(
                'name' => __( 'Rank Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New rank notification content pattern to be shown after the rank (leave blank to hide it). Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_rank_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),
            $prefix . 'rank_template_args_title' => array(
                'name' => __( 'Rank Output Configuration', 'gamipress-notifications' ),
                'desc' => __( 'Configure how the rank automatic output will be displayed', 'gamipress-notifications' ),
                'type' => 'title',
            ),

            // Rank Requirement notification settings

            $prefix . 'disable_rank_requirements' => array(
                'name' => __( 'Disable rank requirements completion notifications', 'gamipress-notifications' ),
                'desc' => __( 'Check this option to do not notify to users about new completed rank requirements.', 'gamipress-notifications' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'rank_requirement_title_pattern' => array(
                'name' => __( 'Rank Requirement Title Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New rank requirement completed notification title pattern (leave blank to hide it). For a list available tags, check next field description.', 'gamipress-notifications' ),
                'type' => 'text',
            ),
            $prefix . 'rank_requirement_content_pattern' => array(
                'name' => __( 'Rank Requirement Content Pattern', 'gamipress-notifications' ),
                'desc' => __( 'New rank requirement completed notification content pattern. Available tags:', 'gamipress-notifications' )
                    . gamipress_notifications_get_rank_requirement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

        ), $achievement_fields, $rank_fields ) ),
        'tabs' => apply_filters( 'gamipress_notifications_settings_tabs', array(
            'notification' => array(
                'icon' => 'dashicons-admin-comments',
                'title' => __( 'Notification', 'gamipress-notifications' ),
                'fields' => array(
                    $prefix . 'life',
                    $prefix . 'disable_live_checks',
                    $prefix . 'delay',
                    $prefix . 'attempts_to_cancel',
                    $prefix . 'click_to_hide',
                    $prefix . 'auto_hide',
                    $prefix . 'auto_hide_delay',
                    $prefix . 'sound_title',
                    $prefix . 'show_sound',
                    $prefix . 'hide_sound',
                    $prefix . 'style_title',
                    $prefix . 'position',
                    $prefix . 'width',
                    $prefix . 'background_color',
                    $prefix . 'title_color',
                    $prefix . 'title_font_size',
                    $prefix . 'text_color',
                    $prefix . 'text_font_size',
                    $prefix . 'link_color',
                    $prefix . 'border_width',
                    $prefix . 'border_color',
                    $prefix . 'border_radius',
                ),
            ),
            'achievement' => array(
                'icon' => 'dashicons-awards',
                'title' => __( 'Achievements', 'gamipress-notifications' ),
                'fields' => array_merge( array(
                    $prefix . 'disable_achievements',
                    $prefix . 'achievement_title_pattern',
                    $prefix . 'achievement_content_pattern',
                    $prefix . 'achievement_template_args_title',
                ), array_keys( $achievement_fields ) ),
            ),
            'steps' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Steps', 'gamipress-notifications' ),
                'fields' => array(
                    $prefix . 'disable_steps',
                    $prefix . 'step_title_pattern',
                    $prefix . 'step_content_pattern',
                ),
            ),
            'points_awards' => array(
                'icon' => 'dashicons-star-filled',
                'title' => __( 'Points Awards', 'gamipress-notifications' ),
                'fields' => array(
                    $prefix . 'disable_points_awards',
                    $prefix . 'points_award_title_pattern',
                    $prefix . 'points_award_content_pattern',
                ),
            ),
            'points_deducts' => array(
                'icon' => 'dashicons-star-empty',
                'title' => __( 'Points Deducts', 'gamipress-notifications' ),
                'fields' => array(
                    $prefix . 'disable_points_deducts',
                    $prefix . 'points_deduct_title_pattern',
                    $prefix . 'points_deduct_content_pattern',
                ),
            ),
            'ranks' => array(
                'icon' => 'dashicons-rank',
                'title' => __( 'Ranks', 'gamipress-notifications' ),
                'fields' => array_merge( array(
                    $prefix . 'disable_ranks',
                    $prefix . 'rank_title_pattern',
                    $prefix . 'rank_content_pattern',
                    $prefix . 'rank_template_args_title',
                ), array_keys( $rank_fields ) ),
            ),
            'rank_requirements' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Rank Requirements', 'gamipress-notifications' ),
                'fields' => array(
                    $prefix . 'disable_rank_requirements',
                    $prefix . 'rank_requirement_title_pattern',
                    $prefix . 'rank_requirement_content_pattern',
                ),
            ),
        ) ),
        'vertical_tabs' => true
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_notifications_settings_meta_boxes' );

/**
 * GamiPress Notifications Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_notifications_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-notifications-license'] = array(
        'title' => __( 'GamiPress Notifications', 'gamipress-notifications' ),
        'fields' => array(
            'gamipress_notifications_license' => array(
                'name' => __( 'License', 'gamipress-notifications' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_NOTIFICATIONS_FILE,
                'item_name' => 'Notifications',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_notifications_licenses_meta_boxes' );

/**
 * GamiPress Notifications automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_notifications_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-notifications'] = __( 'Notifications', 'gamipress-notifications' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_notifications_automatic_updates' );

/**
 * Create notifications page on plugin activation or when needed
 *
 * @since 1.6.0
 */
function gamipress_notifications_create_notifications_page() {
    // Check if page already exists and is published
    $page_id = get_option( 'gamipress_notifications_page_id' );

    if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
        // Ensure it's a page type and fix any issues
        $page = get_post( $page_id );
        if ( $page ) {
            $update_args = array( 'ID' => $page_id );
            $should_update = false;
            
            // Ensure post_type is 'page'
            if ( $page->post_type !== 'page' ) {
                $update_args['post_type'] = 'page';
                $should_update = true;
            }
            
            // Update title to Spanish if it's still in English
            if ( $page->post_title === 'My Notifications' ) {
                $update_args['post_title'] = __( 'Mis notificaciones', 'gamipress-notifications' );
                $should_update = true;
            }
            
            if ( $should_update ) {
                wp_update_post( $update_args );
            }
        }
        return;
    }

    // Try to find existing page by slug
    $existing_page = get_page_by_path( 'my-notifications' );

    if ( $existing_page && $existing_page->post_status === 'publish' ) {
        update_option( 'gamipress_notifications_page_id', $existing_page->ID );
        
        $update_args = array( 'ID' => $existing_page->ID );
        $should_update = false;
        
        // Ensure post_type is 'page'
        if ( $existing_page->post_type !== 'page' ) {
            $update_args['post_type'] = 'page';
            $should_update = true;
        }
        
        // Update title to Spanish if it's still in English
        if ( $existing_page->post_title === 'My Notifications' ) {
            $update_args['post_title'] = __( 'Mis notificaciones', 'gamipress-notifications' );
            $should_update = true;
        }
        
        if ( $should_update ) {
            wp_update_post( $update_args );
        }
        return;
    }

    // Create new page
    $page_id = wp_insert_post( array(
        'post_title'     => __( 'Mis notificaciones', 'gamipress-notifications' ),
        'post_name'      => 'my-notifications',
        'post_content'   => '[gamipress_notifications limit="50"]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'post_author'    => 1,
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ) );

    if ( $page_id ) {
        update_option( 'gamipress_notifications_page_id', $page_id );
    }
}
register_activation_hook( GAMIPRESS_NOTIFICATIONS_FILE, 'gamipress_notifications_create_notifications_page' );

// Also try to create page on admin init if it doesn't exist
add_action( 'admin_init', 'gamipress_notifications_create_notifications_page' );

/**
 * Add diagnostic information to admin
 *
 * @since 1.6.0
 */
function gamipress_notifications_admin_notices() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $page_id = get_option( 'gamipress_notifications_page_id' );
    $page_url = gamipress_notifications_get_page_url();

    if ( ! $page_id || get_post_status( $page_id ) !== 'publish' ) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>GamiPress Notifications:</strong> The notifications page could not be created automatically. ';
        echo 'Please create a page with the shortcode <code>[gamipress_notifications]</code> and set it as the notifications page.</p>';
        echo '<p>Alternatively, you can <a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">create the page manually</a> with slug "my-notifications".</p>';
        echo '</div>';
    } else {
        $page = get_post( $page_id );
        if ( strpos( $page->post_content, '[gamipress_notifications' ) === false ) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>GamiPress Notifications:</strong> The notifications page exists but does not contain the required shortcode. ';
            echo 'Please add <code>[gamipress_notifications]</code> to the page content.</p>';
            echo '<p><a href="' . esc_url( get_edit_post_link( $page_id ) ) . '">Edit the notifications page</a> | ';
            echo '<a href="' . esc_url( add_query_arg( 'gamipress_notifications_recreate_page', '1', admin_url() ) ) . '">Recreate page</a></p>';
            echo '</div>';
        }
    }

    // Handle page recreation
    if ( isset( $_GET['gamipress_notifications_recreate_page'] ) && current_user_can( 'manage_options' ) ) {
        delete_option( 'gamipress_notifications_page_id' );
        gamipress_notifications_create_notifications_page();
        wp_redirect( remove_query_arg( 'gamipress_notifications_recreate_page' ) );
        exit;
    }
}
add_action( 'admin_notices', 'gamipress_notifications_admin_notices' );

/**
 * Get notifications page URL
 *
 * @since 1.6.0
 *
 * @return string
 */
function gamipress_notifications_get_page_url() {
    $page_id = get_option( 'gamipress_notifications_page_id' );

    if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
        return get_permalink( $page_id );
    }

    // Fallback to home page with shortcode
    return home_url( '/?gamipress_notifications=1' );
}

/**
 * Add admin bar menu with notifications
 *
 * @since 1.6.0
 */
function gamipress_notifications_admin_bar_menu() {

    if ( ! is_user_logged_in() ) {
        return;
    }

    global $wp_admin_bar;

    $user_id = get_current_user_id();
    $unread_count = gamipress_notifications_get_unread_count( $user_id );

    $menu_title = gamipress_notifications_get_option( 'admin_bar_menu_label', __( 'Notifications', 'gamipress-notifications' ) );

    $menu_title .= ' <span class="gamipress-notifications-count">' . $unread_count . '</span>';

    $wp_admin_bar->add_menu( array(
        'id'    => 'gamipress-notifications',
        'title' => '<span class="ab-icon dashicons-bell"></span>' . $menu_title,
        'href'  => gamipress_notifications_get_page_url(),
    ) );

   


}
add_action( 'admin_bar_menu', 'gamipress_notifications_admin_bar_menu', 1000 );