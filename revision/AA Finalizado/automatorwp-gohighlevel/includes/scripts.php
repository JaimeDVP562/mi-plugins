<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\GoHighLevel\Scripts
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register admin scripts and styles.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_admin_register_scripts()
{
	$suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

	$css_file = AUTOMATORWP_GOHIGHLEVEL_DIR . 'assets/css/automatorwp-gohighlevel' . $suffix . '.css';
	$js_file = AUTOMATORWP_GOHIGHLEVEL_DIR . 'assets/js/automatorwp-gohighlevel' . $suffix . '.js';

	if (file_exists($css_file)) {
		wp_register_style(
			'automatorwp-gohighlevel-css',
			AUTOMATORWP_GOHIGHLEVEL_URL . 'assets/css/automatorwp-gohighlevel' . $suffix . '.css',
			array(),
			AUTOMATORWP_GOHIGHLEVEL_VER,
			'all'
		);
	}

	if (file_exists($js_file)) {
		wp_register_script(
			'automatorwp-gohighlevel-js',
			AUTOMATORWP_GOHIGHLEVEL_URL . 'assets/js/automatorwp-gohighlevel' . $suffix . '.js',
			array('jquery'),
			AUTOMATORWP_GOHIGHLEVEL_VER,
			true
		);
	}
}
add_action('admin_init', 'automatorwp_gohighlevel_admin_register_scripts');

/**
 * Enqueue admin scripts and styles on AutomatorWP pages.
 *
 * @since 1.0.0
 * @param string $hook Current admin page hook.
 * @return void
 */
function automatorwp_gohighlevel_admin_enqueue_scripts($hook = '')
{
	if (strpos($hook, 'automatorwp') === false) {
		return;
	}

	if (wp_style_is('automatorwp-gohighlevel-css', 'registered')) {
		wp_enqueue_style('automatorwp-gohighlevel-css');
	}

	if (wp_script_is('automatorwp-gohighlevel-js', 'registered')) {
		$localized_data = array(
			'nonce' => automatorwp_get_admin_nonce(),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'version' => AUTOMATORWP_GOHIGHLEVEL_VER,
			'plugin_url' => AUTOMATORWP_GOHIGHLEVEL_URL,
			'rest_base' => rest_get_url_prefix(),
			'rest_nonce' => wp_create_nonce('wp_rest'),
			'debug' => (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG),
		);

		wp_localize_script('automatorwp-gohighlevel-js', 'automatorwp_gohighlevel', $localized_data);
		wp_enqueue_script('automatorwp-gohighlevel-js');
	}
}
add_action('admin_enqueue_scripts', 'automatorwp_gohighlevel_admin_enqueue_scripts', 100);

/**
 * Enqueue frontend styles when needed.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_frontend_enqueue_scripts()
{
	if (! apply_filters('automatorwp_gohighlevel_load_frontend_scripts', false)) {
		return;
	}

	if (wp_style_is('automatorwp-gohighlevel-css', 'registered')) {
		wp_enqueue_style('automatorwp-gohighlevel-css');
	}
}
add_action('wp_enqueue_scripts', 'automatorwp_gohighlevel_frontend_enqueue_scripts');

/**
 * Inline admin scripts for credentials actions.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_inline_scripts()
{
	if (! is_admin() || ! current_user_can('manage_options')) {
		return;
	}

	$screen = get_current_screen();
	if (! $screen || strpos($screen->base, 'automatorwp') === false) {
		return;
	}
	return;
}
add_action('admin_footer', 'automatorwp_gohighlevel_inline_scripts');
