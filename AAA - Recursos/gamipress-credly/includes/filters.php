<?php
/**
 * Filters
 *
 * @package GamiPress\Credly\Filters
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Save post listener
 *
 * @since 1.0.0
 *
 * @param int     $post_ID Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated or not.
 */
function gamipress_credly_on_save_achievement( $post_ID, $post, $update ) {

    if( ! in_array( $post->post_type, gamipress_get_achievement_types_slugs() ) ) {
        return;
    }

    gamipress_credly_sync_achievement( $post_ID, true );

}
add_action( 'save_post', 'gamipress_credly_on_save_achievement', 10, 3 );

/**
 * Delete post listener
 *
 * @since 1.0.0
 *
 * @param int     $post_ID Post ID.
 */
function gamipress_credly_on_delete_achievement( $post_ID ) {

    if( ! in_array( gamipress_get_post_field( 'post_type', $post_ID ), gamipress_get_achievement_types_slugs() ) ) {
        return;
    }

    gamipress_credly_desync_achievement( $post_ID );

}
add_action( 'delete_post', 'gamipress_credly_on_delete_achievement' );


/**
 * Sync badges after login
 *
 * @since 1.0.0
 *
 * @param string  $user_login Username.
 * @param WP_User $user       WP_User object of the logged-in user.
 */
function gamipress_credly_sync_badges_after_login( $user_login, $user = null ) {

    if( (bool) gamipress_credly_get_option( 'auto_sync_users', false ) ) {

        $prefix = '_gamipress_credly_';
        $sync_date = gamipress_get_user_meta( $user->ID, $prefix . 'sync_date', true );
        $remote_id = gamipress_get_user_meta( $user->ID, $prefix . 'remote_id', true );

        if ( empty( $remote_id ) ) {
            return;
        }

        if ( empty( $sync_date ) ){

            $date = current_time( 'Y-m-d H:i:s O' );
            gamipress_update_user_meta( $user->ID, $prefix . 'sync_date', $date );
            
            // Sync earned badges in Gamipress
            gamipress_credly_auto_sync_badges( $user->ID );

        }
    }

}
add_action( 'wp_login', 'gamipress_credly_sync_badges_after_login', 10, 2 );

/**
 * Display sync status with
 *
 * @since  1.0.0
 *
 * @param  object $user The current user's $user object
 */
function gamipress_credly_user_profile_data( $user = null ) {

    $prefix = '_gamipress_credly_'; ?>

    <?php // Verify user meets minimum role to see this information
    if ( current_user_can( gamipress_get_manager_capability() ) ) : ?>

        <h2><?php _e( 'Credly', 'gamipress-credly' ); ?></h2>

        <table class="form-table">

        <tr>
            <th><label><?php _e( 'Account Sync Status:', 'gamipress-credly' ); ?></label></th>
            <td>
                <?php if( ! empty( gamipress_get_user_meta( $user->ID, $prefix . 'remote_id', true ) ) ) : ?>
                    <span style="color: #37863e;"><?php _e( 'Connected', 'gamipress-credly' ); ?></span>
                <?php else : ?>
                    <span style="color: #a00;"><?php _e( 'Not connected', 'gamipress-credly' ); ?></span>
                <?php endif; ?>
            </td>
        </tr>

        </table>

        <hr>

    <?php endif; ?>

    <?php

}
add_action( 'show_user_profile', 'gamipress_credly_user_profile_data' );
add_action( 'edit_user_profile', 'gamipress_credly_user_profile_data' );

/**
 * User earning listener
 *
 * @since 1.0.0
 *
 * @param int       $user_id
 * @param int       $achievement_id
 * @param string    $trigger_type
 * @param int       $site_id
 * @param array     $args
 */
function gamipress_credly_award_achievement_listener( $user_id, $achievement_id, $trigger_type, $site_id, $args ) {

    if( ! in_array( gamipress_get_post_field( 'post_type', $achievement_id ), gamipress_get_achievement_types_slugs() ) ) {
        return;
    }

    gamipress_credly_sync_user_earning( $user_id, $achievement_id );

}
add_action( 'gamipress_award_achievement', 'gamipress_credly_award_achievement_listener', 10, 5 );


/**
 * User revoke listener
 *
 *  @since 1.0.0
 *
 * @param int       $user_id
 * @param int       $achievement_id
 * @param int       $earning_id
 */
function gamipress_credly_revoke_achievement_listener( $user_id, $achievement_id, $earning_id ) {

    if( ! in_array( gamipress_get_post_field( 'post_type', $achievement_id ), gamipress_get_achievement_types_slugs() ) ) {
        return;
    }

    gamipress_credly_desync_user_earning( $user_id, $achievement_id );

}
add_action( 'gamipress_revoke_achievement_to_user', 'gamipress_credly_revoke_achievement_listener', 10, 3 );