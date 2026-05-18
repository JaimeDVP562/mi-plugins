<?php
/**
 * Admin Settings for SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin settings meta boxes
 */
function automatorwp_sendpulse_admin_menu() {
}
add_action( 'admin_menu', 'automatorwp_sendpulse_admin_menu' );