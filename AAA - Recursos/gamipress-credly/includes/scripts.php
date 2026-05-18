<?php
/**
 * Scripts
 *
 * @package     GamiPress\Credly\Scripts
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
function gamipress_credly_register_scripts() {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-credly-css', GAMIPRESS_CREDLY_URL . 'assets/css/gamipress-credly' . $suffix . '.css', array( ), GAMIPRESS_CREDLY_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-credly-js', GAMIPRESS_CREDLY_URL . 'assets/js/gamipress-credly' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_CREDLY_VER, true );

}
add_action( 'init', 'gamipress_credly_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_credly_enqueue_scripts( $hook = null ) {

    wp_enqueue_style( 'gamipress-credly-css' );

    // Prevent duplicated enqueueing
    if( ! wp_script_is( 'gamipress-credly-js', 'enqueued' ) ) {

        wp_localize_script( 'gamipress-credly-js', 'gamipress_credly', array(
            'ajaxurl' => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce' => gamipress_get_nonce(),
            'email_error' => __( 'Please, provide a valid email address.', 'gamipress-credly' ),
        ) );

        wp_enqueue_script( 'gamipress-credly-js' );

    }

}
add_action( 'wp_enqueue_scripts', 'gamipress_credly_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_credly_admin_register_scripts() {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-credly-admin-css', GAMIPRESS_CREDLY_URL . 'assets/css/gamipress-credly-admin' . $suffix . '.css', array( ), GAMIPRESS_CREDLY_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-credly-admin-js', GAMIPRESS_CREDLY_URL . 'assets/js/gamipress-credly-admin' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_CREDLY_VER, true );

}
add_action( 'admin_init', 'gamipress_credly_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_credly_admin_enqueue_scripts( $hook ) {
    global $post_type;

    // Stylesheets
    wp_enqueue_style( 'gamipress-credly-admin-css' );

    wp_localize_script( 'gamipress-credly-admin-js', 'gamipress_credly_admin', array(
        'nonce' => gamipress_get_admin_nonce(),
        'categories_placeholder' => __( 'Choose categories', 'gamipress-credly' ),
    ) );

    // Scripts
    wp_enqueue_script( 'gamipress-credly-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_credly_admin_enqueue_scripts', 100 );