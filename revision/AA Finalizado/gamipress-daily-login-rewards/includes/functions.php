<?php
/**
 * Functions
 *
 * @package     GamiPress\Daily_Login_Rewards\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check rewards for a user in a specific date
 *
 * @since  1.1.0
 *
 * @param int    $user_id   The user ID.
 * @param string   $date    The date to check in format Y-m-d.
 */
function gamipress_daily_login_rewards_check_rewards_for_user( $user_id, $date ) {

    $prefix = '_gamipress_daily_login_rewards_';

    /**
     * To override the date when checking for rewards
     *
     * @since 1.0.0
     *
     * @param string    $date   Current date
     * @param integer   $user->ID   The user ID
     */
    $date = apply_filters( 'gamipress_daily_rewards_check_rewards_date', $date, $user_id );

    $yesterday = date( 'Y-m-d', strtotime( '-1 days', strtotime( $date ) ) );
    $last_login = gamipress_get_user_meta( $user_id, $prefix . 'last_login' );
    $allow_multiple_login = (bool) gamipress_daily_login_rewards_get_option( 'allow_multiple_login', false );

    // Bail if user already log in today
    if( $last_login === $date && ! $allow_multiple_login ) {
        return;
    }

    // A possible fail detected in the system since the last login date is greater that the current date
    if( strtotime( $last_login ) > strtotime( $date ) ) {
        return;
    }

    // Get all available calendars
    $calendars = gamipress_daily_login_rewards_get_user_available_calendars( $user_id );

    // Loop all calendar looking for the lowest oder reward
    foreach( $calendars as $calendar_id ) {

        // Get lowest menu order
        $reward_id = gamipress_daily_login_rewards_get_user_next_reward_id( $user_id, $calendar_id );

        if( $reward_id ) {

            // Setup the reward object
            $reward = gamipress_daily_login_rewards_get_reward_object( $reward_id );

            $consecutive = (bool) gamipress_get_post_meta( $calendar_id, $prefix . 'consecutive' );

            // Check if consecutive login and we are not on first day
            if( $consecutive && $reward['order'] > 1 ) {

                // If multiple login is enabled, change yesterday date to prevent apply the penalty for login the same day
                if( $allow_multiple_login && $last_login === $date ) {
                    $yesterday = $date;
                }

                // If user not log in yesterday, then apply penalty
                if( $last_login !== $yesterday ) {

                    $penalty = gamipress_get_post_meta( $calendar_id, $prefix . 'consecutive_penalty' );
                    $revoke_awards = (bool) ( $penalty === 'revoke_restart' );

                    // Restart the user progress and revoke awarded rewards if penalty is set to revoke them
                    gamipress_daily_login_rewards_revoke_calendar_to_user( $calendar_id, $user_id, $revoke_awards );

                    // Log the calendar revoke or restart in logs
                    if( $revoke_awards ) {
                        gamipress_daily_login_rewards_log_calendar_revoke( $calendar_id, $user_id, $date, $last_login );
                    } else {
                        gamipress_daily_login_rewards_log_calendar_restart( $calendar_id, $user_id, $date, $last_login );
                    }

                    // We need to recalculate again the reward that will awarded to the user (usually, the first day reward)
                    $reward_id = gamipress_daily_login_rewards_get_user_next_reward_id( $user_id, $calendar_id );

                    if( $reward_id ) {
                        // Setup the reward object again
                        $reward = gamipress_daily_login_rewards_get_reward_object( $reward_id );
                    } else {
                        // Move to next calendar if this one has no new rewards
                        continue;
                    }

                }

            }

            // Award the calendar reward to the user (inside this method is checked if user has earned completely the calendar)
            gamipress_daily_login_rewards_award_reward_to_user( $reward, $user_id );
        }

    }

    // Update the user last login
    gamipress_update_user_meta( $user_id, $prefix . 'last_login', $date );

}

/**
 * Get all available to earn calendars to a given user
 *
 * @since 1.0.0
 *
 * @param int $user_id
 *
 * @return array        Array of reward calendars ids like array( 1, 2, 3 )
 */
function gamipress_daily_login_rewards_get_user_available_calendars( $user_id ) {

    global $wpdb;

    $posts          = GamiPress()->db->posts;
    $user_earnings  = GamiPress()->db->user_earnings;

    // Get all published calendars
    $calendars = $wpdb->get_col( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s",
        'rewards-calendar',
        'publish'
    ) );

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    foreach( $calendars as $index => $calendar_id ) {

        // ------------------------
        // Date limit
        // ------------------------

        $date_limit = (bool) gamipress_get_post_meta( $calendar_id, $prefix . 'date_limit' );

        //  Check if calendar is time limited
        if( $date_limit ) {

            $now = date( 'Y-m-d', current_time( 'timestamp' ) );

            // Check if calendar is available yet
            $start_date = gamipress_get_post_meta( $calendar_id, $prefix . 'start_date' );

            if( $start_date ) {
                $start_date = date( 'Y-m-d', $start_date );

                if( $now < $start_date ) {
                    // Remove this calendar from calendars list because is not available yet
                    unset( $calendars[$index] );
                }
            }

            // Check if calendar is ended
            $end_date = gamipress_get_post_meta( $calendar_id, $prefix . 'end_date' );

            if( $end_date ) {
                $end_date = date( 'Y-m-d', $end_date );

                if( $now > $end_date ) {
                    // Remove this calendar from calendars list because availability is ended
                    unset( $calendars[$index] );
                }
            }
        }

        // ------------------------
        // Repeatable
        // ------------------------

        $repeatable_times = gamipress_daily_login_rewards_get_calendar_repeatable_times( $calendar_id );

        if( $repeatable_times > 0 ) {
            $earned_times = count( gamipress_get_user_achievements( array( 'user_id' => $user_id, 'achievement_id' => $calendar_id ) ) );

            if( $earned_times >= $repeatable_times ) {
                // Remove this calendar from calendars list because user has earned it the maximum allowed times
                unset( $calendars[$index] );
            }
        }

        // ------------------------
        // Complete rewards calendars
        // ------------------------

        $complete_calendars = (bool) gamipress_get_post_meta( $calendar_id, $prefix . 'complete_rewards_calendars' );

        //  Check if calendar requires complete other calendars first
        if( $complete_calendars ) {

            $required_calendars = gamipress_get_post_meta( $calendar_id, $prefix . 'required_rewards_calendars', false );

            if( is_array( $required_calendars ) ) {

                $completed_calendars = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*)
                    FROM {$user_earnings} AS e
                    WHERE e.user_id = %d
                     AND e.post_type = %s
                     AND e.post_id IN ( " . implode( ', ', $required_calendars ) . " )",
                    $user_id,
                    'rewards-calendar'
                ) );

                // Remove this calendar from calendars list because requires other calendars to be completed first
                if( count( $required_calendars ) > absint( $completed_calendars ) ) {
                    unset( $calendars[$index] );
                }

            }

        }

    }

    return $calendars;

}

/**
 * Get all available to earn rewards of the given calendar
 *
 * @since 1.0.0
 *
 * @param int $calendar_id
 *
 * @return array                    Array of rewards ids like array( 1, 2, 3 )
 */
function gamipress_daily_login_rewards_get_calendar_rewards( $calendar_id ) {

    $rewards = gamipress_get_post_meta( $calendar_id, '_gamipress_daily_login_rewards_rewards_cache' );

    if( ! is_array( $rewards ) ) {

        global $wpdb;

        $posts = GamiPress()->db->posts;

        // Get lowest menu order
        $rewards = $wpdb->get_col( $wpdb->prepare(
            "SELECT p.ID
                FROM {$posts} AS p
                WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND p.post_parent = %d
                ORDER BY p.menu_order ASC",
            'calendar-reward',
            'publish',
            $calendar_id
        ) );

        gamipress_update_post_meta( $calendar_id, '_gamipress_daily_login_rewards_rewards_cache', $rewards );

    }

    return $rewards;

}

/**
 * Get the calendar ID of the given reward
 *
 * @since 1.0.3
 *
 * @param int $reward_id
 *
 * @return int                      The reward's calendar
 */
function gamipress_daily_login_rewards_get_reward_calendar( $reward_id ) {

    $calendar_id = gamipress_get_post_field( 'post_parent', $reward_id );

    return $calendar_id;

}

/**
 * Get the next reward that the given user can get rewarded of the given calendar
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param int $calendar_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_user_next_reward_id( $user_id, $calendar_id ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    $last_reward_id = gamipress_get_user_meta( $user_id, $prefix . "calendar_{$calendar_id}_last_reward_id" );
    $calendar_earned_datetime = gamipress_get_last_earning_datetime( array(
        'user_id' => $user_id,
        'post_id' => $calendar_id,
        'post_type' => 'rewards-calendar'
    ) );

    // On users that this function runs first time after 1.0.3 update ($last_reward_id empty)
    // is required to perform a backward compatibility check
    if( $last_reward_id === '' ) {

        // Backward compatibility check
        // If user has not earned any rewards (or last earned time is not set) and repeatable times is set to 1, then run previous method code

        global $wpdb;

        $posts          = GamiPress()->db->posts;
        $user_earnings  = GamiPress()->db->user_earnings;

        // Get lowest menu order
        $reward_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT p.ID
                FROM {$posts} AS p
                WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND p.post_parent = %d
                 AND p.ID NOT IN (
                    SELECT e.post_id FROM {$user_earnings} AS e WHERE e.user_id = %d AND e.post_type = %s
                 )
                ORDER BY p.menu_order ASC
                LIMIT 1",
            'calendar-reward',
            'publish',
            $calendar_id,
            $user_id,
            'calendar-reward'
        ) );

    } else {

        $last_reward_id = absint( $last_reward_id );

        if( $last_reward_id === 0 ) {

            // If user has not earned any reward, then return the first reward
            $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );

        } else if( ! gamipress_get_post( $last_reward_id ) ) {

            // If reward does not exists, then return the first reward
            $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );

        } else if( $last_reward_id === gamipress_daily_login_rewards_get_last_reward_id( $calendar_id ) ) {

            // If is the last reward, then fallback to first reward
            $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );

        } else if( gamipress_get_earnings_count( array(
                'user_id' => $user_id,
                'post_id' => $last_reward_id,
                'post_type' => 'calendar-reward',
                'since' => $calendar_earned_datetime,
                ) ) === 0 ) {

            // If the reward has been revoked, get the previous earned reward
            $last_reward_id = false;
            $prev_rewards_ids = gamipress_daily_login_rewards_get_all_prev_rewards_ids( $last_reward_id );

            foreach( $prev_rewards_ids as $prev_reward_id ) {
                // Break loop if finally found the previous earned reward
                if( gamipress_get_earnings_count( array( 'user_id' => $user_id, 'post_id' => $prev_reward_id ) ) ) {
                    $last_reward_id = $prev_reward_id;
                    break;
                }
            }

            // Fallback to first reward
            if( ! $last_reward_id ) {
                $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );
            }
        } else {

            $repeatable_times = gamipress_daily_login_rewards_get_calendar_repeatable_times( $calendar_id );

            if( $repeatable_times !== 1 && $last_reward_id === gamipress_daily_login_rewards_get_last_reward_id( $calendar_id ) ) {

                // If calendar can be earned more than 1 time and user has earned last reward, then return the first reward
                $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );

            } else {

                // Return the next reward based on last reward user earned
                $reward_id = gamipress_daily_login_rewards_get_next_reward_id( $last_reward_id );
            }

            if( $reward_id === 0 ) {
                // Fallback to first reward if last earned reward has been deleted
                $reward_id = gamipress_daily_login_rewards_get_first_reward_id( $calendar_id );
            }
        }

    }

    /**
     * Get the next reward that the given user can get rewarded of the given calendar
     *
     * @since 1.0.3
     *
     * @param int $reward_id
     * @param int $user_id
     * @param int $calendar_id
     *
     * @return int
     */
    return apply_filters( 'gamipress_daily_login_rewards_get_user_next_reward_id', $reward_id, $user_id, $calendar_id );

}

/**
 * Get last reward id earned of a calendar
 *
 * @param int $user_id
 * @param int $calendar_id
 *
 * @return bool|int         The last reward ID, false if user has not earned any reward of this calendar
 */
function gamipress_daily_login_rewards_get_user_last_reward_id( $user_id, $calendar_id ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    $reward_id = absint( gamipress_get_user_meta( $user_id, $prefix . "calendar_{$calendar_id}_last_reward_id" ) );

    if( $reward_id === 0 ) {
        return false;
    }

    return $reward_id;

}

/**
 * Update last reward id earned of a calendar
 *
 * @param int $user_id
 * @param int $calendar_id
 * @param int $reward_id
 *
 * @return bool|int
 */
function gamipress_daily_login_rewards_update_user_last_reward_id( $user_id, $calendar_id, $reward_id ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    // Set the last reward id earned of this calendar
    return gamipress_update_user_meta( $user_id, $prefix . "calendar_{$calendar_id}_last_reward_id",  $reward_id );

}

/**
 * Reset last reward id earned of a calendar
 *
 * @param int $user_id
 * @param int $calendar_id
 *
 * @return bool
 */
function gamipress_daily_login_rewards_reset_user_last_reward_id( $user_id, $calendar_id ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    // Set the last reward id meta of this calendar as 0
    return gamipress_update_user_meta( $user_id, $prefix . "calendar_{$calendar_id}_last_reward_id", '0' );

}

/**
 * Get first reward id of the given calendar
 *
 * @since 1.0.3
 *
 * @param int $calendar_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_first_reward_id( $calendar_id ) {

    global $wpdb;

    $cache = gamipress_get_cache( 'rewards_calendars', array() );

    // If result already cached, return it
    if( isset( $cache[$calendar_id] ) && isset( $cache[$calendar_id]['first_reward_id'] ) ) {
        return $cache[$calendar_id]['first_reward_id'];
    }

    $posts = GamiPress()->db->posts;

    // Get higher menu order
    $first_reward_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			ORDER BY p.menu_order ASC
			LIMIT 1",
        'calendar-reward',
        'publish',
        $calendar_id
    ) );

    // Cache function result
    if( ! isset( $cache[$calendar_id] ) ) {
        $cache[$calendar_id] = array();
    }

    $cache[$calendar_id]['first_reward_id'] = $first_reward_id;

    gamipress_set_cache( 'rewards_calendars', $cache );

    return $first_reward_id;

}

/**
 * Get last reward id of the given calendar
 *
 * @since 1.0.0
 *
 * @param int $calendar_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_last_reward_id( $calendar_id ) {

    global $wpdb;

    $cache = gamipress_get_cache( 'rewards_calendars', array() );

    // If result already cached, return it
    if( isset( $cache[$calendar_id] ) && isset( $cache[$calendar_id]['last_reward_id'] ) ) {
        return $cache[$calendar_id]['last_reward_id'];
    }

    $posts = GamiPress()->db->posts;

    // Get higher menu order
    $last_reward_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			ORDER BY p.menu_order DESC
			LIMIT 1",
        'calendar-reward',
        'publish',
        $calendar_id
    ) );

    // Cache function result
    if( ! isset( $cache[$calendar_id] ) ) {
        $cache[$calendar_id] = array();
    }

    $cache[$calendar_id]['last_reward_id'] = $last_reward_id;

    gamipress_set_cache( 'rewards_calendars', $cache );

    return $last_reward_id;

}

/**
 * Get previous reward id of the given reward
 *
 * @since 1.0.3
 *
 * @param int $reward_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_prev_reward_id( $reward_id ) {

    global $wpdb;

    $posts = GamiPress()->db->posts;

    $calendar_id = gamipress_daily_login_rewards_get_reward_calendar( $reward_id );

    // Get lower menu order
    $prev_reward_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			 AND p.menu_order < %s
			ORDER BY p.menu_order DESC
			LIMIT 1",
        'calendar-reward',
        'publish',
        $calendar_id,
        gamipress_get_post_field( 'menu_order', $reward_id )
    ) );

    return $prev_reward_id;

}

/**
 * Get all previous rewards ids of the given reward
 *
 * @since 1.1.0
 *
 * @param int $reward_id
 *
 * @return array
 */
function gamipress_daily_login_rewards_get_all_prev_rewards_ids( $reward_id ) {

    global $wpdb;

    $posts = GamiPress()->db->posts;

    $calendar_id = gamipress_daily_login_rewards_get_reward_calendar( $reward_id );

    // Get lower menu order
    $rewards = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			 AND p.menu_order < %s
			ORDER BY p.menu_order DESC",
        'calendar-reward',
        'publish',
        $calendar_id,
        gamipress_get_post_field( 'menu_order', $reward_id )
    ) );

    return wp_list_pluck( $rewards, 'ID' );

}

/**
 * Get next reward id of the given reward
 *
 * @since 1.0.3
 *
 * @param int $reward_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_next_reward_id( $reward_id ) {

    global $wpdb;

    $posts = GamiPress()->db->posts;

    $calendar_id = gamipress_daily_login_rewards_get_reward_calendar( $reward_id );

    // Get higher menu order
    $next_reward_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			 AND p.menu_order > %s
			ORDER BY p.menu_order ASC
			LIMIT 1",
        'calendar-reward',
        'publish',
        $calendar_id,
        gamipress_get_post_field( 'menu_order', $reward_id )
    ) );

    return $next_reward_id;

}

/**
 * Get next reward id of the given reward
 *
 * @since 1.1.0
 *
 * @param int $reward_id
 *
 * @return array
 */
function gamipress_daily_login_rewards_get_all_next_rewards_ids( $reward_id ) {

    global $wpdb;

    $posts = GamiPress()->db->posts;

    $calendar_id = gamipress_daily_login_rewards_get_reward_calendar( $reward_id );

    // Get higher menu order
    $rewards = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %d
			 AND p.menu_order > %s
			ORDER BY p.menu_order ASC",
        'calendar-reward',
        'publish',
        $calendar_id,
        gamipress_get_post_field( 'menu_order', $reward_id )
    ) );

    return wp_list_pluck( $rewards, 'ID' );

}

/**
 * Award the given calendar reward to the given user
 *
 * Triggers:
 * gamipress_daily_login_rewards_earn_reward
 * gamipress_daily_login_rewards_specific_earn_reward
 * gamipress_daily_login_rewards_earn_points_reward
 * gamipress_daily_login_rewards_specific_earn_points_reward
 * gamipress_daily_login_rewards_earn_achievement_reward
 * gamipress_daily_login_rewards_specific_earn_achievement_reward
 * gamipress_daily_login_rewards_earn_rank_reward
 * gamipress_daily_login_rewards_specific_earn_rank_reward
 *
 * @since 1.0.0
 *
 * @param int|array $reward_id
 * @param int       $user_id
 */
function gamipress_daily_login_rewards_award_reward_to_user( $reward_id, $user_id ) {

    if( is_array( $reward_id ) ) {
        $reward = $reward_id;
    } else {
        $reward = gamipress_daily_login_rewards_get_reward_object( $reward_id );
    }

    $calendar_earned_datetime = gamipress_get_last_earning_datetime( array(
        'user_id' => $user_id,
        'post_id' => $reward['rewards_calendar'],
        'post_type' => 'rewards-calendar'
    ) );

    // Bail if user has earned this reward yet
    if( gamipress_get_earnings_count( array(
            'user_id' => $user_id,
            'post_id' => $reward['ID'],
            'post_type' => 'calendar-reward',
            'since' => $calendar_earned_datetime,
        ) ) !== 0 ) {
        return;
    }

    // Award the awards configured on the calendar reward to the user
    if( $reward['reward_type'] === 'points' ) {

        if( $reward['points'] ) {
            // Award the points to the user
            gamipress_award_points_to_user( $user_id, $reward['points'], $reward['points_type'] );
        }

    } else if( $reward['reward_type'] === 'achievement' ) {

        $achievement = get_post( $reward['achievement'] );

        if( $achievement ) {
            // Award the achievement to the user
            gamipress_award_achievement_to_user( $achievement->ID, $user_id );
        }

    } else if( $reward['reward_type'] === 'rank' ) {

        $rank = get_post( $reward['rank'] );

        if( $rank ) {

            // Get the current user rank
            $user_rank_id = gamipress_get_user_rank_id( $user_id, $rank->post_type );

            // Just award the rank if user is in a lowest priority rank
            if( gamipress_get_rank_priority( $rank->ID ) > gamipress_get_rank_priority( $user_rank_id ) ) {
                // Award the rank to the user
                gamipress_update_user_rank( $user_id, $rank->ID );
            }

        }

    }

    // Register the calendar reward earning
    gamipress_insert_user_earning( $user_id, array(
        'post_id' => $reward['ID'],
        'post_type' => 'calendar-reward',
        'points' => absint( $reward['points'] ),
        'points_type' => $reward['points_type'],
        'date' => date( 'Y-m-d H:i:s' )
    ) );

    // Set the last reward id earned of this calendar
    gamipress_daily_login_rewards_update_user_last_reward_id( $user_id, $reward['rewards_calendar'], $reward['ID'] );

    // Trigger any daily login reward
    do_action( 'gamipress_daily_login_rewards_earn_reward', $user_id, $reward['ID'], $reward['rewards_calendar'] );

    // Trigger specific calendar daily login reward
    do_action( 'gamipress_daily_login_rewards_specific_earn_reward', $user_id, $reward['ID'], $reward['rewards_calendar'] );

    $reward_type = $reward['reward_type'];

    // Trigger per type any daily login reward
    do_action( "gamipress_daily_login_rewards_earn_{$reward_type}_reward", $user_id, $reward['ID'], $reward['rewards_calendar'] );

    // Trigger per type specific daily login reward
    do_action( "gamipress_daily_login_rewards_specific_earn_{$reward_type}_reward", $user_id, $reward['ID'], $reward['rewards_calendar'] );

    // Check if user has earned all rewards of this calendar
    gamipress_daily_login_rewards_maybe_award_calendar_to_user( $reward['rewards_calendar'], $user_id );

}

/**
 * Check if user has earned all rewards of the given calendar to award it too
 *
 * @since 1.0.0
 *
 * @param int|array $calendar_id
 * @param int       $user_id
 */
function gamipress_daily_login_rewards_maybe_award_calendar_to_user( $calendar_id, $user_id ) {

    $calendar_last_reward_id = absint( gamipress_daily_login_rewards_get_last_reward_id( $calendar_id ) );
    $user_last_reward_id = absint( gamipress_daily_login_rewards_get_user_last_reward_id( $user_id, $calendar_id ) );

    // If user has earned last calendar reward, then award the calendar itself
    if( $calendar_last_reward_id === $user_last_reward_id ) {
        gamipress_daily_login_rewards_award_calendar_to_user( $calendar_id, $user_id );
    }

}

/**
 * Award the given calendar reward to the given user
 *
 * Triggers:
 * gamipress_daily_login_rewards_earn_calendar
 * gamipress_daily_login_rewards_specific_earn_calendar
 *
 * @since 1.0.0
 *
 * @param int|array $calendar_id
 * @param int       $user_id
 */
function gamipress_daily_login_rewards_award_calendar_to_user( $calendar_id, $user_id ) {

    // Register the rewards calendar reward
    gamipress_insert_user_earning( $user_id, array(
        'post_id' => $calendar_id,
        'post_type' => 'rewards-calendar',
        'points' => 0,
        'points_type' => '',
        'date' => date( 'Y-m-d H:i:s' )
    ) );

    // On award a full calendar to the user, is needle to reset the last reward id
    gamipress_daily_login_rewards_reset_user_last_reward_id( $user_id, $calendar_id );

    // Trigger earn any calendar
    do_action( 'gamipress_daily_login_rewards_earn_calendar', $user_id, $calendar_id );

    // Trigger specific calendar earned
    do_action( 'gamipress_daily_login_rewards_specific_earn_calendar', $user_id, $calendar_id );

}

/**
 * Revoke the given rewards calendar to the given user
 *
 * @since 1.0.0
 *
 * @param int|array $calendar_id
 * @param int       $user_id
 * @param bool      $revoke_rewards         Set to true to revoke awarded items
 */
function gamipress_daily_login_rewards_revoke_calendar_to_user( $calendar_id, $user_id, $revoke_rewards = false ) {

    $rewards = gamipress_daily_login_rewards_get_calendar_rewards( $calendar_id );

    // Loop all calendar rewards to revoke them
    foreach( $rewards as $reward_id ) {
        gamipress_daily_login_rewards_revoke_reward_to_user( $reward_id, $user_id, $revoke_rewards );
    }

    global $wpdb;

    $user_earnings = GamiPress()->db->user_earnings;

    // Delete the rewards calendar from user earnings
    $wpdb->delete( $user_earnings,
        array(
            'user_id' => $user_id,
            'post_id' => $calendar_id,
            'post_type' => 'rewards-calendar',
        ),
        array( '%d', '%d', '%s' )
    );

    // On revoke a full calendar to the user, is needle to reset the last reward id
    gamipress_daily_login_rewards_reset_user_last_reward_id( $user_id, $calendar_id );

    // Trigger revoke any calendar
    do_action( 'gamipress_daily_login_rewards_revoke_calendar', $user_id, $calendar_id );

    // Trigger specific calendar revoked
    do_action( 'gamipress_daily_login_rewards_specific_revoke_calendar', $user_id, $calendar_id );

}

/**
 * Revoke the given calendar reward to the given user
 *
 * @since 1.0.0
 *
 * @param int|array $reward_id
 * @param int       $user_id
 * @param bool      $revoke_rewards         Set to true to revoke awarded items
 */
function gamipress_daily_login_rewards_revoke_reward_to_user( $reward_id, $user_id, $revoke_rewards = false ) {

    global $wpdb;

    $user_earnings = GamiPress()->db->user_earnings;

    // Check if should revoke awards
    if( $revoke_rewards ) {

        $earnings_count = gamipress_get_earnings_count( array( 'user_id' => $user_id, 'post_id' => $reward_id, 'post_type' => 'calendar-reward' ) );
        $earned = $earnings_count > 0;

        // Only revoke if user has earned it previously
        if( $earned ) {

            $reward = gamipress_daily_login_rewards_get_reward_object( $reward_id );

            // Process the revoke of awards awarded by the calendar reward
            if( $reward['reward_type'] === 'points' ) {

                if( $reward['points'] ) {

                    // Award the points to the user
                    gamipress_deduct_points_to_user( $user_id, $reward['points'], $reward['points_type'] );

                }

            } else if( $reward['reward_type'] === 'achievement' ) {

                $achievement = get_post( $reward['achievement'] );

                if( $achievement ) {

                    // Award the achievement to the user
                    gamipress_revoke_achievement_to_user( $achievement->ID, $user_id );

                }

            } else if( $reward['reward_type'] === 'rank' ) {

                $rank = get_post( $reward['rank'] );

                if( $rank ) {

                    // Get the current user rank
                    $user_rank_id = gamipress_get_user_rank_id( $user_id, $rank->post_type );

                    // If user is actually on this rank, move to the previous rank
                    if( $user_rank_id === absint( $rank->ID ) ) {

                        // Revoke rank
                        gamipress_revoke_rank_to_user( $user_id, $user_rank_id );

                    }

                }

            }

        }

    }

    // Delete the calendar reward from user earnings
    $wpdb->delete( $user_earnings,
        array(
            'user_id' => $user_id,
            'post_id' => $reward_id,
            'post_type' => 'calendar-reward',
        ),
        array( '%d', '%d', '%s' )
    );

    // Trigger any daily login reward revoked
    do_action( 'gamipress_daily_login_rewards_revoke_reward', $user_id, $reward_id, gamipress_daily_login_rewards_get_reward_calendar( $reward_id ) );

}

/**
 * Get the calendar's repeatable times
 *
 * @since 1.0.3
 *
 * @param int $calendar_id
 *
 * @return int
 */
function gamipress_daily_login_rewards_get_calendar_repeatable_times( $calendar_id ) {

    $cache = gamipress_get_cache( 'rewards_calendars', array() );

    // If result already cached, return it
    if( isset( $cache[$calendar_id] ) && isset( $cache[$calendar_id]['repeatable_times'] ) ) {
        return $cache[$calendar_id]['repeatable_times'];
    }

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_daily_login_rewards_';

    $repeatable = (bool) gamipress_get_post_meta( $calendar_id, $prefix . 'repeatable' );
    $repeatable_times = 1;

    //  Check if calendar is repeatable
    if( $repeatable ) {
        $repeatable_times = absint( gamipress_get_post_meta( $calendar_id, $prefix . 'repeatable_times' ) );
    }

    // Cache function result
    if( ! isset( $cache[$calendar_id] ) ) {
        $cache[$calendar_id] = array();
    }

    $cache[$calendar_id]['repeatable_times'] = $repeatable_times;

    gamipress_set_cache( 'rewards_calendars', $cache );

    /**
     * Filter to override the calendar repeatable times
     *
     * @since 1.0.3
     *
     * @param int   $repeatable_times
     * @param int   $calendar_id
     * @param bool  $repeatable
     *
     * @return int
     */
    return apply_filters( 'gamipress_daily_login_rewards_get_calendar_repeatable_times', $repeatable_times, $calendar_id, $repeatable );

}

/**
 * Check if user has earned a specific reward
 *
 * @since 1.0.3
 *
 * @param int   $user_id
 * @param int   $reward_id
 * @param bool  $current_status If true, will return the current earned status based on if calendar is repeatable or not
 *
 * @return bool
 */
function gamipress_daily_login_rewards_has_user_earned_reward( $user_id, $reward_id, $current_status = false ) {

    $earned = false;

    if( $user_id !== 0 ) {

        $earned_times = count( gamipress_get_user_achievements( array( 'user_id' => get_current_user_id(), 'achievement_id' => get_the_ID() ) ) );

        if( $current_status ) {
            // Check if user last reward order is equal or higher that reward to check
            $calendar_id = gamipress_daily_login_rewards_get_reward_calendar( $reward_id );

            $user_last_reward_id = gamipress_daily_login_rewards_get_user_last_reward_id( $user_id, $calendar_id );

            if( $user_last_reward_id ) {
                $menu_order = absint( gamipress_get_post_field( 'menu_order', $reward_id ) );
                $last_menu_order = absint( gamipress_get_post_field( 'menu_order', $user_last_reward_id ) );

                $earned = ( $last_menu_order >= $menu_order );
            } else {
                // If there is not last reward ID (eg: user has completed the calendar)
                // Check number of times earned is equal or higher that calendar repeatable times
                $repeatable_times = gamipress_daily_login_rewards_get_calendar_repeatable_times( $calendar_id );

                $earned = ( $earned_times > 0 && $earned_times >= $repeatable_times );
            }

        } else {
            // Check if user has earned it any time
            $earned = ( $earned_times > 0 );
        }

    }

    /**
     * Check if user has earned a specific reward
     *
     * @since 1.0.3
     *
     * @param bool  $earned
     * @param int   $user_id
     * @param int   $reward_id
     * @param bool  $current_status If true, will return the current earned status based on if calendar is repeatable or not
     *
     * @return bool
     */
    return apply_filters( 'gamipress_daily_login_rewards_has_user_earned_reward', $earned, $user_id, $reward_id, $current_status )  ;

}