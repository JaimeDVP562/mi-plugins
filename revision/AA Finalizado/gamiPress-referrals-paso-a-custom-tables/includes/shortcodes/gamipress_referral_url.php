<?php
/**
 * GamiPress Referral URL Shortcode
 *
 * @package     GamiPress\Referrals\Shortcodes\Shortcode\GamiPress_Referral_URL
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_referral_url] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_referral_url_shortcode() {

    gamipress_register_shortcode( 'gamipress_referral_url', array(
        'name'              => __( 'Referral URL', 'gamipress-referrals' ),
        'description'       => __( 'Render a referral URL of current page or from the given URL.', 'gamipress-referrals' ),
        'output_callback'   => 'gamipress_referral_url_shortcode',
        'icon'              => 'admin-links',
        'fields'            => array(

            'url' => array(
                'name'        => __( 'URL', 'gamipress-referrals' ),
                'description' => __( 'URL to append the referral code. If empty, will render the URL of the current page.', 'gamipress-referrals' ),
                'type' 		  => 'text',
                'default' 	  => '',
            ),
            'current_user' => array(
                'name'        => __( 'Current User', 'gamipress-referrals' ),
                'description' => __( 'Append referral code of the current logged in user.', 'gamipress-referrals' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
                'default' 	  => 'yes',
            ),
            'user_id' => array(
                'name'        => __( 'User', 'gamipress-referrals' ),
                'description' => __( 'Append referral code of a specific user.', 'gamipress-referrals' ),
                'type'        => 'select',
                'classes' 	  => 'gamipress-user-selector',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_users'
            ),

        ),
    ) );

}
add_action( 'init', 'gamipress_register_referral_url_shortcode' );

/**
 * Affiliate ID Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_referral_url_shortcode( $atts = array() ) {

    $atts = shortcode_atts( array(
        'url'               => '',
        'current_user'      => 'yes',
        'user_id'           => '0',
    ), $atts, 'gamipress_referral_url' );

    // Force to set current user as user ID
    if( $atts['current_user'] === 'yes' ) {
        $atts['user_id'] = get_current_user_id();
    }

    // ---------------------------
    // Shortcode Errors
    // ---------------------------

    // Return if user id not specified
    if ( $atts['current_user'] === 'no' && absint( $atts['user_id'] ) === 0 )
        return gamipress_shortcode_error( __( 'Please, provide the user id.', 'gamipress-referrals' ), 'gamipress_referral_url' );

    $affiliate = gamipress_referrals_get_affiliate( $atts['user_id'] );

    if( $atts['current_user'] === 'no' && ! $affiliate )
        return gamipress_shortcode_error( __( 'Please, provide a valid user.', 'gamipress-referrals' ), 'gamipress_referral_url' );

    // ---------------------------
    // Shortcode Processing
    // ---------------------------

    $referral_id = gamipress_referrals_get_affiliate_referral_id( $affiliate );
    $output = '';

    if( $affiliate ) {

        $url_parameter = gamipress_referrals_get_option( 'url_parameter', 'ref' );

        if ( ! empty( $atts['url'] ) ) {
            // Append to specific URL
            $output = add_query_arg( array( $url_parameter => $referral_id ), $atts['url'] );
        } else {

            // Append to the current URL
            $output = add_query_arg( array( $url_parameter => $referral_id ), gamipress_referrals_get_current_page_url() );
        }

        // Sanitize URL
        $output = esc_url( $output );

    }


    /**
     * Filter to return a custom output
     *
     * @since 1.0.0
     *
     * @param string            $output             Shortcode output (the referral url)
     * @param int               $user_id            Affiliate ID
     * @param WP_User|false     $affiliate          Affiliate object
     * @param array             $atts               Shortcode attributes
     *
     * @return string
     */
    $output = apply_filters( 'gamipress_referral_url_shortcode_output', $output, $atts['user_id'], $affiliate, $atts );

    // Return shortcode output
    return $output;

}