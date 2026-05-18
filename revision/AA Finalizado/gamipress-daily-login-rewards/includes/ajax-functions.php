<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\Daily_Login_Rewards\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX Helper for selecting posts in Shortcode Embedder
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_ajax_get_rewards_calendars_options() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? like_escape( $_REQUEST['q'] ) : '';

    $results = $wpdb->get_results( $wpdb->prepare(
        "
		SELECT p.ID, p.post_title
		FROM   $wpdb->posts AS p
		WHERE  p.post_title LIKE %s
		       AND p.post_type = 'rewards-calendar'
		       AND p.post_status = 'publish'
		",
        "%%{$search}%%"
    ) );

    // Return our results
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_gamipress_daily_login_rewards_get_rewards_calendars_options', 'gamipress_daily_login_rewards_ajax_get_rewards_calendars_options' );

/**
 * Mark an rewards calendar as shown by the user
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_ajax_calendar_shown() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_daily_login_rewards', 'nonce' );

    $rewards_calendar_id = absint( $_REQUEST['rewards_calendar_id'] );

    $prefix = '_gamipress_daily_login_rewards_';
    $user_id = get_current_user_id();

    $calendars_to_show = gamipress_get_user_meta( $user_id, $prefix . 'rewards_calendars_to_show' );

    // Bail if user hasn't calendars to be shown
    if( ! is_array( $calendars_to_show ) )
        wp_send_json_error( __( 'No calendars to show.', 'gamipress-daily-login-rewards' ) );

    $index = array_search( $rewards_calendar_id, $calendars_to_show );

    unset( $calendars_to_show[$index] );

    gamipress_update_user_meta( $user_id, $prefix . 'rewards_calendars_to_show', $calendars_to_show );

    wp_send_json_success( __( 'Calendar removed successfully!', 'gamipress-daily-login-rewards' ) );

}
add_action( 'wp_ajax_gamipress_daily_login_rewards_calendar_shown', 'gamipress_daily_login_rewards_ajax_calendar_shown' );