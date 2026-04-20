<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\WordPress\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
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
function automatorwp_wordpress_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'automatorwp-wordpress-js', AUTOMATORWP_WORDPRESS_URL . 'assets/js/automatorwp-wordpress' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );

}
add_action( 'admin_init', 'automatorwp_wordpress_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_wordpress_admin_enqueue_scripts( $hook ) {

    // Scripts
    wp_localize_script( 'automatorwp-wordpress-js', 'automatorwp_wordpress', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-wordpress-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_wordpress_admin_enqueue_scripts', 100 );