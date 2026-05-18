<?php
/**
 * Post Types
 *
 * @package     GamiPress\Daily_Login_Rewards\Post_Types
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Rewards Calendar CPT
 *
 * @since  1.0.0
 */
function gamipress_daily_login_rewards_register_post_types() {

    $rewards_calendar_labels = gamipress_daily_login_rewards_rewards_calendar_labels();

    $public_rewards_calendar = (bool) gamipress_daily_login_rewards_get_option( 'public', false );
    $supports = gamipress_daily_login_rewards_get_option( 'supports', array( 'title', 'editor', 'excerpt' ) );

    if( ! is_array( $supports ) ) {
        $supports =  array( 'title', 'editor', 'excerpt' );
    }

    // Register Rewards Calendar
    register_post_type( 'rewards-calendar', array(
        'labels'             => array(
            'name'               => $rewards_calendar_labels['plural'],
            'singular_name'      => $rewards_calendar_labels['singular'],
            'add_new'            => __( 'Add New', 'gamipress-daily-login-rewards' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'gamipress-daily-login-rewards' ), $rewards_calendar_labels['singular'] ),
            'edit_item'          => sprintf( __( 'Edit %s', 'gamipress-daily-login-rewards' ), $rewards_calendar_labels['singular'] ),
            'new_item'           => sprintf( __( 'New %s', 'gamipress-daily-login-rewards' ), $rewards_calendar_labels['singular'] ),
            'all_items'          => $rewards_calendar_labels['plural'],
            'view_item'          => sprintf( __( 'View %s', 'gamipress-daily-login-rewards' ), $rewards_calendar_labels['singular'] ),
            'search_items'       => sprintf( __( 'Search %s', 'gamipress-daily-login-rewards' ), $rewards_calendar_labels['plural'] ),
            'not_found'          => sprintf( __( 'No %s found', 'gamipress-daily-login-rewards' ), strtolower( $rewards_calendar_labels['plural'] ) ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'gamipress-daily-login-rewards' ), strtolower( $rewards_calendar_labels['plural'] ) ),
            'parent_item_colon'  => '',
            'menu_name'          => $rewards_calendar_labels['plural'],
        ),
        'public'             => $public_rewards_calendar,
        'publicly_queryable' => $public_rewards_calendar,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => gamipress_daily_login_rewards_get_option( 'slug', 'rewards-calendars' ) ),
        'capability_type'    => 'page',
        'has_archive'        => $public_rewards_calendar,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => $supports
    ) );

    // Register Calendar Rewards
    $calendar_reward_labels = gamipress_daily_login_rewards_calendar_reward_labels();

    $public_calendar_rewards = apply_filters( 'gamipress_daily_login_rewards_public_calendar_rewards', false );

    register_post_type( 'calendar-reward', array(
        'labels'             => array(
            'name'               => $calendar_reward_labels['plural'],
            'singular_name'      => $calendar_reward_labels['singular'],
            'add_new'            => __( 'Add New', 'gamipress-daily-login-rewards' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'gamipress-daily-login-rewards' ), $calendar_reward_labels['singular'] ),
            'edit_item'          => sprintf( __( 'Edit %s', 'gamipress-daily-login-rewards' ), $calendar_reward_labels['singular'] ),
            'new_item'           => sprintf( __( 'New %s', 'gamipress-daily-login-rewards' ), $calendar_reward_labels['singular'] ),
            'all_items'          => $calendar_reward_labels['plural'],
            'view_item'          => sprintf( __( 'View %s', 'gamipress-daily-login-rewards' ), $calendar_reward_labels['singular'] ),
            'search_items'       => sprintf( __( 'Search %s', 'gamipress-daily-login-rewards' ), $calendar_reward_labels['plural'] ),
            'not_found'          => sprintf( __( 'No %s found', 'gamipress-daily-login-rewards' ), strtolower( $calendar_reward_labels['plural'] ) ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'gamipress-daily-login-rewards' ), strtolower( $calendar_reward_labels['plural'] ) ),
            'parent_item_colon'  => '',
            'menu_name'          => $calendar_reward_labels['plural'],
        ),
        'public'             => $public_calendar_rewards,
        'publicly_queryable' => $public_calendar_rewards,
        'show_ui'            => current_user_can( gamipress_get_manager_capability() ),
        'show_in_menu'       => ( ( $public_calendar_rewards || gamipress_is_debug_mode() ) ? 'gamipress' : false ),
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => $public_calendar_rewards,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'thumbnail' ),

    ) );

}
add_action( 'init', 'gamipress_daily_login_rewards_register_post_types', 11 );

/**
 * Rewards calendar labels
 *
 * @since  1.0.0
 * @return array
 */
function gamipress_daily_login_rewards_rewards_calendar_labels() {

    return apply_filters( 'gamipress_daily_login_rewards_rewards_calendar_labels' , array(
        'singular' => __( 'Rewards Calendar', 'gamipress-daily-login-rewards' ),
        'plural' => __( 'Rewards Calendars', 'gamipress-daily-login-rewards' )
    ));

}

/**
 * Calendar reward labels
 *
 * @since  1.0.7
 * @return array
 */
function gamipress_daily_login_rewards_calendar_reward_labels() {

    return apply_filters( 'gamipress_daily_login_rewards_rewards_calendar_labels' , array(
        'singular' => __( 'Reward', 'gamipress-daily-login-rewards' ),
        'plural' => __( 'Rewards', 'gamipress-daily-login-rewards' )
    ));

}