<?php
/**
 * Plugin Name:           AutomatorWP - SureMembers
 * Plugin URI:            https://automatorwp.com/add-ons/suremembers/
 * Description:           Connect AutomatorWP with SureMembers
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-suremembers
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SureMembers
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_SureMembers {

    /**
     * @var         AutomatorWP_Integration_SureMembers $instance The one true AutomatorWP_Integration_SureMembers
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_SureMembers self::$instance The one true AutomatorWP_Integration_SureMembers
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_SureMembers();
            self::$instance->constants();
            self::$instance->includes();
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
        define( 'AUTOMATORWP_SUREMEMBERS_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_SUREMEMBERS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SUREMEMBERS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SUREMEMBERS_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_SUREMEMBERS_DIR . 'includes/triggers/user-added-to-access-group.php';

            // Actions
            require_once AUTOMATORWP_SUREMEMBERS_DIR . 'includes/actions/add-user-to-group.php';

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

        automatorwp_register_integration( 'suremembers', array(
            'label' => 'SureMembers',
            'icon'  => AUTOMATORWP_SUREMEMBERS_URL . 'assets/suremembers.svg',
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

        if ( ! defined( 'SUREMEMBERS_BASE' ) ) {
            return false;
        }

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

        if ( ! class_exists( 'AutomatorWP_SureMembers' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_SureMembers instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_SureMembers The one true AutomatorWP_Integration_SureMembers
 */
function AutomatorWP_Integration_SureMembers() {
    return AutomatorWP_Integration_SureMembers::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_SureMembers' );
