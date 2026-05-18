<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\FluentCart\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts and styles.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_admin_register_scripts() {
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    wp_register_style(
        'automatorwp-fluentcart-css',
        AUTOMATORWP_FLUENTCART_URL . 'assets/css/automatorwp-fluentcart' . $suffix . '.css',
        array(), AUTOMATORWP_FLUENTCART_VER, 'all'
    );
    wp_register_script(
        'automatorwp-fluentcart-js',
        AUTOMATORWP_FLUENTCART_URL . 'assets/js/automatorwp-fluentcart' . $suffix . '.js',
        array( 'jquery' ), AUTOMATORWP_FLUENTCART_VER, true
    );
}
add_action( 'admin_init', 'automatorwp_fluentcart_admin_register_scripts' );

/**
 * Enqueue admin scripts and styles.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_admin_enqueue_scripts( $hook ) {
    wp_enqueue_style( 'automatorwp-fluentcart-css' );
    wp_localize_script( 'automatorwp-fluentcart-js', 'automatorwp_fluentcart', array(
        'nonce'   => automatorwp_get_admin_nonce(),
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
    wp_enqueue_script( 'automatorwp-fluentcart-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_fluentcart_admin_enqueue_scripts', 100 );