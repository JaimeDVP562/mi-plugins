<?php
/**
 * Plugin Name:           AutomatorWP - FluentBooking
 * Plugin URI:            https://automatorwp.com/add-ons/fluentbooking/
 * Description:           Connect AutomatorWP with FluentBooking.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-fluentbooking
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\FluentBooking
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

 final class AutomatorWP_Integration_FluentBooking {

    /**
     * @var         AutomatorWP_Integration_FluentBooking $instance The one true AutomatorWP_Integration_FluentBooking
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_FluentBooking self::$instance The one true AutomatorWP_Integration_FluentBooking
     */
    public static function instance() {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_Integration_FluentBooking();

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
        define( 'AUTOMATORWP_FLUENTBOOKING_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_FLUENTBOOKING_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_FLUENTBOOKING_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_FLUENTBOOKING_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_FLUENTBOOKING_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_FLUENTBOOKING_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_FLUENTBOOKING_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_FLUENTBOOKING_DIR . 'includes/triggers/user-single-schedule-meeting.php';
            require_once AUTOMATORWP_FLUENTBOOKING_DIR . 'includes/triggers/user-team-schedule-meeting.php';
           
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

        automatorwp_register_integration( 'fluentbooking', array(
            'label' => 'FluentBooking',
            'icon'  => AUTOMATORWP_FLUENTBOOKING_URL . 'assets/fluentbooking.svg',
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

        if ( ! defined( 'FLUENT_BOOKING_VERSION' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_FluentBooking' ) ) {
            return false;
        }

        return true;

    }
     
}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_FluentBooking instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_FluentBooking The one true AutomatorWP_Integration_FluentBooking
 */
function AutomatorWP_Integration_FluentBooking() {
    return AutomatorWP_Integration_FluentBooking::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_FluentBooking' );
