<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\Cohere\Scripts
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_cohere_register_scripts()
{
    wp_register_script( 'automatorwp-cohere', AUTOMATORWP_COHERE_URL . 'assets/js/automatorwp-cohere.js', array( 'jquery' ), AUTOMATORWP_COHERE_VER, true );
}
add_action( 'admin_init', 'automatorwp_cohere_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_cohere_admin_scripts( $hook )
{
    wp_enqueue_script( 'automatorwp-cohere' );
    wp_localize_script( 'automatorwp-cohere', 'automatorwp_cohere', array(
        'nonce' => wp_create_nonce( 'automatorwp_admin' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_cohere_admin_scripts' );
