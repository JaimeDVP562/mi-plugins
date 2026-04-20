<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Drip\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_drip_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'automatorwp-drip-js', AUTOMATORWP_DRIP_URL . 'assets/js/automatorwp-drip' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_DRIP_VER, true );
    
}
add_action( 'admin_init', 'automatorwp_drip_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_drip_admin_enqueue_scripts( $hook ) {

    wp_localize_script( 'automatorwp-drip-js', 'automatorwp_drip', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-drip-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_drip_admin_enqueue_scripts', 100 );