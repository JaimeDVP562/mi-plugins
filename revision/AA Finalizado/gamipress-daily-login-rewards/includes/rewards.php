<?php
/**
 * Rewards
 *
 * @package     GamiPress\Daily_Login_Rewards\Rewards
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Build a reward object
 *
 * @since  1.0.0
 *
 * @param int $reward_id
 * @param int|string $thumbnail_size
 *
 * @return array
 */
function gamipress_daily_login_rewards_get_reward_object( $reward_id = 0, $thumbnail_size = null ) {

    if( $thumbnail_size !== null && is_numeric( $thumbnail_size ) ) {
        $thumbnail_size = array( $thumbnail_size, $thumbnail_size );
    }

    // Setup our default requirements array, assume we require nothing
    $reward = array(
        'ID'                    => $reward_id,
        'reward_type'           => gamipress_get_post_meta( $reward_id, '_gamipress_reward_type' ),
        'label'                 => gamipress_get_post_field( 'post_title', $reward_id ),
        'rewards_calendar'      => gamipress_get_post_field( 'post_parent', $reward_id ),
        'order'                 => absint( gamipress_get_post_field( 'menu_order', $reward_id ) ),
        'thumbnail_id'          => absint( get_post_thumbnail_id( $reward_id ) ),
        'thumbnail'             => false,

        // Defaults
        'points'                => 0,
        'points_type'           => '',
        'points_type_thumbnail' => 0,
        'achievement'           => 0,
        'achievement_thumbnail' => 0,
        'rank'                  => 0,
        'rank_thumbnail'        => 0,
    );

    if( empty( $reward['reward_type'] ) ) {
        $reward['reward_type'] = 'none';
    }

    if( $reward['thumbnail_id'] ) {
        $reward['thumbnail'] =  get_the_post_thumbnail( $reward_id, ( $thumbnail_size === null ? 'full' : $thumbnail_size ) );
    }

    // Specific reward type data
    if( $reward['reward_type'] === 'points' ) {

        $reward['points']                  = absint( gamipress_get_post_meta( $reward_id, '_gamipress_points' ) );
        $reward['points_type']             = gamipress_get_post_meta( $reward_id, '_gamipress_points_type' );
        $reward['points_type_thumbnail']   = absint( gamipress_get_post_meta( $reward_id, '_gamipress_points_type_thumbnail' ) );

        // If user wants to user the points type thumbnail, then override it
        if( $reward['points_type_thumbnail'] ) {

            $points_type_thumbnail = gamipress_get_points_type_thumbnail( $reward['points_type'], ( $thumbnail_size === null ? 'gamipress-points' : $thumbnail_size ) );

            if( $points_type_thumbnail ) {
                $reward['thumbnail'] = $points_type_thumbnail;
            }
        }

    } else if( $reward['reward_type'] === 'achievement' ) {

        $reward['achievement']             = absint( gamipress_get_post_meta( $reward_id, '_gamipress_achievement' ) );
        $reward['achievement_thumbnail']   = absint( gamipress_get_post_meta( $reward_id, '_gamipress_achievement_thumbnail' ) );

        // If user wants to user the achievement thumbnail, then override it
        if( $reward['achievement'] && $reward['achievement_thumbnail'] ) {

            $achievement_thumbnail = gamipress_get_achievement_post_thumbnail( $reward['achievement'], ( $thumbnail_size === null ? 'gamipress-achievement' : $thumbnail_size ) );

            if( $achievement_thumbnail ) {
                $reward['thumbnail'] = $achievement_thumbnail;
            }
        }

    } else if( $reward['reward_type'] === 'rank' ) {

        $reward['rank']             = absint( gamipress_get_post_meta( $reward_id, '_gamipress_rank' ) );
        $reward['rank_thumbnail']   = absint( gamipress_get_post_meta( $reward_id, '_gamipress_rank_thumbnail' ) );

        // If user wants to user the rank thumbnail, then override it
        if( $reward['rank'] && $reward['rank_thumbnail'] ) {

            $rank_thumbnail = gamipress_get_rank_post_thumbnail( $reward['rank'], ( $thumbnail_size === null ? 'gamipress-rank' : $thumbnail_size ) );

            if( $rank_thumbnail ) {
                $reward['thumbnail'] = $rank_thumbnail;
            }
        }

    }

    // Available filter for overriding elsewhere
    return apply_filters( 'gamipress_daily_login_rewards_reward_object', $reward, $reward_id );

}