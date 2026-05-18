<?php
/**
 * Recurring Rewards
 *
 * @package GamiPress\Recurring_Rewards\Recurring_Rewards
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the custom cron schedules used by recurring rewards.
 *
 * @since 1.0.0
 *
 * @param array $schedules Available cron schedules.
 *
 * @return array Filtered cron schedules.
 */
function gamipress_recurring_rewards_add_cron_schedules( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => __( 'Every Minute', 'gamipress-recurring-rewards' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'gamipress_recurring_rewards_add_cron_schedules' );

/**
 * Schedules the recurring rewards cron event when needed.
 *
 * @since 1.0.0
 */
function gamipress_recurring_rewards_schedule_cron() {

    if ( ! wp_next_scheduled( 'gamipress_recurring_rewards_cron_every_minute' ) ) {
        wp_schedule_event( time(), 'every_minute', 'gamipress_recurring_rewards_cron_every_minute' );
    }

}
add_action( 'wp_loaded', 'gamipress_recurring_rewards_schedule_cron' );

/**
 * Execute award on cron event
 *
 * @since 1.0.0
 */
add_action( 'gamipress_recurring_rewards_cron_every_minute', 'gamipress_recurring_rewards_award_points' );
