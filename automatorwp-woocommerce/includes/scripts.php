<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Scripts
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
function automatorwp_woocommerce_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'automatorwp-woocommerce-js', AUTOMATORWP_WOOCOMMERCE_URL . 'assets/js/automatorwp-woocommerce' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_WOOCOMMERCE_VER, true );

}
add_action( 'admin_init', 'automatorwp_woocommerce_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 *
 * @param string $hook
 *
 * @return      void
 */
function automatorwp_woocommerce_admin_enqueue_scripts( $hook ) {

    // Scripts
    wp_enqueue_script( 'automatorwp-woocommerce-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_woocommerce_admin_enqueue_scripts' );