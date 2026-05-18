<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\DeepL\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_deepl_admin_register_scripts() {

    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_register_style(
        'automatorwp-deepl-css',
        AUTOMATORWP_DEEPL_URL . 'assets/css/automatorwp-deepl' . $suffix . '.css',
        array(),
        AUTOMATORWP_DEEPL_VER,
        'all'
    );

    wp_register_script(
        'automatorwp-deepl-js',
        AUTOMATORWP_DEEPL_URL . 'assets/js/automatorwp-deepl' . $suffix . '.js',
        array( 'jquery' ),
        AUTOMATORWP_DEEPL_VER,
        true
    );

}
add_action( 'admin_init', 'automatorwp_deepl_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_deepl_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-deepl-css' );

    wp_localize_script( 'automatorwp-deepl-js', 'automatorwp_deepl', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-deepl-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_deepl_admin_enqueue_scripts', 100 );
