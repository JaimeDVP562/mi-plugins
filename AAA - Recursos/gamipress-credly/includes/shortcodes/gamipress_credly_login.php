<?php
/**
 * GamiPress Credly login Shortcode
 *
 * @package     GamiPress\Credly\Shortcodes\Shortcode\GamiPress_Redeem_Coupon
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_credly_login] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_credly_register_login_shortcode() {

    gamipress_register_shortcode( 'gamipress_credly_login', array(
        'name'              => __( 'Credly Login', 'gamipress-credly' ),
        'description'       => __( 'Render form to let user sync his account with Credly.', 'gamipress-credly' ),
        'output_callback'   => 'gamipress_credly_login_shortcode',
        'icon'              => 'awards',
        'fields'            => array(
            'label' => array(
                'name'        => __( 'Email Label Text', 'gamipress-credly' ),
                'description' => __( 'Email input label text.', 'gamipress-credly' ),
                'type' 	=> 'text',
                'default' => __( 'Enter your Credly email:', 'gamipress-credly' )
            ),
            'button_text' => array(
                'name'        => __( 'Button Text', 'gamipress-credly' ),
                'description' => __( 'Form button text.', 'gamipress-credly' ),
                'type' 	=> 'text',
                'default' => __( 'Sync', 'gamipress-credly' )
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_credly_register_login_shortcode' );

/**
 * Redeem Coupon Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_credly_login_shortcode( $atts = array() ) {

    global $gamipress_credly_template_args;

    // Get the shortcode attributes
    $atts = shortcode_atts( array(

        'label'         => __( 'Enter your Credly email:', 'gamipress-credly' ),
        'button_text'   => __( 'Save email', 'gamipress-credly' ),

    ), $atts, 'gamipress_credly_login' );

    $auth = gamipress_credly_get_auth();

    if( ! $auth ) {
        return '';
    }

    // Setup user id
    $user_id = get_current_user_id();

    if( $user_id === 0 ) {
        return sprintf( __( 'You need to <a href="%s">log in</a> to sync your account with Credly.', 'gamipress-credly' ), wp_login_url( get_permalink() ) );
    }

    $remote_id = gamipress_credly_get_user_remote_id( $user_id );

    if( ! empty( $remote_id ) ) {
        return __( 'Your account has been synchronized with Credly successfully!', 'gamipress-credly' );
    }

    $gamipress_credly_template_args = $atts;

    // Enqueue assets
    gamipress_credly_enqueue_scripts();

    ob_start();
    gamipress_get_template_part( 'credly-login' );
    $output = ob_get_clean();

    // Return our rendered form
    return $output;
}
