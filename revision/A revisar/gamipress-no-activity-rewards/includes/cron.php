<?php

/**
 * Dynamic Cron logic for No Activity Rewards
 *
 * @package GamiPress\GamiPress_No_Activity_Rewards
 * @author  AutomatorWP
 */

if (! defined('ABSPATH')) exit;

/**
 * Register the daily check cron event
 * * @since 1.0.0
 */
function gamipress_no_activity_rewards_register_cron()
{
    if (! wp_next_scheduled('gamipress_no_activity_rewards_daily_check')) {
        wp_schedule_event(time(), 'daily', 'gamipress_no_activity_rewards_daily_check');
    }
}

/**
 * Optimization: Only register the cron check while in admin to save frontend resources
 */
if (is_admin()) {
    add_action('admin_init', 'gamipress_no_activity_rewards_register_cron');
}

/**
 * Main inactivity checker - Scans for dynamic requirements in achievements and ranks
 * * @since 1.0.0
 */
function gamipress_no_activity_rewards_check_inactivity()
{
    global $wpdb;

    /**
     * Step 1: Query for all active achievements or ranks that use our custom requirements.
     * We use a LIKE search on serialized meta data to find relevant requirements.
     */
    $query_requirements = "
        SELECT post_id, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_gamipress_requirements' 
        AND meta_value LIKE '%gamipress_no_activity_rewards_%'";

    $results = $wpdb->get_results($query_requirements);

    if (empty($results)) {
        return;
    }

    /**
     * Step 2: Prevent redundant database queries.
     * We store processed combinations of "Type + Days + SubType" to run the SQL once per group.
     */
    $processed_thresholds = array();

    foreach ($results as $row) {
        $requirements = maybe_unserialize($row->meta_value);

        if (! is_array($requirements)) {
            continue;
        }

        foreach ($requirements as $req) {
            // Filter only requirements belonging to this add-on
            if (strpos($req['type'], 'gamipress_no_activity_rewards_') === false) {
                continue;
            }

            $days = isset($req['days']) ? absint($req['days']) : 7;
            $type = $req['type'];

            // Extract subtypes if applicable (Achievement types, Points types, etc.)
            $sub_type = '';
            if (isset($req['achievement_type'])) $sub_type = $req['achievement_type'];
            if (isset($req['points_type'])) $sub_type = $req['points_type'];
            if (isset($req['rank_type'])) $sub_type = $req['rank_type'];

            // Generate a unique key for this configuration
            $check_key = $type . '_' . $days . '_' . $sub_type;

            if (isset($processed_thresholds[$check_key])) {
                continue;
            }

            // Execute the specific database check for this inactivity type
            gamipress_no_activity_rewards_process_specific_inactivity($type, $days, $sub_type);

            // Mark as processed for the current cron execution
            $processed_thresholds[$check_key] = true;
        }
    }
}
add_action('gamipress_no_activity_rewards_daily_check', 'gamipress_no_activity_rewards_check_inactivity');

/**
 * Helper function to handle SQL queries based on the inactivity type
 * * @param string $type     Requirement type identifier
 * @param int    $days     Number of days configured
 * @param string $sub_type Specific category (post_type or points_slug)
 * * @since 1.0.0
 */
function gamipress_no_activity_rewards_process_specific_inactivity($type, $days, $sub_type = '')
{
    global $wpdb;

    // Calculate time thresholds
    $seconds_limit       = $days * DAY_IN_SECONDS;
    $threshold_date      = date('Y-m-d H:i:s', current_time('timestamp') - $seconds_limit);
    $threshold_timestamp = current_time('timestamp') - $seconds_limit;

    $users_to_trigger = array();

    switch ($type) {

        case 'gamipress_no_activity_rewards_user_has_not_logged_in':
            /**
             * Users whose 'last_login' meta is older than threshold or non-existent.
             * We use LEFT JOIN to include users who never logged in.
             */
            $users_to_trigger = $wpdb->get_col($wpdb->prepare("
                SELECT u.ID FROM {$wpdb->users} u
                LEFT JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id AND um.meta_key = 'last_login')
                WHERE (um.meta_value IS NULL OR CAST(um.meta_value AS UNSIGNED) <= %d)
                LIMIT 500", $threshold_timestamp));
            break;

        case 'gamipress_no_activity_rewards_user_has_not_earned_any_achievement':
            /**
             * Users NOT present in the GamiPress logs table with an achievement award within X days.
             */
            $users_to_trigger = $wpdb->get_col($wpdb->prepare("
                SELECT ID FROM {$wpdb->users} 
                WHERE ID NOT IN (
                    SELECT user_id FROM {$wpdb->prefix}gamipress_logs 
                    WHERE type = 'achievement_award' 
                    AND date_delivered > %s
                ) LIMIT 500", $threshold_date));
            break;

        case 'gamipress_no_activity_rewards_user_has_not_earned_points':
            /**
             * Users NOT present in the GamiPress logs table with points earned within X days.
             */
            $users_to_trigger = $wpdb->get_col($wpdb->prepare("
                SELECT ID FROM {$wpdb->users} 
                WHERE ID NOT IN (
                    SELECT user_id FROM {$wpdb->prefix}gamipress_logs 
                    WHERE type = 'points_award' 
                    AND date_delivered > %s
                ) LIMIT 500", $threshold_date));
            break;

        case 'gamipress_no_activity_rewards_user_has_not_earned_achievement_type':
            /**
             * Advanced query: Checks if the user lacks achievements of a specific type (post_type).
             */
            $users_to_trigger = $wpdb->get_col($wpdb->prepare("
                SELECT ID FROM {$wpdb->users} 
                WHERE ID NOT IN (
                    SELECT user_id FROM {$wpdb->prefix}gamipress_logs 
                    WHERE type = 'achievement_award' 
                    AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)
                    AND date_delivered > %s
                ) LIMIT 500", $sub_type, $threshold_date));
            break;

            // Additional cases for Ranks or specific Point Types can be added here
    }

    // If matches are found, fire the action hooks
    if (! empty($users_to_trigger)) {
        foreach ($users_to_trigger as $user_id) {
            /**
             * Fire the action that GamiPress listens to.
             * 1st Arg: User ID
             * 2nd Arg: Configured days
             * 3rd Arg: Category subtype (if any)
             */
            do_action($type, (int) $user_id, $days, $sub_type);
        }
    }
}
