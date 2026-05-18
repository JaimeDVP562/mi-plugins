<?php
/**
 * Admin Updates
 *
 * Adds the Security Optimizer integration to AutomatorWP automatic update labels.
 */
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registers the Security Optimizer label for AutomatorWP automatic updates.
 *
 * @param array $automatic_updates_plugins List of plugin update labels.
 * @return array Modified list with Security Optimizer included.
 */
function automatorwp_so_automatic_updates( $automatic_updates_plugins ) {
    $automatic_updates_plugins['security-optimizer'] = __( 'Security Optimizer integration', 'automatorwp' );
    return $automatic_updates_plugins;
}
add_filter( 'automatorwp_automatic_updates_plugins', 'automatorwp_so_automatic_updates' );