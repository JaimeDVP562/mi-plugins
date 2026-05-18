<?php
/**
 * Listeners
 *
 * @package GamiPress\Recurring_Rewards\Listeners
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Update recurring reward access for a requirement match.
 *
 * @since 1.0.0
 *
 * @param int    $user_id          User ID to update.
 * @param string $requirement_type Requirement type to evaluate.
 * @param int    $requirement_id   Requirement object ID.
 * @param bool   $grant_access     Whether access should be granted or revoked.
 *
 * @return void
 */
function gamipress_recurring_rewards_process_requirement_access_change( $user_id, $requirement_type, $requirement_id, $grant_access = true ) {

    $user_id = absint( $user_id );
    $requirement_id = absint( $requirement_id );

    if ( $user_id <= 0 || $requirement_id <= 0 ) {
        return;
    }

    $recurring_rewards = gamipress_recurring_rewards_get_recurring_rewards();

    foreach ( $recurring_rewards as $recurring_reward ) {

        $requirements_json = $recurring_reward->requirements;
        $requirements = json_decode( $requirements_json, true );

        if ( is_array( $requirements ) ) {
            foreach ( $requirements as $requirement ) {
                if ( $requirement['type'] === $requirement_type && absint( $requirement['id'] ) === $requirement_id ) {
                    $user_meets_requirements = gamipress_recurring_rewards_user_meets_requirements( $user_id, $recurring_reward->recurring_reward_id );

                    if ( $grant_access && $user_meets_requirements ) {
                        gamipress_recurring_rewards_grant_access( $user_id, $recurring_reward->recurring_reward_id );
                    } elseif ( ! $grant_access && ! $user_meets_requirements ) {
                        gamipress_recurring_rewards_revoke_access( $user_id, $recurring_reward->recurring_reward_id );
                    }
                }
            }
        }
    }

}

/**
 * Award achievement listener
 *
 * @since 1.0.0
 *
 * @param int $user_id        User who earned the achievement.
 * @param int $achievement_id Achievement ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_award_achievement_listener( $user_id, $achievement_id ) {

    gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'achievement', $achievement_id, true );

}
add_action( 'gamipress_award_achievement', 'gamipress_recurring_rewards_award_achievement_listener', 10, 2 );

/**
 * Award rank listener
 *
 * @since 1.0.0
 *
 * @param int $user_id User who received the rank.
 * @param int $rank_id Rank ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_award_rank_listener( $user_id, $rank_id ) {

    gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'rank', $rank_id, true );

}
add_action( 'gamipress_award_rank_to_user', 'gamipress_recurring_rewards_award_rank_listener', 10, 2 );

/**
 * Update user rank listener
 *
 * @since 1.0.0
 *
 * @param int         $user_id        User whose rank changed.
 * @param WP_Post|int $new_rank       New rank object or ID.
 * @param WP_Post|int $old_rank       Previous rank object or ID.
 * @param int         $admin_id       Admin user ID that triggered the change.
 * @param int|null    $achievement_id Achievement ID related to the update when available.
 *
 * @return void
 */
function gamipress_recurring_rewards_update_user_rank_listener( $user_id, $new_rank, $old_rank, $admin_id, $achievement_id ) {

    $new_rank_id = ( is_object( $new_rank ) && isset( $new_rank->ID ) ) ? absint( $new_rank->ID ) : absint( $new_rank );
    $old_rank_id = ( is_object( $old_rank ) && isset( $old_rank->ID ) ) ? absint( $old_rank->ID ) : absint( $old_rank );

    if ( $new_rank_id > 0 ) {
        gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'rank', $new_rank_id, true );
    }

    if ( $old_rank_id > 0 && $old_rank_id !== $new_rank_id ) {
        gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'rank', $old_rank_id, false );
    }

}
add_action( 'gamipress_update_user_rank', 'gamipress_recurring_rewards_update_user_rank_listener', 10, 5 );

/**
 * Revoke rank listener
 *
 * @since 1.0.0
 *
 * @param int $user_id User who lost the rank.
 * @param int $rank_id Rank ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_revoke_rank_listener( $user_id, $rank_id ) {

    gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'rank', $rank_id, false );

}
add_action( 'gamipress_revoke_rank_to_user', 'gamipress_recurring_rewards_revoke_rank_listener', 10, 2 );

/**
 * Revoke achievement listener
 *
 * @since 1.0.0
 *
 * @param int $user_id        User who lost the achievement.
 * @param int $achievement_id Achievement ID.
 * @param int $earning_id     Earning record ID when available.
 *
 * @return void
 */
function gamipress_recurring_rewards_revoke_achievement_listener( $user_id, $achievement_id, $earning_id = 0 ) {

    gamipress_recurring_rewards_process_requirement_access_change( $user_id, 'achievement', $achievement_id, false );

}
add_action( 'gamipress_revoke_achievement_to_user', 'gamipress_recurring_rewards_revoke_achievement_listener', 10, 2 );
add_action( 'gamipress_revoke_achievement', 'gamipress_recurring_rewards_revoke_achievement_listener', 10, 2 );
