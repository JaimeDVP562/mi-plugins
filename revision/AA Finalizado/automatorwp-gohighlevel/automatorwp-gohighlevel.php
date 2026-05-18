<?php
/**
 * Plugin Name: AutomatorWP - GoHighLevel 
 * Description: Adds GoHighLevel triggers and actions for AutomatorWP.
 * Version: 0.1.0
 * Text Domain: automatorwp-gohighlevel
 */

if (! defined('ABSPATH')) {
	exit;
}

define('AWP_GOHIGHLEVEL_FILE', __FILE__);
define('AWP_GOHIGHLEVEL_DIR', plugin_dir_path(__FILE__));
define('AWP_GOHIGHLEVEL_URL', plugin_dir_url(__FILE__));

if (! defined('AWP_GOHIGHLEVEL_VER')) {
	define('AWP_GOHIGHLEVEL_VER', '0.1.0');
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN')) {
	define('AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN', 'automatorwp-gohighlevel');
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_FILE')) {
	define('AUTOMATORWP_GOHIGHLEVEL_FILE', AWP_GOHIGHLEVEL_FILE);
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_DIR')) {
	define('AUTOMATORWP_GOHIGHLEVEL_DIR', AWP_GOHIGHLEVEL_DIR);
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_URL')) {
	define('AUTOMATORWP_GOHIGHLEVEL_URL', AWP_GOHIGHLEVEL_URL);
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_VER')) {
	define('AUTOMATORWP_GOHIGHLEVEL_VER', AWP_GOHIGHLEVEL_VER);
}

if (! defined('AUTOMATORWP_GOHIGHLEVEL_API_TIMEOUT')) {
	define('AUTOMATORWP_GOHIGHLEVEL_API_TIMEOUT', 20);
}

function awp_gohighlevel_load_directory($relative_dir)
{
	$dir = trailingslashit(AWP_GOHIGHLEVEL_DIR . ltrim($relative_dir, '/'));

	if (! is_dir($dir)) {
		return;
	}

	$files = glob($dir . '*.php');
	if (! is_array($files)) {
		return;
	}

	foreach ($files as $file) {
		require_once $file;
	}
}

function awp_gohighlevel_bootstrap()
{
	if (defined('WP_DEBUG') && WP_DEBUG) {
		error_log('[AWP-GoHighLevel ' . AWP_GOHIGHLEVEL_VER . '] bootstrap loaded');
	}

	require_once AWP_GOHIGHLEVEL_DIR . 'includes/functions.php';
	require_once AWP_GOHIGHLEVEL_DIR . 'includes/admin.php';
	require_once AWP_GOHIGHLEVEL_DIR . 'includes/ajax-functions.php';
	require_once AWP_GOHIGHLEVEL_DIR . 'includes/webhooks.php';
	require_once AWP_GOHIGHLEVEL_DIR . 'includes/scripts.php';
	require_once AWP_GOHIGHLEVEL_DIR . 'includes/tags.php';

	awp_gohighlevel_load_directory('includes/triggers');
	awp_gohighlevel_load_directory('includes/actions');
}
add_action('plugins_loaded', 'awp_gohighlevel_bootstrap', 20);

function awp_gohighlevel_register_integration()
{
	if (! function_exists('automatorwp_register_integration')) {
		return;
	}

	$integration = array(
		'label' => 'GoHighLevel',
	);

	$icon_file = AWP_GOHIGHLEVEL_DIR . 'assets/img/dashicon-gohighlevel.svg';
	if (file_exists($icon_file)) {
		$integration['icon'] = AWP_GOHIGHLEVEL_URL . 'assets/img/dashicon-gohighlevel.svg';
	}

	automatorwp_register_integration('gohighlevel', $integration);
}
add_action('automatorwp_init', 'awp_gohighlevel_register_integration', 15);
