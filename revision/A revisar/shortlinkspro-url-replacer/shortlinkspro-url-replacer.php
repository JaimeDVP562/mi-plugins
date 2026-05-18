<?php
/**
 * Plugin Name: ShortLinks Pro - URL Replacer
 * Plugin URI:  https://shortlinks.pro/
 * Description: Reemplaza URLs en contenidos por shortcodes de ShortLinks Pro.
 * Version:     1.0.0
 * Author:      Alejandro Oliva
 * Text Domain: shortlinkspro-url-replacer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SLP_URL_Replacer {

	/**
	 * @var SLP_URL_Replacer
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return SLP_URL_Replacer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->constants();
			self::$instance->includes();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Constants
	 *
	 * @return void
	 */
	private function constants() {
		if ( ! defined( 'SLP_URL_REPLACER_VERSION' ) ) {
			define( 'SLP_URL_REPLACER_VERSION', '1.0.0' );
		}

		if ( ! defined( 'SLP_URL_REPLACER_FILE' ) ) {
			define( 'SLP_URL_REPLACER_FILE', __FILE__ );
		}

		if ( ! defined( 'SLP_URL_REPLACER_DIR' ) ) {
			define( 'SLP_URL_REPLACER_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'SLP_URL_REPLACER_URL' ) ) {
			define( 'SLP_URL_REPLACER_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Includes
	 *
	 * @return void
	 */
	private function includes() {
		require_once SLP_URL_REPLACER_DIR . 'includes/functions.php';
		require_once SLP_URL_REPLACER_DIR . 'includes/replacer.php';
		require_once SLP_URL_REPLACER_DIR . 'includes/admin.php';
		require_once SLP_URL_REPLACER_DIR . 'includes/ajax.php';
	}

	/**
	 * Hooks
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'admin_enqueue_scripts', 'slp_url_replacer_admin_assets' );
		add_action( 'add_meta_boxes', 'slp_url_replacer_register_meta_box' );
		add_action( 'save_post', 'slp_url_replacer_save_link_config', 10, 2 );

		add_action( 'wp_ajax_slp_url_replacer_run_replace', 'slp_url_replacer_ajax_run_replace' );
		add_action( 'wp_ajax_slp_url_replacer_run_cleanup', 'slp_url_replacer_ajax_run_cleanup' );

		add_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20, 3 );
	}
}

/**
 * Init
 *
 * @return SLP_URL_Replacer
 */
function slp_url_replacer() {
	return SLP_URL_Replacer::instance();
}

add_action( 'plugins_loaded', 'slp_url_replacer' );