<?php
/**
 * User Earnings
 *
 * @package GamiPress\Daily_Login_Rewards\User_Earnings
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Columns rendering for user earnings list view
 *
 * @since 1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function gamipress_daily_login_rewards_manage_user_earnings_custom_column( $column_name, $object_id ) {

    $user_earning = ct_get_object( $object_id );

    if( ! in_array( $user_earning->post_type, array( 'rewards-calendar', 'calendar-reward' ) ) ) {
        return;
    }

    $can_manage = current_user_can( gamipress_get_manager_capability() );

    switch( $column_name ) {
        case 'name':

            if( $user_earning->post_type === 'calendar-reward' ) :

                $reward = gamipress_daily_login_rewards_get_reward_object( $user_earning->post_id, 32 );
                $calendar_title = '<a href="' . ( $can_manage ? get_edit_post_link( $reward['rewards_calendar'] ) : get_post_permalink( $reward['rewards_calendar'] ) ) . '">' . gamipress_get_post_field( 'post_title', $reward['rewards_calendar'] ) . '</a>'; ?>

                <?php // Reward thumbnail ?>
                <?php if( $reward['thumbnail'] ) :
                    echo $reward['thumbnail'];
                endif; ?>

                <?php // Reward title ?>
                <strong><?php echo sprintf( __( 'Day %d', 'gamipress-daily-login-reward' ), $reward['order'] ) . ' - ' . $reward['label']; ?></strong>
                <br>
                <?php // Reward description ?>
                <?php echo __( 'Login Reward', 'gamipress-daily-login-reward' ) . ', ' . __( 'Calendar:', 'gamipress-daily-login-reward' ) . ' ' . $calendar_title; ?>

            <?php elseif( $user_earning->post_type === 'rewards-calendar' ) :

                $calendar_title = '<a href="' . ( $can_manage ? get_edit_post_link( $user_earning->post_id ) : get_post_permalink( $user_earning->post_id ) ) . '">' . gamipress_get_post_field( 'post_title', $user_earning->post_id ) . '</a>'; ?>

                <?php // Calendar thumbnail ?>
                <?php echo get_the_post_thumbnail( $user_earning->post_id, array( 32, 32 ) ); ?>

                <?php // Calendar title ?>
                <strong><?php echo $calendar_title; ?></strong>
                <br>
                <?php // Reward description ?>
                <?php echo __( 'Rewards Calendar', 'gamipress-daily-login-reward' ); ?>

            <?php endif;

            break;
    }

}
add_action( 'manage_gamipress_user_earnings_custom_column', 'gamipress_daily_login_rewards_manage_user_earnings_custom_column', 10, 2 );

/**
 * Render earnings column
 *
 * @since 1.0.0
 *
 * @param string    $column_output  Default column output
 * @param string    $column_name    The column name
 * @param stdClass  $user_earning   The column name
 * @param array     $template_args  Template received arguments
 *
 * @return string
 */
function gamipress_daily_login_rewards_earnings_render_column( $column_output, $column_name, $user_earning, $template_args ) {

    if( ! in_array( $user_earning->post_type, array( 'rewards-calendar', 'calendar-reward' ) ) ) {
        return $column_output;
    }

    // Setup vars
    $earning_title = '';
    $earning_description = '';

    switch( $column_name ) {
        case 'thumbnail':

            if( $user_earning->post_type === 'calendar-reward' ) {
                // Calendar reward

                $reward = gamipress_daily_login_rewards_get_reward_object( $user_earning->post_id );

                // Get the reward thumbnail
                $column_output = ( $reward['thumbnail'] ? $reward['thumbnail'] : '' );

            } else if( $user_earning->post_type === 'rewards-calendar' ) {
                // Rewards calendar

                // Get the calendar thumbnail
                $column_output = get_the_post_thumbnail( $user_earning->post_id );

            }

            break;
        case 'description':

            if( $user_earning->post_type === 'calendar-reward' ) {
                // Calendar reward

                $reward = gamipress_daily_login_rewards_get_reward_object( $user_earning->post_id );

                // Calendar reward title
                $earning_title = sprintf( __( 'Day %d', 'gamipress-daily-login-reward' ), $reward['order'] ) . ' - ' . $reward['label'];

                // Build a description based on if rewards calendar is public or not
                if( (bool) gamipress_daily_login_rewards_get_option( 'public', false ) ) {

                    $earning_description = sprintf( '%s, %s: <a href="%s">%s</a>',
                        __( 'Login Reward', 'gamipress-daily-login-reward' ) ,
                        __( 'Calendar', 'gamipress-daily-login-reward' ),
                        get_post_permalink( $reward['rewards_calendar'] ),
                        gamipress_get_post_field( 'post_title', $reward['rewards_calendar'] )
                    );

                } else {

                    $earning_description = sprintf( '%s, %s: %s',
                        __( 'Login Reward', 'gamipress-daily-login-reward' ) ,
                        __( 'Calendar', 'gamipress-daily-login-reward' ),
                        gamipress_get_post_field( 'post_title', $reward['rewards_calendar'] )
                    );

                }

            } else if( $user_earning->post_type === 'rewards-calendar' ) {
                // Rewards calendar

                // Build a title based on if rewards calendar is public or not
                if( (bool) gamipress_daily_login_rewards_get_option( 'public', false ) ) {

                    $earning_title = sprintf( '<a href="%s">%s</a>',
                        get_post_permalink( $user_earning->post_id ),
                        gamipress_get_post_field( 'post_title', $user_earning->post_id )
                    );

                } else {

                    $earning_title = gamipress_get_post_field( 'post_title', $user_earning->post_id );

                }

                // Rewards calendar description
                $earning_description = __( 'Rewards Calendar', 'gamipress-daily-login-reward' );

            }

            $column_output = sprintf( '<strong class="gamipress-earning-title">%s</strong>'
                . '<br>'
                . '<span class="gamipress-earning-description">%s</span>',
                $earning_title,
                $earning_description
            );

            break;
    }

    return $column_output;
}
add_filter( 'gamipress_earnings_render_column', 'gamipress_daily_login_rewards_earnings_render_column', 10, 4 );