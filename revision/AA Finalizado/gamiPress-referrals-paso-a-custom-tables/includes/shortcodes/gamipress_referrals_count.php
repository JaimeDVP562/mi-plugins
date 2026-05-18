<?php
/**
 * GamiPress Referrals Count Shortcode
 *
 * @package     GamiPress\Referrals\Shortcodes\Shortcode\GamiPress_Referrals_Count
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_referrals_count] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_referrals_count_shortcode() {

    gamipress_register_shortcode( 'gamipress_referrals_count', array(
        'name'              => __( 'Referrals count', 'gamipress-referrals' ),
        'description'       => __( 'Render user\'s referrals count.', 'gamipress-referrals' ),
        'output_callback'   => 'gamipress_referrals_count_shortcode',
        'icon'              => 'groups',
        'fields'            => array(

            'type' => array(
                'name'        => __( 'Type', 'gamipress-referrals' ),
                'description' => __( 'Referrals to count.', 'gamipress-referrals' ),
                'type' 		  => 'select',
                'options_cb'  => 'gamipress_referrals_count_shortcode_type_options_cb',
                'default' 	  => 'visits',
            ),
            'current_user' => array(
                'name'        => __( 'Current User', 'gamipress-referrals' ),
                'description' => __( 'Referrals count of the current logged in user.', 'gamipress-referrals' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
                'default' 	  => 'yes',
            ),
            'user_id' => array(
                'name'        => __( 'User', 'gamipress-referrals' ),
                'description' => __( 'Referrals count of a specific user.', 'gamipress-referrals' ),
                'type'        => 'select',
                'classes' 	  => 'gamipress-user-selector',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_users'
            ),


        ),
    ) );

}
add_action( 'init', 'gamipress_register_referrals_count_shortcode' );

/**
 * Get the referrals count shortcode types
 *
 * @since  1.0.6
 *
 * @return array
 */
function gamipress_referrals_count_shortcode_type_options_cb( $field = null ) {

    $types = array(
        'visits' => __( 'Referral visits', 'gamipress-referrals' ),
        'signups' => __( 'Referral sign ups', 'gamipress-referrals' ),
    );

    if( gamipress_referrals_enable_sales() ) {
        $types['sales'] = __( 'Referral sales', 'gamipress-referrals' );
        $types['sales_refunded'] = __( 'Referral sales refunded', 'gamipress-referrals' );
    }

    return $types;

}

/**
 * Affiliate ID Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_referrals_count_shortcode( $atts = array() ) {

    $atts = shortcode_atts( array(

        'type'              => 'visits',
        'current_user'      => 'yes',
        'user_id'           => '0',

    ), $atts, 'gamipress_referrals_count' );

    // Force to set current user as user ID
    if( $atts['current_user'] === 'yes' ) {
        $atts['user_id'] = get_current_user_id();
    }

    // ---------------------------
    // Shortcode Errors
    // ---------------------------

    $allowed_types = gamipress_referrals_count_shortcode_type_options_cb();

    if ( ! in_array( $atts['type'], array_keys( $allowed_types ) ) )
        return gamipress_shortcode_error( __( 'Please, provide a valid type.', 'gamipress-referrals' ), 'gamipress_referrals_count' );

    // Return if user id not specified
    if ( $atts['current_user'] === 'no' && absint( $atts['user_id'] ) === 0 )
        return gamipress_shortcode_error( __( 'Please, provide the user id.', 'gamipress-referrals' ), 'gamipress_referrals_count' );

    $affiliate = gamipress_referrals_get_affiliate( $atts['user_id'] );

    if( $atts['current_user'] === 'no' && ! $affiliate )
        return gamipress_shortcode_error( __( 'Please, provide a valid user.', 'gamipress-referrals' ), 'gamipress_referrals_count' );

    // ---------------------------
    // Shortcode Processing
    // ---------------------------

    $output = gamipress_referrals_get_referrals_count( absint( $atts['user_id'] ), $atts['type'] );

    /**
     * Filter to return a custom output
     *
     * @since 1.0.0
     *
     * @param string            $output             Shortcode output (the referrals count)
     * @param int               $user_id            Affiliate ID
     * @param WP_User|false     $affiliate          Affiliate object
     * @param array             $atts               Shortcode attributes
     *
     * @return string
     */
    $output = apply_filters( 'gamipress_referrals_count_shortcode_output', $output, $atts['user_id'], $affiliate, $atts );

    // Return shortcode output
    return $output;

}