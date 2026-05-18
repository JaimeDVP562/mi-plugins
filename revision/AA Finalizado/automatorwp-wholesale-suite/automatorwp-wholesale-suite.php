<?php
/**
 * Plugin Name:           AutomatorWP - Wholesale Suite
 * Plugin URI:            https://wordpress.org/plugins/automatorwp-wholesale-suite/
 * Description:           Connect AutomatorWP with Wholesale Suite
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-wholesale-suite
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Wholesale Suite
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Wholesale_Suite {

    /**
     * @var         AutomatorWP_Integration_Wholesale_Suite $instance The one true AutomatorWP_Integration_Wholesale_Suite
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Wholesale_Suite
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Wholesale_Suite();
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

        define( 'AUTOMATORWP_WHOLESALE_SUITE_VER',  '1.0.0' );
        define( 'AUTOMATORWP_WHOLESALE_SUITE_FILE', __FILE__ );
        define( 'AUTOMATORWP_WHOLESALE_SUITE_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_WHOLESALE_SUITE_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_WHOLESALE_SUITE_DIR . 'includes/triggers/wholesale-order-completed.php';
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
    }

    /**
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'wholesale_suite', array(
            'label' => 'Wholesale Suite',
            'icon'  => AUTOMATORWP_WHOLESALE_SUITE_URL . 'assets/wholesale-suite-icon.jpg',
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
            'automatorwp-wholesale-suite',
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
                __( 'AutomatorWP - Wholesale Suite requires <a href="%s">AutomatorWP</a> in order to work. Please install and activate it first.', 'automatorwp-wholesale-suite' ),
                'https://wordpress.org/plugins/automatorwp/'
            ); ?></p>
        </div>
        <?php
    }

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
 * The main function responsible for returning the one true AutomatorWP_Integration_Wholesale_Suite instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_Wholesale_Suite
 */
function AutomatorWP_Wholesale_Suite_Init() {
    return AutomatorWP_Integration_Wholesale_Suite::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Wholesale_Suite_Init' );