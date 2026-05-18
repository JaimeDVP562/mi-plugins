<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Security_Optimizer\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_security_optimizer_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_security_optimizer_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Registers the Security Optimizer label for AutomatorWP automatic updates.
 *
 * @since 1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function automatorwp_so_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['security-optimizer'] = __( 'Security Optimizer integration', 'automatorwp-security-optimizer' );

    return $automatic_updates_plugins;

}
add_filter( 'automatorwp_automatic_updates_plugins', 'automatorwp_so_automatic_updates' );