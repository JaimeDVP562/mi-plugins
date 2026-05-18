<?php

/**
 * Functions
 *
 * @package GamiPress\Recurring_Rewards\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get recurring rewards
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_recurring_rewards_get_recurring_rewards() {

    ct_setup_table( 'gamipress_recurring_rewards' );

    $ct_query = new CT_Query( array(
    ) );

    $results = $ct_query->get_results();

    ct_reset_setup_table();

    return $results;

}

/**
 * Get a specific recurring reward
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id
 *
 * @return object|null
 */
function gamipress_recurring_rewards_get_recurring_reward( $recurring_reward_id ) {

    ct_setup_table( 'gamipress_recurring_rewards' );

    $ct_query = new CT_Query( array(
        'field' => 'recurring_reward_id',
        'value' => $recurring_reward_id,
    ) );

    $results = $ct_query->get_results();

    ct_reset_setup_table();

    return ! empty( $results ) ? $results[0] : null;

}

/**
 * Get a CT table name for recurring rewards tables.
 *
 * @since 1.0.0
 *
 * @param string $table
 *
 * @return string
 */
function gamipress_recurring_rewards_get_table_name( $table ) {

    $ct_table = ct_setup_table( $table );
    $table_name = '';

    if ( is_a( $ct_table, 'CT_Table' ) && isset( $ct_table->db->table_name ) ) {
        $table_name = $ct_table->db->table_name;
    }

    ct_reset_setup_table();

    return $table_name;

}

/**
 * Determine if current CT context belongs to recurring rewards table.
 *
 * @since 1.0.0
 *
 * @param string $table_name
 *
 * @return bool
 */
function gamipress_recurring_rewards_is_current_ct_table( $table_name ) {

    global $ct_table;

    return ( is_a( $ct_table, 'CT_Table' ) && isset( $ct_table->name ) && $ct_table->name === $table_name );

}

/**
 * Get user recurring rewards
 *
 * @since 1.0.0
 *
 * @param int $user_id
 *
 * @return array
 */
function gamipress_recurring_rewards_get_user_recurring_rewards( $user_id ) {

    global $wpdb;

    $user_rewards_table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_reward_users' );
    $recurring_rewards_table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_rewards' );

    if ( empty( $user_rewards_table_name ) || empty( $recurring_rewards_table_name ) ) {
        return array();
    }

    $recurring_rewards = $wpdb->get_results( $wpdb->prepare(
        "SELECT rr.* FROM {$user_rewards_table_name} rru
        INNER JOIN {$recurring_rewards_table_name} rr ON rru.recurring_reward_id = rr.recurring_reward_id
        WHERE rru.user_id = %d AND rru.active = 1",
        $user_id
    ) );

    return $recurring_rewards;

}

/**
 * Check if user meets requirements for recurring reward
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param int $recurring_reward_id
 *
 * @return bool
 */
function gamipress_recurring_rewards_user_meets_requirements( $user_id, $recurring_reward_id ) {

    $recurring_reward = gamipress_recurring_rewards_get_recurring_reward( $recurring_reward_id );

    if ( ! $recurring_reward ) {
        return false;
    }

    $requirements_json = isset( $recurring_reward->requirements ) ? $recurring_reward->requirements : '';

    if ( empty( $requirements_json ) ) {
        return true;
    }

    $requirements = json_decode( $requirements_json, true );

    if ( ! is_array( $requirements ) ) {
        return false;
    }

    if ( empty( $requirements ) ) {
        return true;
    }

    foreach ( $requirements as $requirement ) {
        if ( $requirement['type'] === 'achievement' ) {
            if ( ! gamipress_has_user_earned_achievement( $requirement['id'], $user_id ) ) {
                return false;
            }
        } elseif ( $requirement['type'] === 'rank' ) {
            if ( ! function_exists( 'gamipress_has_user_earned_rank' ) || ! gamipress_has_user_earned_rank( $requirement['id'], $user_id ) ) {
                return false;
            }
        }
    }

    return true;

}

/**
 * Grant access to recurring reward
 *
 * @since 1.0.0
 *
 * @param int $user_id             User ID.
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_grant_access( $user_id, $recurring_reward_id ) {

    $ct_table = ct_setup_table( 'gamipress_recurring_reward_users' );
    global $wpdb;
    $table_name = $ct_table->db->table_name;
    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT recurring_reward_user_id, active FROM {$table_name} WHERE user_id = %d AND recurring_reward_id = %d",
        $user_id, $recurring_reward_id
    ) );


    $current_time = current_time( 'timestamp' );
    $current_mysql = current_time( 'mysql' );

    if ( $existing ) {
        if ( ! $existing->active ) {
            $wpdb->update(
                $table_name,
                array( 'active' => 1, 'date_unlocked' => $current_mysql ),
                array( 'recurring_reward_user_id' => $existing->recurring_reward_user_id )
            );
            ct_update_object_meta( $existing->recurring_reward_user_id, '_date_activated', $current_time, 'gamipress_recurring_reward_users' );
        }
    } else {
        $wpdb->insert( $table_name, array(
            'user_id' => $user_id,
            'recurring_reward_id' => $recurring_reward_id,
            'date_unlocked' => $current_mysql,
            'active' => 1,
        ) );

        $new_id = $wpdb->insert_id;
        if ( $new_id ) {
            ct_update_object_meta( $new_id, '_user_id', $user_id, 'gamipress_recurring_reward_users' );
            ct_update_object_meta( $new_id, '_recurring_reward_id', $recurring_reward_id, 'gamipress_recurring_reward_users' );
            ct_update_object_meta( $new_id, '_date_activated', $current_time, 'gamipress_recurring_reward_users' );
            ct_update_object_meta( $new_id, '_last_award', 0, 'gamipress_recurring_reward_users' );
        }
    }

    ct_reset_setup_table();
}

/**
 * Grant access to existing users who meet the requirements
 * Also revokes access from users who don't meet requirements
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_grant_access_to_existing_users( $recurring_reward_id ) {
    
    $recurring_reward = gamipress_recurring_rewards_get_recurring_reward( $recurring_reward_id );

    if ( ! $recurring_reward ) {
        return;
    }

    $users = get_users( array( 'fields' => 'ID' ) );

    foreach ( $users as $user_id ) {
        if ( gamipress_recurring_rewards_user_meets_requirements( $user_id, $recurring_reward_id ) ) {
            gamipress_recurring_rewards_grant_access( $user_id, $recurring_reward_id );
        } else {
            gamipress_recurring_rewards_revoke_access( $user_id, $recurring_reward_id );
        }
    }
}


/**
 * Revoke access to recurring reward
 *
 * @since 1.0.0
 *
 * @param int $user_id             User ID.
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_revoke_access( $user_id, $recurring_reward_id ) {

    $ct_table = ct_setup_table( 'gamipress_recurring_reward_users' );

    $ct_table->db->update(
        array( 'active' => 0 ),
        array( 'user_id' => $user_id, 'recurring_reward_id' => $recurring_reward_id )
    );

    ct_reset_setup_table();

}

/**
 * Return award interval in seconds for a recurring reward.
 *
 * @since 1.0.0
 *
 * @param int    $cycle_amount Number of units configured for the cycle.
 * @param string $cycle_type   Cycle unit.
 *
 * @return int Interval in seconds.
 */
function gamipress_recurring_rewards_get_award_interval( $cycle_amount, $cycle_type ) {
    $cycle_amount = absint( $cycle_amount );
    if ( $cycle_amount < 1 ) {
        return 0;
    }

    switch ( $cycle_type ) {
        case 'second':
            return $cycle_amount;
        case 'minute':
            return $cycle_amount * MINUTE_IN_SECONDS;
        case 'hour':
            return $cycle_amount * HOUR_IN_SECONDS;
        case 'day':
            return $cycle_amount * DAY_IN_SECONDS;
        case 'week':
            return $cycle_amount * WEEK_IN_SECONDS;
        case 'month':
            return $cycle_amount * MONTH_IN_SECONDS;
        case 'year':
            return $cycle_amount * YEAR_IN_SECONDS;
    }

    return 0;
}

/**
 * Award points for a specific recurring reward when executed manually.
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return int Number of award operations executed.
 */
function gamipress_recurring_rewards_award_points_for_reward( $recurring_reward_id ) {
    $recurring_reward = gamipress_recurring_rewards_get_recurring_reward( $recurring_reward_id );
    if ( ! $recurring_reward ) {
        return 0;
    }

    gamipress_recurring_rewards_grant_access_to_existing_users( $recurring_reward_id );

    $points_amount   = absint( $recurring_reward->points_amount );
    $points_type     = $recurring_reward->points_type;
    $cycle_amount    = absint( $recurring_reward->cycle_amount );
    $cycle_type      = $recurring_reward->cycle_type;
    $cycle_day       = absint( $recurring_reward->cycle_day );
    $cycle_month_day = absint( $recurring_reward->cycle_month_day );

    if ( $points_amount < 1 || ! $cycle_type || ! $cycle_amount ) {
        return 0;
    }

    $now = current_time( 'timestamp' );
    $last_award = absint( get_option( 'gamipress_recurring_reward_last_award_' . $recurring_reward_id, 0 ) );

    if ( ! $last_award ) {
        update_option( 'gamipress_recurring_reward_last_award_' . $recurring_reward_id, $now );
        return 0;
    }

    if ( $cycle_type === 'week' ) {
        $current_day = date( 'N', $now );
        if ( $current_day != $cycle_day ) {
            return 0;
        }
    }

    if ( $cycle_type === 'month' ) {
        $current_day = date( 'j', $now );
        $days_in_month = date( 't', $now );
        $adjusted_day = min( $cycle_month_day, $days_in_month );
        if ( $current_day != $adjusted_day ) {
            return 0;
        }
    }

    $interval = gamipress_recurring_rewards_get_award_interval( $cycle_amount, $cycle_type );
    if ( $interval <= 0 || $now - $last_award < $interval ) {
        return 0;
    }

    update_option( 'gamipress_recurring_reward_last_award_' . $recurring_reward_id, $now );

    global $wpdb;
    $ct_table = ct_setup_table( 'gamipress_recurring_reward_users' );
    $user_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT user_id FROM {$ct_table->db->table_name} WHERE recurring_reward_id = %d AND active = 1",
        $recurring_reward_id
    ) );
    ct_reset_setup_table();

    if ( empty( $user_ids ) ) {
        return 1;
    }

    foreach ( $user_ids as $user_id ) {
        if ( ! gamipress_recurring_rewards_user_meets_requirements( $user_id, $recurring_reward_id ) ) {
            continue;
        }
        
        if ( function_exists( 'gamipress_award_points_to_user' ) ) {
            gamipress_award_points_to_user(
                $user_id,
                $points_amount,
                $points_type,
                array(
                    'reason' => __( 'Recurring reward points', 'gamipress-recurring-rewards' ),
                    'reference' => 'recurring_reward',
                    'reference_id' => $recurring_reward_id,
                )
            );
        }
    }

    return 1;
}

/**
 * Processes recurring rewards that are due and awards points to active users.
 *
 * This routine uses direct queries because cron execution may run without the
 * custom table context normally available in the admin UI.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_award_points() {

    global $wpdb;
    $now = current_time( 'timestamp' );

    $recurring_rewards_table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_rewards' );

    if ( empty( $recurring_rewards_table_name ) ) {
        return;
    }

    $user_rewards_table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_reward_users' );

    if ( empty( $user_rewards_table_name ) ) {
        return;
    }

    $recurring_rewards = $wpdb->get_results(
        "SELECT * FROM {$recurring_rewards_table_name}"
    );

    if ( empty( $recurring_rewards ) ) {
        return;
    }

    foreach ( $recurring_rewards as $reward ) {
        $reward_id       = $reward->recurring_reward_id;
        $points_amount   = absint( $reward->points_amount );
        $points_type     = $reward->points_type;
        $cycle_amount    = absint( $reward->cycle_amount );
        $cycle_type      = $reward->cycle_type;
        $cycle_day       = absint( $reward->cycle_day );
        $cycle_month_day = absint( $reward->cycle_month_day );

        if ( $points_amount < 1 || ! $cycle_type || ! $cycle_amount ) {
            continue;
        }

        if ( 'week' === $cycle_type && $cycle_day ) {
            if ( (int) date( 'N', $now ) !== $cycle_day ) {
                continue;
            }
        }

        if ( 'month' === $cycle_type && $cycle_month_day ) {
            $days_in_month  = (int) date( 't', $now );
            $adjusted_day   = min( $cycle_month_day, $days_in_month );
            if ( (int) date( 'j', $now ) !== $adjusted_day ) {
                continue;
            }
        }

        $interval = gamipress_recurring_rewards_get_award_interval( $cycle_amount, $cycle_type );
        if ( $interval <= 0 ) {
            continue;
        }

        $option_key     = 'gamipress_recurring_reward_last_execution_' . $reward_id;
        $last_execution = absint( get_option( $option_key, 0 ) );

        if ( ! $last_execution ) {
            update_option( $option_key, $now );
            continue;
        }

        $elapsed = $now - $last_execution;

        if ( $elapsed < $interval ) {
            continue;
        }

        $active_users = $wpdb->get_col( $wpdb->prepare(
            "SELECT user_id FROM {$user_rewards_table_name}
             WHERE recurring_reward_id = %d AND active = 1",
            $reward_id
        ) );

        if ( ! empty( $active_users ) ) {
            foreach ( $active_users as $user_id ) {
                if ( ! gamipress_recurring_rewards_user_meets_requirements( $user_id, $reward_id ) ) {
                    continue;
                }
                
                if ( function_exists( 'gamipress_award_points_to_user' ) ) {
                    gamipress_award_points_to_user(
                        $user_id,
                        $points_amount,
                        $points_type,
                        array(
                            'reason' => __( 'Recurring reward', 'gamipress-recurring-rewards' ),
                            'reference' => 'recurring_reward',
                            'reference_id' => $reward_id,
                        )
                    );
                }
            }
        }

        update_option( $option_key, $now );
    }
}

/**
 * Force award points now for a specific reward
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return bool True when at least one user received the reward.
 */
function gamipress_recurring_rewards_force_award_now( $recurring_reward_id ) {
    
    $recurring_reward = gamipress_recurring_rewards_get_recurring_reward( $recurring_reward_id );
    if ( ! $recurring_reward ) {
        return false;
    }

    $points_amount = absint( $recurring_reward->points_amount );
    $points_type   = $recurring_reward->points_type;

    if ( $points_amount < 1 || ! $points_type ) {
        return false;
    }

    global $wpdb;

    $user_rewards_table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_reward_users' );

    if ( empty( $user_rewards_table_name ) ) {
        return false;
    }
    
    $user_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT user_id FROM {$user_rewards_table_name}
         WHERE recurring_reward_id = %d AND active = 1",
        $recurring_reward_id
    ) );

    if ( empty( $user_ids ) ) {
        return false;
    }

    $awarded_count = 0;
    foreach ( $user_ids as $user_id ) {
        if ( ! gamipress_recurring_rewards_user_meets_requirements( $user_id, $recurring_reward_id ) ) {
            continue;
        }
        
        if ( function_exists( 'gamipress_award_points_to_user' ) ) {
            $result = gamipress_award_points_to_user(
                $user_id,
                $points_amount,
                $points_type,
                array(
                    'reason' => __( 'Recurring reward manual award', 'gamipress-recurring-rewards' ),
                    'reference' => 'recurring_reward',
                    'reference_id' => $recurring_reward_id,
                )
            );
            if ( $result ) {
                $awarded_count++;
            }
        }
    }

    delete_option( 'gamipress_recurring_reward_last_execution_' . $recurring_reward_id );

    return $awarded_count > 0;
}

/**
 * Reset award timer when reward is edited
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_reset_award_timer( $recurring_reward_id ) {
    if ( ! $recurring_reward_id ) {
        return;
    }

    delete_option( 'gamipress_recurring_reward_last_execution_' . $recurring_reward_id );
}

/**
 * Reset reward timer when recurring reward objects are updated.
 *
 * @since 1.0.0
 *
 * @param int    $object_id     Updated object ID.
 * @param object $object_after  Object state after the update.
 * @param object $object_before
 *
 * @return void
 */
function gamipress_recurring_rewards_reset_award_timer_on_ct_update( $object_id, $object_after, $object_before ) {

    unset( $object_after, $object_before );

    if ( ! gamipress_recurring_rewards_is_current_ct_table( 'gamipress_recurring_rewards' ) ) {
        return;
    }

    gamipress_recurring_rewards_reset_award_timer( $object_id );

}
add_action( 'ct_object_updated', 'gamipress_recurring_rewards_reset_award_timer_on_ct_update', 10, 3 );

/**
 * Clean up related data when a recurring reward is deleted.
 *
 * @since 1.0.0
 *
 * @param int $recurring_reward_id Recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_cleanup_on_delete( $recurring_reward_id ) {

    if ( ! $recurring_reward_id ) {
        return;
    }

    global $wpdb;

    $ct_table = ct_setup_table( 'gamipress_recurring_reward_users' );
    $wpdb->delete(
        $ct_table->db->table_name,
        array( 'recurring_reward_id' => intval( $recurring_reward_id ) ),
        array( '%d' )
    );
    ct_reset_setup_table();

    delete_option( 'gamipress_recurring_reward_last_award_' . $recurring_reward_id );
    delete_option( 'gamipress_recurring_reward_last_execution_' . $recurring_reward_id );
}

/**
 * Clean recurring reward related data when CT deletes one object.
 *
 * @since 1.0.0
 *
 * @param int $object_id Deleted object ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_cleanup_on_ct_delete( $object_id ) {

    if ( ! gamipress_recurring_rewards_is_current_ct_table( 'gamipress_recurring_rewards' ) ) {
        return;
    }

    gamipress_recurring_rewards_cleanup_on_delete( $object_id );

}
add_action( 'deleted_object', 'gamipress_recurring_rewards_cleanup_on_ct_delete' );
