<?php
/**
 * Content Filters
 *
 * @package     GamiPress\Daily_Login_Rewards\Content_Filters
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Renders the properly pop-up at footer
 *
 * @since  1.0.0
 */
function gamipress_daily_login_rewards_maybe_render_popups() {

    // Bail if user is not logged in
    if( ! is_user_logged_in() ) {
        return;
    }

    // Bail if admin area
    if( is_admin() ) {
        return;
    }

    // Bail if user is visiting a rewards calendar
    if( is_singular( 'rewards-calendar' ) ) {
        return;
    }

    $prefix = '_gamipress_daily_login_rewards_';
    $user_id = get_current_user_id();

    $calendars_to_show = gamipress_get_user_meta( $user_id, $prefix . 'rewards_calendars_to_show' );

    if( $calendars_to_show && is_array( $calendars_to_show ) ) :

        // Popup display options
        $button_text = gamipress_daily_login_rewards_get_option( 'popup_button_text', __( 'Ok!', 'gamipress-daily-login-rewards' ) );
        $title = (bool) gamipress_daily_login_rewards_get_option( 'popup_show_title', false );
        $excerpt = (bool) gamipress_daily_login_rewards_get_option( 'popup_show_excerpt', false );

        foreach( $calendars_to_show as $rewards_calendar_id ) : ?>

            <div id="gamipress-daily-login-popup-<?php echo $rewards_calendar_id; ?>" class="gamipress-daily-login-popup" style="display: none;">

                <?php echo gamipress_do_shortcode( 'gamipress_rewards_calendar', array(
                    'id'        => $rewards_calendar_id,
                    'title'     => ( $title ? 'yes' : 'no' ),
                    'excerpt'   => ( $excerpt ? 'yes' : 'no' ),
                ) ); ?>

                <div class="gamipress-daily-login-popup-button-wrapper">
                    <button type="button" class="gamipress-daily-login-popup-button"><?php echo $button_text; ?></button>
                </div>

            </div>

        <?php endforeach; ?>

        <div class="gamipress-daily-login-overlay" style="display: none;"></div>

    <?php endif;

}
add_action( 'wp_footer', 'gamipress_daily_login_rewards_maybe_render_popups' );

/**
 * Filter rewards calendar content to add the calendar table
 *
 * @since  1.0.0
 *
 * @param  string $content The page content
 *
 * @return string          The page content after reformat
 */
function gamipress_daily_login_rewards_reformat_entries( $content ) {

    // Filter, but only on the main loop!
    if ( ! gamipress_daily_login_rewards_is_main_loop( get_the_ID() ) )
        return $content;

    // now that we're where we want to be, tell the filters to stop removing
    $GLOBALS['gamipress_daily_login_rewards_reformat_content'] = true;

    global $gamipress_daily_login_rewards_template_args;

    // Initialize template args global
    $gamipress_daily_login_rewards_template_args = array();

    $gamipress_daily_login_rewards_template_args['original_content'] = $content;

    ob_start();

    gamipress_get_template_part( 'single-rewards-calendar' );

    $new_content = ob_get_clean();

    // Ok, we're done reformatting
    $GLOBALS['gamipress_daily_login_rewards_reformat_content'] = false;

    return $new_content;
}
add_filter( 'the_content', 'gamipress_daily_login_rewards_reformat_entries', 9 );

/**
 * Helper function tests that we're in the main loop
 *
 * @since  1.0.0
 * @param  bool|integer $id The page id
 * @return boolean     A boolean determining if the function is in the main loop
 */
function gamipress_daily_login_rewards_is_main_loop( $id = false ) {

    // only run our filters on the gamipress leaderboard singular pages
    if ( is_admin() || ! is_singular( 'rewards-calendar' ) )
        return false;
    // w/o id, we're only checking template context
    if ( ! $id )
        return true;

    // Checks several variables to be sure we're in the main loop (and won't effect things like post pagination titles)
    return ( ( $GLOBALS['post']->ID == $id ) && in_the_loop() && empty( $GLOBALS['gamipress_daily_login_rewards_reformat_content'] ) );
}