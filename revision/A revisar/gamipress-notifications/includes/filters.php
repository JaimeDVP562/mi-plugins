<?php
/**
 * Filters
 *
 * @package     GamiPress\Notifications\Filters
 * @since       1.4.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Exclude notifications check in AutomatorWP redirect utility
 *
 * @param array $excluded_ajax_actions
 *
 * @return array
 */
function gamipress_notifications_exclude_notifications_check_in_automatorwp( $excluded_ajax_actions ) {

    $excluded_ajax_actions[] = 'gamipress_notifications_get_notices';

    return $excluded_ajax_actions;

}
add_filter( 'automatorwp_redirect_excluded_ajax_actions', 'gamipress_notifications_exclude_notifications_check_in_automatorwp' );

/**
 * Hook on user earning creation to create notification
 *
 * @since 1.6.0
 *
 * @param int       $user_earning_id    The user earning ID
 * @param int       $user_id            The user ID
 * @param string    $trigger_type       The trigger type
 * @param int       $achievement_id     The achievement ID (if is an achievement)
 * @param int       $points             The points amount (if is points)
 * @param string    $points_type        The points type slug (if is points)
 */
function gamipress_notifications_on_user_earning_awarded( $user_earning_id, $user_id, $trigger_type = '', $achievement_id = 0, $points = 0, $points_type = '' ) {
    
    
    gamipress_notifications_insert_notification( $user_id, $user_earning_id );

}

add_action( 'gamipress_insert_user_earning', 'gamipress_notifications_on_user_earning_awarded', 10, 2 );