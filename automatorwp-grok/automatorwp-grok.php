<?php
/**
 * Plugin Name: AutomatorWP - Grok (xAI)
 * Description: Grok (xAI) integration for AutomatorWP.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: automatorwp-grok
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AutomatorWP_Grok {

    const VERSION = '1.0.0';

    public function __construct() {
        $this->define_constants();
        $this->includes();

        // Load translations safely (WP 6.7+).
        add_action( 'init', array( $this, 'load_textdomain' ), 5 );
    }

    private function define_constants() {

        if ( ! defined( 'AUTOMATORWP_GROK_VERSION' ) ) {
            define( 'AUTOMATORWP_GROK_VERSION', self::VERSION );
        }

        if ( ! defined( 'AUTOMATORWP_GROK_PLUGIN_FILE' ) ) {
            define( 'AUTOMATORWP_GROK_PLUGIN_FILE', __FILE__ );
        }

        if ( ! defined( 'AUTOMATORWP_GROK_PLUGIN_DIR' ) ) {
            define( 'AUTOMATORWP_GROK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        if ( ! defined( 'AUTOMATORWP_GROK_PLUGIN_URL' ) ) {
            define( 'AUTOMATORWP_GROK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

        if ( ! defined( 'AUTOMATORWP_GROK_API_BASE_URL' ) ) {
            define( 'AUTOMATORWP_GROK_API_BASE_URL', 'https://api.x.ai' );
        }
    }

    private function includes() {

        // Core helpers / abstractions
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'includes/functions.php';

        // API and AJAX handlers
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'helpers/api.php';
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'helpers/ajax.php';

        // Integration registration and settings UI
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'integration.php';
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'settings/settings.php';

        // Actions
        require_once AUTOMATORWP_GROK_PLUGIN_DIR . 'actions/generate-text.php';
    }

    public function load_textdomain() {

        load_plugin_textdomain(
            'automatorwp-grok',
            false,
            dirname( plugin_basename( AUTOMATORWP_GROK_PLUGIN_FILE ) ) . '/languages'
        );
    }
}

new AutomatorWP_Grok();
