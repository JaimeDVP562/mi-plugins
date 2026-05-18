<?php
/**
 * GamiPress Rewards Calendar Shortcode
 *
 * @package     GamiPress\Daily_Login_Rewards\Shortcodes\Shortcode\GamiPress_Rewards_Calendar
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_rewards_calendar] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_rewards_calendar_shortcode() {
    gamipress_register_shortcode( 'gamipress_rewards_calendar', array(
        'name'              => __( 'Rewards Calendar', 'gamipress-daily-login-rewards' ),
        'description'       => __( 'Render the desired rewards calendar.', 'gamipress-daily-login-rewards' ),
        'output_callback'   => 'gamipress_rewards_calendar_shortcode',
        'icon'              => 'calendar-alt',
        'fields'            => array(
            'id' => array(
                'name' => __( 'Rewards Calendar', 'gamipress-daily-login-rewards' ),
                'desc' => __( 'Choose the rewards calendar to display.', 'gamipress-daily-login-rewards' ),
                'type'        => 'select',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_posts'
            ),
            'title' => array(
                'name'        => __( 'Show Title', 'gamipress-daily-login-rewards' ),
                'description' => __( 'Display the rewards calendar title.', 'gamipress-daily-login-rewards' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
            'excerpt' => array(
                'name'        => __( 'Show Excerpt', 'gamipress-daily-login-rewards' ),
                'description' => __( 'Display the rewards calendar short description.', 'gamipress-daily-login-rewards' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
            'columns' => array(
                'name' 	        => __( 'Columns', 'gamipress-daily-login-rewards' ),
                'desc' 	        => __( 'Columns to divide calendar rewards.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'select',
                'options' 	    => array(
                    '1' => __( '1 Column', 'gamipress-daily-login-rewards' ),
                    '2' => __( '2 Columns', 'gamipress-daily-login-rewards' ),
                    '3' => __( '3 Columns', 'gamipress-daily-login-rewards' ),
                    '4' => __( '4 Columns', 'gamipress-daily-login-rewards' ),
                    '5' => __( '5 Columns', 'gamipress-daily-login-rewards' ),
                    '6' => __( '6 Columns', 'gamipress-daily-login-rewards' ),
                    '7' => __( '7 Columns', 'gamipress-daily-login-rewards' ),
                ),
            ),
            'image_size' => array(
                'name' 	        => __( 'Image Size', 'gamipress-daily-login-rewards' ),
                'desc' 	        => __( 'Size of the rewards images.', 'gamipress-daily-login-rewards' ),
                'type' 	        => 'text',
                'attributes' 	=> array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
                'default'    => 100,
            ),
        ),
    ) );
}
add_action( 'init', 'gamipress_register_rewards_calendar_shortcode' );

/**
 * Rewards Calendar Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_rewards_calendar_shortcode( $atts = array() ) {

    global $post, $gamipress_daily_login_rewards_template_args;

    // Rewards calendar post vars
    $rewards_calendar_id = isset( $atts['id'] ) && ! empty( $atts['id'] ) ? $atts['id'] : get_the_ID();
    $rewards_calendar_post = get_post( $rewards_calendar_id );

    // Return if rewards calendar post does not exists
    if( ! $rewards_calendar_post ) {
        return;
    }

    // Return if not is a rewards calendar
    if( $rewards_calendar_post->post_type !== 'rewards-calendar' ) {
        return;
    }

    // Return if rewards calendar was not published
    if( $rewards_calendar_post->post_status !== 'publish' ) {
        return;
    }

    // Fields prefix
    $prefix = '_gamipress_daily_login_rewards_';

    $atts = shortcode_atts( array(

        'title'         => 'yes',
        'id'            => $rewards_calendar_id,
        'excerpt'       => 'yes',
        'columns'       => gamipress_get_post_meta( $rewards_calendar_id, $prefix . 'columns' ),
        'image_size'    => absint( gamipress_get_post_meta( $rewards_calendar_id, $prefix . 'image_size' ) ),

    ), $atts, 'gamipress_rewards_calendar' );

    // Initialize template args
    $gamipress_daily_login_rewards_template_args = array();

    $gamipress_daily_login_rewards_template_args = $atts;

    // Enqueue assets
    gamipress_daily_login_rewards_enqueue_scripts();

    $post = $rewards_calendar_post;

    setup_postdata( $post );

    // Render the rewards calendar
    ob_start();
    gamipress_get_template_part( 'rewards-calendar' );
    $output = ob_get_clean();

    wp_reset_postdata();

    // Return our rendered rewards calendar
    return $output;

}
