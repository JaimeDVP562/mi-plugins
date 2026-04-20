<?php
/**
 * Plugin Name:           AutomatorWP - Campaign Monitor
 * Plugin URI:            https://automatorwp.com/add-ons/campaign-monitor/
 * Description:           Connect AutomatorWP with Campaign Monitor.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-campaign-monitor
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Campaign_Monitor
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Campaign_Monitor {

    /**
     * @var         AutomatorWP_Integration_Campaign_Monitor $instance The one true AutomatorWP_Integration_Campaign_Monitor
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Campaign_Monitor self::$instance The one true AutomatorWP_Integration_Campaign_Monitor
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Campaign_Monitor();
            
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
        define( 'AUTOMATORWP_CAMPAIGN_MONITOR_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_CAMPAIGN_MONITOR_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_CAMPAIGN_MONITOR_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_CAMPAIGN_MONITOR_URL', plugin_dir_url( __FILE__ ) );
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

            // Includes
            require_once AUTOMATORWP_CAMPAIGN_MONITOR_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_CAMPAIGN_MONITOR_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_CAMPAIGN_MONITOR_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_CAMPAIGN_MONITOR_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_CAMPAIGN_MONITOR_DIR . 'includes/actions/add-user-list.php';
            
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

        automatorwp_register_integration( 'campaign_monitor', array(
            'label' => 'Campaign Monitor',
            'icon'  => AUTOMATORWP_CAMPAIGN_MONITOR_URL . 'assets/campaign-monitor.svg',
        ) );

    }

    /**
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_Campaign_Monitor' ) ) {
            return false;
        }

        return true;

    }


}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Campaign_Monitor instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Campaign_Monitor The one true AutomatorWP_Integration_Campaign_Monitor
 */
function AutomatorWP_Integration_Campaign_Monitor() {
    return AutomatorWP_Integration_Campaign_Monitor::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Campaign_Monitor' );
