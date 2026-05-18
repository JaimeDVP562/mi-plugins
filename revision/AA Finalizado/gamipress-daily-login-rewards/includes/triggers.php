<?php
/**
 * Triggers
 *
 * @package     GamiPress\Daily_Login_Rewards\Triggers
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin activity triggers
 *
 * @since 1.0.0
 *
 * @param array $activity_triggers
 *
 * @return mixed
 */
function gamipress_daily_login_rewards_activity_triggers( $activity_triggers ) {

    $activity_triggers[__( 'Daily Login Rewards', 'gamipress-daily-login-rewards' )] = array(

        // Calendar rewards
        'gamipress_daily_login_rewards_earn_reward' 		                => __( 'Earn any reward', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_specific_earn_reward'                => __( 'Earn any reward of a specific calendar', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_earn_points_reward'	                => __( 'Earn any points reward', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_specific_earn_points_reward'	        => __( 'Earn any points reward of a specific calendar', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_earn_achievement_reward'	            => __( 'Earn any achievement reward', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_specific_earn_achievement_reward'	=> __( 'Earn any achievement reward of a specific calendar', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_earn_rank_reward'	                => __( 'Earn any rank reward', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_specific_earn_rank_reward'	        => __( 'Earn any rank reward of a specific calendar', 'gamipress-daily-login-rewards' ),

        // Rewards calendars
        'gamipress_daily_login_rewards_earn_calendar'	                    => __( 'Earn all rewards of any calendar', 'gamipress-daily-login-rewards' ),
        'gamipress_daily_login_rewards_specific_earn_calendar'	            => __( 'Earn all rewards of a specific calendar', 'gamipress-daily-login-rewards' ),
    );

    return $activity_triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_daily_login_rewards_activity_triggers' );

/**
 * Register specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_daily_login_rewards_specific_activity_triggers( $specific_activity_triggers ) {

    // Calendar rewards
    $specific_activity_triggers['gamipress_daily_login_rewards_specific_earn_reward'] = array( 'rewards-calendar' );
    $specific_activity_triggers['gamipress_daily_login_rewards_specific_earn_points_reward'] = array( 'rewards-calendar' );
    $specific_activity_triggers['gamipress_daily_login_rewards_specific_earn_achievement_reward'] = array( 'rewards-calendar' );
    $specific_activity_triggers['gamipress_daily_login_rewards_specific_earn_rank_reward'] = array( 'rewards-calendar' );
    // Rewards calendars
    $specific_activity_triggers['gamipress_daily_login_rewards_specific_earn_calendar'] = array( 'rewards-calendar' );

    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_daily_login_rewards_specific_activity_triggers' );

/**
 * Register specific activity triggers labels
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_daily_login_rewards_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    // Calendar rewards
    $specific_activity_trigger_labels['gamipress_daily_login_rewards_specific_earn_reward'] = __( 'Earn any reward of %s', 'gamipress-daily-login-rewards' );
    $specific_activity_trigger_labels['gamipress_daily_login_rewards_specific_earn_points_reward'] = __( 'Earn any points reward of %s', 'gamipress-daily-login-rewards' );
    $specific_activity_trigger_labels['gamipress_daily_login_rewards_specific_earn_achievement_reward'] = __( 'Earn any achievement reward of %s', 'gamipress-daily-login-rewards' );
    $specific_activity_trigger_labels['gamipress_daily_login_rewards_specific_earn_rank_reward'] = __( 'Earn any rank reward of %s', 'gamipress-daily-login-rewards' );
    // Rewards calendars
    $specific_activity_trigger_labels['gamipress_daily_login_rewards_specific_earn_calendar'] = __( 'Earn all rewards of %s', 'gamipress-daily-login-rewards' );

    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_daily_login_rewards_specific_activity_trigger_label' );

/**
 * Get user for a given trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          User ID.
 */
function gamipress_daily_login_rewards_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        // Calendar rewards
        case 'gamipress_daily_login_rewards_earn_reward':
        case 'gamipress_daily_login_rewards_specific_earn_reward':
        case 'gamipress_daily_login_rewards_earn_points_reward':
        case 'gamipress_daily_login_rewards_specific_earn_points_reward':
        case 'gamipress_daily_login_rewards_earn_achievement_reward':
        case 'gamipress_daily_login_rewards_specific_earn_achievement_reward':
        case 'gamipress_daily_login_rewards_earn_rank_reward':
        case 'gamipress_daily_login_rewards_specific_earn_rank_reward':
        // Rewards calendars
        case 'gamipress_daily_login_rewards_earn_calendar':
        case 'gamipress_daily_login_rewards_specific_earn_calendar':
            $user_id = $args[0];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_daily_login_rewards_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a given specific trigger action.
 *
 * @since  1.0.0
 *
 * @param integer $specific_id  Specific ID.
 * @param string  $trigger      Trigger name.
 * @param array   $args         Passed trigger args.
 *
 * @return integer          Specific ID.
 */
function gamipress_daily_login_rewards_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        // Calendar rewards
        case 'gamipress_daily_login_rewards_specific_earn_reward':
        case 'gamipress_daily_login_rewards_specific_earn_points_reward':
        case 'gamipress_daily_login_rewards_specific_earn_achievement_reward':
        case 'gamipress_daily_login_rewards_specific_earn_rank_reward':
            $specific_id = $args[2];
            break;
            // Rewards calendars
        case 'gamipress_daily_login_rewards_earn_calendar':
        case 'gamipress_daily_login_rewards_specific_earn_calendar':
            $specific_id = $args[1];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_daily_login_rewards_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.0
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_daily_login_rewards_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {
        // Calendar rewards
        case 'gamipress_daily_login_rewards_earn_reward':
        case 'gamipress_daily_login_rewards_specific_earn_reward':
        case 'gamipress_daily_login_rewards_earn_points_reward':
        case 'gamipress_daily_login_rewards_specific_earn_points_reward':
        case 'gamipress_daily_login_rewards_earn_achievement_reward':
        case 'gamipress_daily_login_rewards_specific_earn_achievement_reward':
        case 'gamipress_daily_login_rewards_earn_rank_reward':
        case 'gamipress_daily_login_rewards_specific_earn_rank_reward':
            // Add the calendar reward ID
            $log_meta['calendar_reward_id'] = $args[1];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_daily_login_rewards_log_event_trigger_meta_data', 10, 5 );