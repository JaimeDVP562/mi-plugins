<?php
/**
 * Plugin Name:           AutomatorWP - Breakdance integration
 * Plugin URI:            https://automatorwp.com/add-ons/breakdance/
 * Description:           Connect AutomatorWP with Breakdance.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-breakdance
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.0
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Breakdance
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

final class AutomatorWP_Integration_Breakdance {

    /**
     * @var         AutomatorWP_Integration_Breakdance $instance The one true AutomatorWP_Integration_Breakdance
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Breakdance self::$instance The one true AutomatorWP_Integration_Breakdance
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Breakdance();

            if( ! self::$instance->pro_installed() ) {

                self::$instance->constants();
                self::$instance->includes();
                
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_BREAKDANCE_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_BREAKDANCE_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_BREAKDANCE_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_BREAKDANCE_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Triggers
            require_once AUTOMATORWP_BREAKDANCE_DIR . 'includes/triggers/save-page.php';
            require_once AUTOMATORWP_BREAKDANCE_DIR . 'includes/triggers/publish-page.php';
            require_once AUTOMATORWP_BREAKDANCE_DIR . 'includes/triggers/submit-form.php';
            // Anonymous Triggers
            require_once AUTOMATORWP_BREAKDANCE_DIR . 'includes/triggers/anonymous-submit-form.php';

            // Includes
            require_once AUTOMATORWP_BREAKDANCE_DIR . 'includes/functions.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'breakdance', array(
            'label' => 'Breakdance',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/breakdance.svg',
        ) );

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        // Breakdance can be loaded even if not currently active
        return true;

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {

        if ( class_exists( 'AutomatorWP_Breakdance' ) ) {
            return true;
        }

        return false;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Breakdance instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Breakdance The one true AutomatorWP_Integration_Breakdance
 */
function AutomatorWP_Integration_Breakdance() {
    return AutomatorWP_Integration_Breakdance::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Breakdance' );