<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\Perplexity\Scripts
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_perplexity_register_scripts() {
    wp_register_script( 'automatorwp-perplexity', AUTOMATORWP_PERPLEXITY_URL . 'assets/js/automatorwp-perplexity.js', array( 'jquery' ), AUTOMATORWP_PERPLEXITY_VER, true );
    wp_register_style(  'automatorwp-perplexity', AUTOMATORWP_PERPLEXITY_URL . 'assets/css/automatorwp-perplexity.css', array(), AUTOMATORWP_PERPLEXITY_VER );
}
add_action( 'admin_init', 'automatorwp_perplexity_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_perplexity_admin_scripts( $hook ) {
    wp_enqueue_script( 'automatorwp-perplexity' );
    wp_enqueue_style(  'automatorwp-perplexity' );
    wp_localize_script( 'automatorwp-perplexity', 'automatorwp_perplexity', array(
        'nonce' => wp_create_nonce( 'automatorwp_admin' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_perplexity_admin_scripts' );
