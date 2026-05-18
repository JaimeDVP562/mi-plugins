<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\MailMint\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since 1.0.0
 */
function automatorwp_mailmint_admin_register_scripts()
{
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_register_script(
        'automatorwp-mailmint-js',
        AUTOMATORWP_MAILMINT_URL . 'assets/js/automatorwp-mailmint' . $suffix . '.js',
        array( 'jquery' ),
        AUTOMATORWP_MAILMINT_VER,
        true
    );
}
add_action( 'admin_init', 'automatorwp_mailmint_admin_register_scripts' );

/**
 * Enqueue and localize admin scripts
 *
 * @since 1.0.0
 *
 * @param string $hook
 */
function automatorwp_mailmint_admin_enqueue_scripts( $hook )
{
    wp_localize_script( 'automatorwp-mailmint-js', 'automatorwp_mailmint', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-mailmint-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailmint_admin_enqueue_scripts', 100 );
