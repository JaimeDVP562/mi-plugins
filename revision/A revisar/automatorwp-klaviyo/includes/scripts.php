<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Klaviyo\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Ródenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_klaviyo_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-klaviyo-css', AUTOMATORWP_KLAVIYO_URL . 'assets/css/automatorwp-klaviyo' . $suffix . '.css', array(), AUTOMATORWP_KLAVIYO_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-klaviyo-js', AUTOMATORWP_KLAVIYO_URL . 'assets/js/automatorwp-klaviyo' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_KLAVIYO_VER, true );
    
}
add_action( 'admin_init', 'automatorwp_klaviyo_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_klaviyo_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-klaviyo-css' );

    wp_localize_script( 'automatorwp-klaviyo-js', 'automatorwp_klaviyo', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-klaviyo-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_klaviyo_admin_enqueue_scripts', 100 );
