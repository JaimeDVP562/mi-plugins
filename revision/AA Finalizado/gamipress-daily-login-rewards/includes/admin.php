<?php
/**
 * Admin
 *
 * @package GamiPress\Daily_Login_Rewards\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/admin/rewards-ui.php';
require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/admin/calendar-generator.php';

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
function gamipress_daily_login_rewards_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_daily_login_rewards_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * Adds the admin menu
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_admin_menu() {
    $minimum_role = gamipress_get_manager_capability();
    add_submenu_page( 'gamipress', __( 'Rewards Calendars', 'gamipress-daily-login-rewards' ), __( 'Rewards Calendars', 'gamipress-daily-login-rewards' ), $minimum_role, 'edit.php?post_type=rewards-calendar', false );
}
add_action( 'admin_menu', 'gamipress_daily_login_rewards_admin_menu', 20 );

/**
 * Add GamiPress Daily Login Rewards admin bar menu
 *
 * @since 1.0.2
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function gamipress_daily_login_rewards_admin_bar_menu( $wp_admin_bar ) {

    // - Rewards Calendars
    $wp_admin_bar->add_node( array(
        'id'     => 'gamipress-rewards-calendar',
        'title'  => __( 'Rewards Calendars', 'gamipress-daily-login-rewards' ),
        'parent' => 'gamipress-add-ons',
        'href'   => admin_url( 'edit.php?post_type=rewards-calendar' )
    ) );

}
add_action( 'admin_bar_menu', 'gamipress_daily_login_rewards_admin_bar_menu', 999 );

/**
 * GamiPress Daily Login Rewards Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_daily_login_rewards_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_daily_login_rewards_';

    $meta_boxes['gamipress-daily-login-rewards-settings'] = array(
        'title' => gamipress_dashicon( 'calendar-alt' ) . __( 'Daily Login Rewards', 'gamipress-daily-login-rewards' ),
        'fields' => apply_filters( 'gamipress_daily_login_rewards_settings_fields', array(

            // General

            $prefix . 'allow_multiple_login' => array(
                'name' => __( 'Allow Multiple Login', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Check this option if you want to allow users get rewarded for log in multiple times in same day. You can enable this option for testing purposes if you want to check how users are getting awarded without need to wait to the next day.', 'gamipress-daily-login-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'stamp' => array(
                'name' => __( 'Stamp', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Upload, choose or paste the URL of the stamp to be displayed on earned calendar rewards (leave blank if you don\'t want to add it).', 'gamipress-daily-login-rewards' ),
                'type' => 'file',
            ),
            $prefix . 'align_stamp_with_image' => array(
                'name' => __( 'Align stamp with reward\'s image', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'By default, the stamp is aligned with the reward element (text and image), check this option if you want to align the stamp effect with the reward\'s image instead.', 'gamipress-daily-login-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),

            // Pop-up

            $prefix . 'popup_button_text' => array(
                'name' => __( 'Pop-up Button Text', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Set the button text shown at bottom to hide the pop-up.', 'gamipress-daily-login-rewards' ),
                'type' => 'text',
                'default' => __( 'Ok!', 'gamipress-daily-login-rewards' ),
            ),
            $prefix . 'popup_show_title' => array(
                'name' => __( 'Show Rewards Calendar Title', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Check this option to display the rewards calendar title on pop-ups.', 'gamipress-daily-login-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch'
            ),
            $prefix . 'popup_show_excerpt' => array(
                'name' => __( 'Show Rewards Calendar Excerpt', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Check this option to display the rewards calendar short description on pop-ups.', 'gamipress-daily-login-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch'
            ),

            // Post Type

            $prefix . 'post_type_title' => array(
                'name' => __( 'Rewards Calendar Post Type', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'From this settings you can modify the default configuration of the rewards calendar post type.', 'gamipress-daily-login-rewards' ),
                'type' => 'title',
            ),
            $prefix . 'slug' => array(
                'name' => __( 'Slug', 'gamipress-daily-login-rewards' ),
                'desc' => '<span class="gamipress-daily-login-rewards-full-slug hide-if-no-js">' . site_url() . '/<strong class="gamipress-daily-login-rewards-slug"></strong>/</span>',
                'type' => 'text',
                'default' => 'rewards-calendars',
            ),
            $prefix . 'public' => array(
                'name' => __( 'Public', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Check this option if you want to allow to your visitors access to a rewards calendar as a page. Not checking this option will make rewards calendar just visible through shortcodes or widgets.', 'gamipress-daily-login-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'supports' => array(
                'name' => __( 'Supports', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Check the features you want to add to the rewards calendar post type.', 'gamipress-daily-login-rewards' ),
                'type' => 'multicheck',
                'classes' => 'gamipress-switch',
                'options' => array(
                    'title'             => __( 'Title' ),
                    'editor'            => __( 'Editor' ),
                    'author'            => __( 'Author' ),
                    'thumbnail'         => __( 'Thumbnail' ) . ' (' . __( 'Featured Image' ) . ')',
                    'excerpt'           => __( 'Excerpt' ),
                    'trackbacks'        => __( 'Trackbacks' ),
                    'custom-fields'     => __( 'Custom Fields' ),
                    'comments'          => __( 'Comments' ),
                    'revisions'         => __( 'Revisions' ),
                    'page-attributes'   => __( 'Page Attributes' ),
                    'post-formats'      => __( 'Post Formats' ),
                ),
                'default' => array( 'title', 'editor', 'excerpt' )
            ),
        ) ),
        'tabs' => apply_filters( 'gamipress_daily_login_rewards_settings_tabs', array(
            'general' => array(
                'icon' => 'dashicons-admin-generic',
                'title' => __( 'General', 'gamipress-daily-login-rewards' ),
                'fields' => array(
                    $prefix . 'allow_multiple_login',
                    $prefix . 'stamp',
                    $prefix . 'align_stamp_with_image',
                ),
            ),
            'popup' => array(
                'icon' => 'dashicons-slides',
                'title' => __( 'Pop-up', 'gamipress-daily-login-rewards' ),
                'fields' => array(
                    $prefix . 'popup_button_text',
                    $prefix . 'popup_show_title',
                    $prefix . 'popup_show_excerpt',
                ),
            ),
            'post_type' => array(
                'icon' => 'dashicons-admin-post',
                'title' => __( 'Post Type', 'gamipress-daily-login-rewards' ),
                'fields' => array(
                    $prefix . 'post_type_title',
                    $prefix . 'slug',
                    $prefix . 'public',
                    $prefix . 'supports'
                ),
            ),
        ) ),
        'vertical_tabs' => true
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_daily_login_rewards_settings_meta_boxes' );

/**
 * Daily Login Rewards Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_daily_login_rewards_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-daily-login-rewards-license'] = array(
        'title' => __( 'Daily Login Rewards', 'gamipress-daily-login-rewards' ),
        'fields' => array(
            'gamipress_daily_login_rewards_license' => array(
                'name' => __( 'License', 'gamipress-daily-login-rewards' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_DAILY_LOGIN_REWARDS_FILE,
                'item_name' => 'Daily Login Rewards',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_daily_login_rewards_licenses_meta_boxes' );

/**
 * Register custom meta boxes
 *
 * @since  1.0.0
 */
function gamipress_daily_login_rewards_meta_boxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    // Rewards Calendar Completion Requirements
    gamipress_add_meta_box(
        'rewards-calendar-completion-requirements',
        __( 'Rewards Calendar Completion Requirements', 'gamipress-daily-login-rewards' ),
        'rewards-calendar',
        array(
            $prefix . 'consecutive' => array(
                'name' 	         => __( 'Consecutively', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'User needs to log in consecutively to earn rewards.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'checkbox',
                'classes' 	     => 'gamipress-switch',
            ),
            $prefix . 'consecutive_penalty' => array(
                'name' 	         => __( 'Penalty', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Select the penalty for not log in consecutively.', 'gamipress-daily-login-rewards' )
                . '<br>' . __( '<strong>Restart progress:</strong> User will return back to day 1. Already earned rewards will not be awarded again.', 'gamipress-daily-login-rewards' )
                . '<br>' . __( '<strong>Restart progress and revoke rewards:</strong> User will return back to day 1 and all earned rewards will be revoked.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'select',
                'options' 	     => array(
                    'restart'           => __( 'Restart progress', 'gamipress-daily-login-rewards' ),
                    'revoke_restart'    => __( 'Restart progress and revoke rewards', 'gamipress-daily-login-rewards' ),
                ),
            ),
            $prefix . 'date_limit' => array(
                'name' 	         => __( 'Limited time', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Make this calendar available by a limited time.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'checkbox',
                'classes' 	     => 'gamipress-switch',
            ),
            $prefix . 'start_date' => array(
                'name' 	         => __( 'Start date', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Enter the start date (leave blank to no limit by a start date). If entered, the rewards will be awarded after or on this date.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'text_date_timestamp',
            ),
            $prefix . 'end_date' => array(
                'name' 	         => __( 'End date', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Enter the end date (leave blank to no limit by an end date). If entered, the rewards will be awarded before or on this date.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'text_date_timestamp',
            ),
            $prefix . 'repeatable' => array(
                'name' 	         => __( 'Repeatable', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Make this calendar repeatable to let users earn the rewards limited or unlimited times.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'checkbox',
                'classes' 	     => 'gamipress-switch',
            ),
            $prefix . 'repeatable_times' => array(
                'name'          => __( 'Maximum Times', 'gamipress-daily-login-rewards' ),
                'desc'          => __( 'Number of times a user can repeat this calendar (set it to 0 for no maximum).', 'gamipress-daily-login-rewards' ),
                'type'          => 'text_small',
                'attributes'    => array(
                    'type' => 'number'
                ),
                'default'       => '0',
            ),
            $prefix . 'complete_rewards_calendars' => array(
                'name' 	         => __( 'Complete other calendars', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'User needs to complete other calendars to being awarded.', 'gamipress-daily-login-rewards' ),
                'type' 	         => 'checkbox',
                'classes' 	     => 'gamipress-switch',
            ),
            $prefix . 'required_rewards_calendars' => array(
                'name' 	         => __( 'Calendars', 'gamipress-daily-login-rewards' ),
                'desc' 	         => __( 'Choose the calendar(s) that user needs to complete.', 'gamipress-daily-login-rewards' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'options_cb'    => 'gamipress_options_cb_posts'
            ),
        ),
        array( 'priority' => 'high', )
    );

    // Rewards Calendar Display Options
    gamipress_add_meta_box(
        'rewards-calendar-display-options',
        __( 'Rewards Calendar Display Options', 'gamipress-daily-login-rewards' ),
        'rewards-calendar',
        array(
            $prefix . 'popup_on_reward' => array(
                'name' 	        => __( 'Show a pop-up on reward', 'gamipress-daily-login-rewards' ),
                'desc' 	        => __( 'When user gets rewarded, show a pop-up to notify it.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'checkbox',
                'classes' 	    => 'gamipress-switch',
            ),
            $prefix . 'columns' => array(
                'name' 	        => __( 'Columns', 'gamipress-daily-login-rewards' ),
                'desc' 	        => __( 'Columns to divide calendar rewards.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'select',
                'options' 	    => array(
                    '1' => __( '1 Column', 'gamipress-daily-login-rewards' ),
                    '2' => __( '2 Columns', 'gamipress-daily-login-rewards' ),
                    '3' => __( '3 Columns', 'gamipress-daily-login-rewards' ),
                    '4' => __( '4 Columns', 'gamipress-daily-login-rewards' ),
                    '5' => __( '5 Columns', 'gamipress-daily-login-rewards' ),
                    '6' => __( '6 Columns', 'gamipress-daily-login-rewards' ),
                    '7' => __( '7 Columns', 'gamipress-daily-login-rewards' ),
                ),
            ),
            $prefix . 'image_size' => array(
                'name' 	        => __( 'Image Size', 'gamipress-daily-login-rewards' ),
                'desc' 	        => __( 'Size of the rewards images.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'text',
                'attributes' 	=> array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
                'default'    => 100,
            ),
        ),
        array( 'priority' => 'high', )
    );

    // Rewards Calendar Shortcode
    gamipress_add_meta_box(
        'rewards-calendar-shortcode',
        __( 'Rewards Calendar Shortcode', 'gamipress-daily-login-rewards' ),
        'rewards-calendar',
        array(
            $prefix . 'shortcode' => array(
                'desc' 	        => __( 'Place this shortcode anywhere to display this rewards calendar.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'text',
                'attributes'    => array(
                    'readonly'  => 'readonly',
                    'onclick'   => 'this.focus(); this.select();'
                ),
                'default_cb'    => 'gamipress_daily_login_rewards_shortcode_field_default_cb'
            ),
        ),
        array(
            'context'  => 'side',
            'priority' => 'default'
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_daily_login_rewards_meta_boxes' );

/**
 * Daily Login Rewards automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_daily_login_rewards_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-daily-login-rewards'] = __( 'Daily Login Rewards', 'gamipress-daily-login-rewards' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_daily_login_rewards_automatic_updates' );

// Shortcode field default cb
function gamipress_daily_login_rewards_shortcode_field_default_cb( $field_args, $field ) {
    return '[gamipress_rewards_calendar id="' . $field->object_id . '"]';
}