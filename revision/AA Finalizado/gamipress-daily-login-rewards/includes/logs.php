<?php
/**
 * Logs
 *
 * @package     GamiPress\Daily_Login_Rewards\Logs
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin log types
 *
 * @since 1.0.0
 *
 * @param array $gamipress_log_types
 *
 * @return array
 */
function gamipress_daily_login_rewards_logs_types( $gamipress_log_types ) {

    $gamipress_log_types['calendar_revoke'] = __( 'Calendar Revoke', 'gamipress-daily-login-rewards' );
    $gamipress_log_types['calendar_restart'] = __( 'Calendar Restart', 'gamipress-daily-login-rewards' );

    return $gamipress_log_types;

}
add_filter( 'gamipress_logs_types', 'gamipress_daily_login_rewards_logs_types' );

/**
 * Log calendar revoke on logs
 *
 * @since 1.0.0
 *
 * @param int $post_id
 * @param int $user_id
 *
 * @return int|false
 */
function gamipress_daily_login_rewards_log_calendar_revoke( $post_id = null, $user_id = null, $date = '', $last_login = '' ) {

    // Log meta data
    $log_meta = array(
        'pattern'       => sprintf(
            __( '{user} lost all rewards of "%s" for break a consecutive login (last login %s)', 'gamipress-daily-login-rewards' ),
            get_post_field( 'post_title', $post_id ),
            date_i18n( get_option( 'date_format' ), strtotime( $last_login ) )
        ),
        'post_id'       => $post_id,
        'date'          => $date,
        'last_login'    => $last_login,
    );

    // Register the content unlock on logs
    return gamipress_insert_log( 'calendar_revoke', $user_id, 'private', $log_meta );

}

/**
 * Log calendar restart on logs
 *
 * @since 1.0.0
 *
 * @param int $post_id
 * @param int $user_id
 *
 * @return int|false
 */
function gamipress_daily_login_rewards_log_calendar_restart( $post_id = null, $user_id = null, $date = '', $last_login = '' ) {

    // Log meta data
    $log_meta = array(
        'pattern'       => sprintf(
            __( '{user} has restarted the calendar "%s" for break a consecutive login (last login %s)', 'gamipress-daily-login-rewards' ),
            get_post_field( 'post_title', $post_id ),
            date_i18n( get_option( 'date_format' ), strtotime( $last_login ) )
        ),
        'post_id'       => $post_id,
        'date'          => $date,
        'last_login'    => $last_login,
    );

    // Register the content unlock on logs
    return gamipress_insert_log( 'calendar_restart', $user_id, 'private', $log_meta );

}