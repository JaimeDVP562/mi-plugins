<?php
/**
 * GamiPress Affiliate ID Shortcode
 *
 * @package     GamiPress\Referrals\Shortcodes\Shortcode\GamiPress_Affiliate_ID
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_affiliate_id] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_affiliate_id_shortcode() {

    gamipress_register_shortcode( 'gamipress_affiliate_id', array(
        'name'              => __( 'Affiliate ID', 'gamipress-referrals' ),
        'description'       => __( 'Render user\'s affiliate ID.', 'gamipress-referrals' ),
        'output_callback'   => 'gamipress_affiliate_id_shortcode',
        'icon'              => 'admin-users',
        'fields'            => array(

            'current_user' => array(
                'name'        => __( 'Current User', 'gamipress-referrals' ),
                'description' => __( 'Show affiliate ID of the current logged in user.', 'gamipress-referrals' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
                'default' 	  => 'yes',
            ),
            'user_id' => array(
                'name'        => __( 'User', 'gamipress-referrals' ),
                'description' => __( 'Show affiliate ID of a specific user.', 'gamipress-referrals' ),
                'type'        => 'select',
                'classes' 	  => 'gamipress-user-selector',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_users'
            ),

        ),
    ) );

}
add_action( 'init', 'gamipress_register_affiliate_id_shortcode' );

/**
 * Affiliate ID Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_affiliate_id_shortcode( $atts = array() ) {

    $atts = shortcode_atts( array(
        'current_user'      => 'yes',
        'user_id'           => '0',
    ), $atts, 'gamipress_affiliate_id' );

    // Force to set current user as user ID
    if( $atts['current_user'] === 'yes' ) {
        $atts['user_id'] = get_current_user_id();
    }

    // ---------------------------
    // Shortcode Errors
    // ---------------------------

    // Return if user id not specified
    if ( $atts['current_user'] === 'no' && absint( $atts['user_id'] ) === 0 )
        return gamipress_shortcode_error( __( 'Please, provide the user id.', 'gamipress-referrals' ), 'gamipress_affiliate_id' );

    $affiliate = gamipress_referrals_get_affiliate( $atts['user_id'] );

    if( $atts['current_user'] === 'no' && ! $affiliate )
        return gamipress_shortcode_error( __( 'Please, provide a valid user.', 'gamipress-referrals' ), 'gamipress_affiliate_id' );

    // ---------------------------
    // Shortcode Processing
    // ---------------------------

    $output = gamipress_referrals_get_affiliate_referral_id( $affiliate );

    /**
     * Filter to return a custom output
     *
     * @since 1.0.0
     *
     * @param string            $output             Shortcode output (the affiliate ID based on affiliate links setting)
     * @param int               $user_id            Affiliate ID
     * @param WP_User|false     $affiliate          Affiliate object
     * @param array             $atts               Shortcode attributes
     *
     * @return string
     */
    $output = apply_filters( 'gamipress_affiliate_id_shortcode_output', $output, $atts['user_id'], $affiliate, $atts );

    // Return shortcode output
    return $output;

}