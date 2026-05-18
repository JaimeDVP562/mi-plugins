<?php
/**
 * GamiPress Referral URL Generator Shortcode
 *
 * @package     GamiPress\Referrals\Shortcodes\Shortcode\GamiPress_Referral_URL_Generator
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_referral_url_generator] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_referral_url_generator_shortcode() {

    gamipress_register_shortcode( 'gamipress_referral_url_generator', array(
        'name'              => __( 'Referral URL Generator', 'gamipress-referrals' ),
        'description'       => __( 'Render a referral URL generator form.', 'gamipress-referrals' ),
        'output_callback'   => 'gamipress_referral_url_generator_shortcode',
        'icon'              => 'admin-links',
        'fields'            => array(

            'url' => array(
                'name'        => __( 'URL', 'gamipress-referrals' ),
                'description' => __( 'URL to initialize the URL field of the generator. If empty, will use the URL of the current page.', 'gamipress-referrals' ),
                'type' 		  => 'text',
                'default' 	  => '',
            ),
            'button_text' => array(
                'name'        => __( 'Button Text', 'gamipress-referrals' ),
                'description' => __( 'Generate URL button text.', 'gamipress-referrals' ),
                'type' 	=> 'text',
                'default' => __( 'Generate URL', 'gamipress-referrals' )
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_register_referral_url_generator_shortcode' );

/**
 * Affiliate ID Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_referral_url_generator_shortcode( $atts = array() ) {

    global $gamipress_referrals_template_args;

    $atts = shortcode_atts( array(
        'url'               => '',
        'button_text' 	    => __( 'Generate URL', 'gamipress-referrals' ),
    ), $atts, 'gamipress_referral_url_generator' );


    // Bail if user is not logged in
    if( ! is_user_logged_in() ) return '';

    $affiliate = gamipress_referrals_get_affiliate_by_user_id( get_current_user_id() );

    // Bail if can't setup the affiliate object
    if( ! $affiliate ) return '';

    if( empty( $atts['url'] ) )
        $atts['url'] = gamipress_referrals_get_current_page_url();

    // Setup template args
    $gamipress_referrals_template_args = $atts;

    // Enqueue assets
    gamipress_referrals_enqueue_scripts();

    ob_start();
    gamipress_get_template_part( 'referral-url-generator' );
    $output = ob_get_clean();

    // Return shortcode output
    return $output;

}