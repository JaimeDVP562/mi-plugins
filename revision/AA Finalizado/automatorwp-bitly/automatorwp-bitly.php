<?php
/**
 * Plugin Name:           AutomatorWP - Bitly
 * Plugin URI:            https://wordpress.org/plugins/automatorwp-bitly/
 * Description:           Connect AutomatorWP with Bitly
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-bitly
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Bitly
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Bitly {

    /**
     * @var         AutomatorWP_Integration_Bitly $instance The one true AutomatorWP_Integration_Bitly
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Bitly
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Bitly();
            self::$instance->constants();
            self::$instance->hooks();
            self::$instance->includes();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @since       1.0.0
     * @return      void
     */
    private function constants() {

        define( 'AUTOMATORWP_BITLY_VER',  '1.0.0' );
        define( 'AUTOMATORWP_BITLY_FILE', __FILE__ );
        define( 'AUTOMATORWP_BITLY_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_BITLY_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_BITLY_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_BITLY_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BITLY_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BITLY_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_BITLY_DIR . 'includes/actions/create-short-link.php';
        }
    }

    /**
     * Setup plugin hooks
     *
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init',             array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices',    array( $this, 'admin_notices' ) );

        register_activation_hook( __FILE__,   array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__,  array( $this, 'deactivate' ) );
    }

    /**
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'bitly', array(
            'label' => 'Bitly',
            'icon'  => AUTOMATORWP_BITLY_URL . 'assets/bitly-icon.svg',
        ) );
    }

    /**
     * Load plugin textdomain
     *
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        load_plugin_textdomain(
            'automatorwp-bitly',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Show admin notices if requirements are not met
     *
     * @since       1.0.0
     * @return      void
     */
    public function admin_notices() {

        if ( $this->meets_requirements() ) {
            return;
        }

        ?>
        <div id="message" class="error">
            <p><?php printf(
                __( 'AutomatorWP - Bitly requires <a href="%s">AutomatorWP</a> in order to work. Please install and activate it first.', 'automatorwp-bitly' ),
                'https://wordpress.org/plugins/automatorwp/'
            ); ?></p>
        </div>
        <?php
    }

    /**
     * Activation hook
     *
     * @since       1.0.0
     * @return      void
     */
    public function activate() {}

    /**
     * Deactivation hook
     *
     * @since       1.0.0
     * @return      void
     */
    public function deactivate() {}

    /**
     * Check if all requirements are met
     *
     * @since       1.0.0
     * @return      bool
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;
    }
}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Bitly instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_Bitly
 */
function AutomatorWP_Bitly() {
    return AutomatorWP_Integration_Bitly::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Bitly' );