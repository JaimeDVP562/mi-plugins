<?php
/**
 * Functions
 * 
 * @package GamiPress\No_Activity_Rewards\Functions
 * @since  1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Update activity
 * 
 * @since 1.0.0
 */
function gamipress_nar_update_activity( $user_id, $meta_key ) {
    update_user_meta( $user_id, $meta_key, time() );
}
// Last Login
add_action( 'wp_login', function( $user_login, $user ) {
    gamipress_nar_update_activity( $user->ID, '_gp_nar_last_login' );
}, 10, 2 );

// Last Achievement
add_action( 'gamipress_award_achievement', function( $user_id ) {
    gamipress_nar_update_activity( $user_id, '_gp_nar_last_achievement' );
}, 10 );
// Last Points
add_action( 'gamipress_award_points', function( $user_id ) {
    gamipress_nar_update_activity( $user_id, '_gp_nar_last_points' );
}, 10 );



/**
 * Check inactivity function
 * 
 * @since 1.0.0
 * @return void
 */
function gamipress_nar_check_inactivity() {
    global $wpdb;

    $check_map = array(
        'gamipress_nar_user_has_not_logged_in'              => '_gp_nar_last_login',
        'gamipress_nar_user_has_not_earned_any_achievement' => '_gp_nar_last_achievement',
        'gamipress_nar_user_has_not_earned_points'          => '_gp_nar_last_points',
    );

    foreach ( $check_map as $trigger => $meta_key ) {
        

        $all_days = $wpdb->get_col( $wpdb->prepare( "
            SELECT DISTINCT pm_days.meta_value 
            FROM $wpdb->postmeta AS pm_trigger
            INNER JOIN $wpdb->postmeta AS pm_days ON pm_trigger.post_id = pm_days.post_id
            WHERE pm_trigger.meta_key = '_gamipress_action' 
            AND pm_trigger.meta_value = %s
            AND pm_days.meta_key = '_gamipress_inactivity_days'
        ", $trigger ) );

        if ( empty( $all_days ) ) continue;

        foreach ( $all_days as $days_limit ) {
            $days_limit = absint( $days_limit );
            $threshold = time() - ( $days_limit * DAY_IN_SECONDS );


            $user_ids = $wpdb->get_col( $wpdb->prepare( "
                SELECT u.ID FROM $wpdb->users u
                LEFT JOIN $wpdb->usermeta um ON (u.ID = um.user_id AND um.meta_key = %s)
                WHERE (
                    um.meta_value <= %d -- Tienen actividad antigua
                    OR (um.meta_value IS NULL AND UNIX_TIMESTAMP(u.user_registered) <= %d) -- Nunca han tenido actividad
                )
                LIMIT 100
            ", $meta_key, $threshold, $threshold ) );

            if ( empty( $user_ids ) ) continue;

            foreach ( $user_ids as $user_id ) {
                $last_activity = (int) get_user_meta( $user_id, $meta_key, true );
                
                if ( ! $last_activity ) {
                    $userdata = get_userdata( $user_id );
                    $last_activity = strtotime( $userdata->user_registered );
                }

                $inactive_days = floor( ( time() - $last_activity ) / DAY_IN_SECONDS );

                gamipress_trigger_event( array(
                    'event'   => $trigger,
                    'user_id' => $user_id,
                    'args'    => array( $trigger, $user_id, $inactive_days, $last_activity ),
                ) );


                gamipress_nar_update_activity( $user_id, $meta_key );
            }
        }
    }
}
add_action( 'gamipress_no_activity_rewards_daily_check', 'gamipress_nar_check_inactivity' );




/**
 * Check get required days function
 * 
 * @since 1.0.0
 * @return int $days
 */
function gamipress_nar_get_required_days( $trigger ) {
    global $wpdb;
    
    $days = $wpdb->get_var( $wpdb->prepare( "
        SELECT pm_days.meta_value 
        FROM $wpdb->postmeta AS pm_trigger
        INNER JOIN $wpdb->postmeta AS pm_days ON pm_trigger.post_id = pm_days.post_id
        WHERE pm_trigger.meta_key = '_gamipress_action' 
        AND pm_trigger.meta_value = %s
        AND pm_days.meta_key = '_gamipress_inactivity_days'
        LIMIT 1
    ", $trigger ) );

    return $days ? absint( $days ) : false;
}