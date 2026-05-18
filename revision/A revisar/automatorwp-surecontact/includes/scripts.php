<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\surecontact\Scripts
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
function AutomatorWP_SureContact_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'automatorwp-surecontact-js', AutomatorWP_SureContact_URL . 'assets/js/automatorwp-surecontact' . $suffix . '.js', array( 'jquery' ), AutomatorWP_SureContact_VER, true );
    
}
add_action( 'admin_init', 'AutomatorWP_SureContact_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function AutomatorWP_SureContact_admin_enqueue_scripts( $hook ) {

    wp_localize_script( 'automatorwp-surecontact-js', 'AutomatorWP_SureContact', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-surecontact-js' );

}
add_action( 'admin_enqueue_scripts', 'AutomatorWP_SureContact_admin_enqueue_scripts', 100 );