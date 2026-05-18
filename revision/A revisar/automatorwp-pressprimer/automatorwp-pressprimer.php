<?php
/**
 * Plugin Name:           AutomatorWP - PressPrimer
 * Plugin URI:            https://automatorwp.com/
 * Description:           Connect AutomatorWP with PressPrimer Quiz.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-pressprimer
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\PressPrimer
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_PressPrimer {

    /**
     * @var         AutomatorWP_Integration_PressPrimer $instance The one true AutomatorWP_Integration_PressPrimer
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @since       1.0.0
     * @return      AutomatorWP_Integration_PressPrimer
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_PressPrimer();
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

        define( 'AUTOMATORWP_PRESSPRIMER_VER',  '1.0.0' );
        define( 'AUTOMATORWP_PRESSPRIMER_FILE', __FILE__ );
        define( 'AUTOMATORWP_PRESSPRIMER_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_PRESSPRIMER_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_PRESSPRIMER_DIR . 'includes/triggers/user-completes-quiz.php';
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

        automatorwp_register_integration( 'pressprimer', array(
            'label' => 'PressPrimer',
            'icon'  => AUTOMATORWP_PRESSPRIMER_URL . 'assets/pressprimer-icon.png',
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
            'automatorwp-pressprimer',
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
        <div class="error">
            <p><?php printf(
                __( 'AutomatorWP - PressPrimer requires <a href="%s">AutomatorWP</a> in order to work. Please install and activate it first.', 'automatorwp-pressprimer' ),
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
 * The main function responsible for returning the one true AutomatorWP_Integration_PressPrimer instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_PressPrimer
 */
function AutomatorWP_PressPrimer_Init() {
    return AutomatorWP_Integration_PressPrimer::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_PressPrimer_Init', 11 );