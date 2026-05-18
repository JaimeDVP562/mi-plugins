<?php

/**
 * Scripts
 *
 * @package     AutomatorWP\Bitly\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since  1.0.0
 * @return void
 */
function automatorwp_bitly_admin_register_scripts() {

    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

    wp_register_script(
        'automatorwp-bitly-js',
        AUTOMATORWP_BITLY_URL . 'assets/js/automatorwp-bitly' . $suffix . '.js',
        array( 'jquery' ),
        AUTOMATORWP_BITLY_VER,
        true
    );
}
add_action( 'admin_init', 'automatorwp_bitly_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since  1.0.0
 * @return void
 */
function automatorwp_bitly_admin_enqueue_scripts( $hook ) {

    wp_localize_script( 'automatorwp-bitly-js', 'automatorwp_bitly', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-bitly-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_bitly_admin_enqueue_scripts', 100 );