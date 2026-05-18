<?php
/**
 * Listeners
 *
 * @package     GamiPress\Daily_Login_Rewards\Listeners
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Listener for user log in
 *
 * @since  1.0.0
 *
 * @param string    $user_login   Username.
 * @param WP_User   $user         WP_User object of the logged-in user.
 */
function gamipress_daily_login_rewards_login_listener( $user_login, $user ) {

    gamipress_daily_login_rewards_check_rewards_for_user( $user->ID, date( 'Y-m-d', current_time( 'timestamp' ) ) );

}
add_action( 'wp_login', 'gamipress_daily_login_rewards_login_listener', 1, 2 );

/**
 * Listener for user visit
 *
 * @since  1.0.0
 */
function gamipress_daily_login_rewards_visit_listener() {

    $user_id = get_current_user_id();

    if( $user_id !== 0 ) {

        $allow_multiple_login = (bool) gamipress_daily_login_rewards_get_option( 'allow_multiple_login', false );

        // only trigger the visit listener if multiple login is disabled
        if( ! $allow_multiple_login ) {
            gamipress_daily_login_rewards_check_rewards_for_user( $user_id, date( 'Y-m-d', current_time( 'timestamp' ) ) );
        }
    }

}
add_action( 'init', 'gamipress_daily_login_rewards_visit_listener', 10 );

/**
 * Listener for user reward earned
 *
 * @since  1.0.0
 *
 * @param int    $user_id       The user ID.
 * @param int    $reward_id     The reward ID.
 * @param int    $calendar_id   The calendar ID.
 */
function gamipress_daily_login_rewards_on_earn_reward( $user_id, $reward_id, $calendar_id ) {

    $prefix = '_gamipress_daily_login_rewards_';

    // Check if calendar reward is configured to be shown as popup at front end
    $popup_on_reward = gamipress_get_post_meta( $calendar_id, $prefix . 'popup_on_reward' );

    if( $popup_on_reward ) {

        // Get the rewards calendars that should be shown as popup to the user
        $calendars_to_show = gamipress_get_user_meta( $user_id, $prefix . 'rewards_calendars_to_show' );

        if( ! $calendars_to_show )
            $calendars_to_show = array();

        // If this rewards calendar hasn't been added, then add it
        if( ! in_array( $calendar_id, $calendars_to_show ) ) {
            $calendars_to_show[] = $calendar_id;

            gamipress_update_user_meta( $user_id, $prefix . 'rewards_calendars_to_show', $calendars_to_show );
        }

    }

}
add_action( 'gamipress_daily_login_rewards_earn_reward', 'gamipress_daily_login_rewards_on_earn_reward', 10, 3 );