<?php
/**
 * Scripts
 *
 * @package     GamiPress\Referrals\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_referrals_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-referrals-js', GAMIPRESS_REFERRALS_URL . 'assets/js/gamipress-referrals' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_REFERRALS_VER, true );

}
add_action( 'init', 'gamipress_referrals_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_referrals_enqueue_scripts( $hook = null ) {

    // Localize scripts
    wp_localize_script( 'gamipress-referrals-js', 'gamipress_referrals', array(
        'url_parameter' => gamipress_referrals_get_option( 'url_parameter', 'ref' ),
        'referral_id'   => gamipress_referrals_get_affiliate_referral_id( get_current_user_id() ),
        'invalid_url'   => __( 'Please enter a valid URL.', 'gamipress-referrals' ),
    ) );

    // Enqueue assets
    wp_enqueue_script( 'gamipress-referrals-js' );

}
//add_action( 'wp_enqueue_scripts', 'gamipress_referrals_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_referrals_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-referrals-admin-js', GAMIPRESS_REFERRALS_URL . 'assets/js/gamipress-referrals-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), GAMIPRESS_REFERRALS_VER, true );
    wp_register_script( 'gamipress-referrals-shortcode-editor-js', GAMIPRESS_REFERRALS_URL . 'assets/js/gamipress-referrals-shortcode-editor' . $suffix . '.js', array( 'jquery', 'gamipress-select2-js' ), GAMIPRESS_REFERRALS_VER, true );
    wp_register_script( 'gamipress-referrals-requirements-ui-js', GAMIPRESS_REFERRALS_URL . 'assets/js/gamipress-referrals-requirements-ui' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_REFERRALS_VER, true );

}
add_action( 'admin_init', 'gamipress_referrals_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_referrals_admin_enqueue_scripts( $hook ) {

    global $post_type;

    //Scripts
    wp_enqueue_script( 'gamipress-referrals-admin-js' );

    // Just enqueue on add/edit views and on post types that supports editor feature
    if( ( $hook === 'post.php' || $hook === 'post-new.php' ) && post_type_supports( $post_type, 'editor' ) ) {
        wp_enqueue_script( 'gamipress-referrals-shortcode-editor-js' );
    }

    $allowed_post_types = array_merge( gamipress_get_achievement_types_slugs(), gamipress_get_rank_types_slugs() );

    // Requirements ui script
    if ( $post_type === 'points-type' || in_array( $post_type, $allowed_post_types ) ) {
        wp_enqueue_script( 'gamipress-referrals-requirements-ui-js' );
    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_referrals_admin_enqueue_scripts', 100 );