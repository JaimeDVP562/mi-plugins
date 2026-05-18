<?php
/**
 * Plugin Name:           AutomatorWP - Short.io
 * Plugin URI:            https://wordpress.org/plugins/wp-shortcm/
 * Description:           Connect AutomatorWP with Short.io to automate link management.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-shortio
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Shortio
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Shortio {

    /**
     * @var AutomatorWP_Integration_Shortio The single instance of the class.
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Main Instance.
     *
     * Insures that only one instance of the integration exists in memory at any one time.
     *
     * @since 1.0.0
     * @return AutomatorWP_Integration_Shortio
     */
    public static function instance() {
        if( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Shortio();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants.
     *
     * @since 1.0.0
     */
    private function constants() {
        define( 'AUTOMATORWP_SHORT_IO_VER', '1.0.0' );
        define( 'AUTOMATORWP_SHORT_IO_FILE', __FILE__ );
        define( 'AUTOMATORWP_SHORT_IO_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_SHORT_IO_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include required files based on requirements.
     *
     * @since 1.0.0
     */
    private function includes() {
        if( $this->meets_requirements() ) {
            // Core logic
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/scripts.php';

            // Action classes
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/actions/create-domain.php';
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/actions/create-short-link.php'; 
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/actions/delete-link.php'; 
            require_once AUTOMATORWP_SHORT_IO_DIR . 'includes/actions/redirect-to-link.php'; 
        }
    }

    /**
     * Setup WordPress hooks.
     *
     * @since 1.0.0
     */
    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
    }

    /**
     * Registers the Short.io integration into AutomatorWP.
     *
     * @since 1.0.0
     */
    public function register_integration() {
        automatorwp_register_integration( 'shortio', array(
            'label' => 'Short.io',
            'icon'  => AUTOMATORWP_SHORT_IO_URL . 'assets/shortio-icon.svg',
        ) );
    }

    /**
     * Check if core AutomatorWP is active.
     *
     * @since 1.0.0
     * @return bool
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }
}

/**
 * Returns the main instance of AutomatorWP_Integration_Shortio.
 *
 * @since 1.0.0
 * @return AutomatorWP_Integration_Shortio
 */
function AutomatorWP_Shortio() {
    return AutomatorWP_Integration_Shortio::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Shortio' );