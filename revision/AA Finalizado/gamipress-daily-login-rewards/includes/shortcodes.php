<?php
/**
 * Shortcodes
 *
 * @package GamiPress\Daily_Login_Rewards\Shortcodes
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GamiPress Daily Login Rewards Shortcodes
require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/shortcodes/gamipress_rewards_calendar.php';
require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/shortcodes/gamipress_rewards_current_day.php';

/**
 * Adds the "show_calendar_rewards" parameter to [gamipress_earnings]
 *
 * @since 1.1.2
 *
 * @param array $fields
 *
 * @return mixed
 */
function gamipress_daily_login_rewards_earnings_shortcode_fields( $fields ) {

    $fields['show_calendar_rewards'] = array(
        'name' => __('Show Calendar Rewards', 'gamipress-daily-login-rewards'),
        'description' => __('Show calendar rewards earned.', 'gamipress-daily-login-rewards'),
        'type' => 'checkbox',
        'classes' => 'gamipress-switch',
    );

    return $fields;

}
add_filter( 'gamipress_gamipress_earnings_shortcode_fields', 'gamipress_daily_login_rewards_earnings_shortcode_fields' );

/**
 * Adds the "show_calendar_rewards" field to the general tab on [gamipress_earnings]
 *
 * @since 1.1.2
 *
 * @param array $tabs
 *
 * @return mixed
 */
function gamipress_daily_login_rewards_earnings_shortcode_tabs( $tabs ) {

    $tabs['general']['fields'][] = 'show_calendar_rewards';

    return $tabs;

}
add_filter( 'gamipress_gamipress_earnings_shortcode_tabs', 'gamipress_daily_login_rewards_earnings_shortcode_tabs' );

/**
 * Adds the "show_calendar_rewards" parameter to [gamipress_earnings] defaults
 *
 * @since 1.1.2
 *
 * @param array $defaults
 *
 * @return array
 */
function gamipress_daily_login_rewards_earnings_shortcode_defaults( $defaults ) {

    $defaults['show_calendar_rewards'] = 'no';

    return $defaults;

}
add_filter( 'gamipress_earnings_shortcode_defaults', 'gamipress_daily_login_rewards_earnings_shortcode_defaults' );

/**
 * Applies the "show_calendar_rewards" parameter to [gamipress_earnings] query
 *
 * @since 1.1.2
 *
 * @param array $query_args Query args to be passed to CT_Query
 * @param array $args       Function received args
 *
 * @return array
 */
function gamipress_daily_login_rewards_earnings_shortcode_query_args( $query_args, $args ) {

    if( $args['show_calendar_rewards'] === 'yes' ) {
        $query_args['post_type'][] = 'calendar-reward';
        $query_args['post_type'][] = 'rewards-calendar';
    }

    return $query_args;

}
add_filter( 'gamipress_earnings_shortcode_query_args', 'gamipress_daily_login_rewards_earnings_shortcode_query_args', 10, 2 );