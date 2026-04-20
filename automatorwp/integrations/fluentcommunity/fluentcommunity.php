<?php
/**
 * Plugin Name:           AutomatorWP - FluentCommunity
 * Plugin URI:            https://automatorwp.com/add-ons/fluentcommunity/
 * Description:           Connect AutomatorWP with FluentCommunity.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-fluentcommunity
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\FluentCommunity
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */ 

final class AutomatorWP_Integration_FluentCommunity {

    /**
     * @var         AutomatorWP_Integration_FluentCommunity $instance The one true AutomatorWP_Integration_FluentCommunity
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_FluentCommunity self::$instance The one true AutomatorWP_Integration_FluentCommunity
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_FluentCommunity();
            
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
        define( 'AUTOMATORWP_FLUENTCOMMUNITY_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_FLUENTCOMMUNITY_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_FLUENTCOMMUNITY_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_FLUENTCOMMUNITY_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/triggers/post-added.php';
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/triggers/comment-added.php';
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/triggers/join-space.php';
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/triggers/complete-course.php';

            // Actions
            require_once AUTOMATORWP_FLUENTCOMMUNITY_DIR . 'includes/actions/add-user-space.php';

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

        automatorwp_register_integration( 'fluentcommunity', array(
            'label' => 'FluentCommunity',
            'icon'  => AUTOMATORWP_FLUENTCOMMUNITY_URL . 'assets/fluentcommunity.svg',
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

        if ( ! defined( 'FLUENT_COMMUNITY_PLUGIN_DIR' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_FluentCommunity' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_FluentCommunity instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_FluentCommunity The one true AutomatorWP_Integration_FluentCommunity
 */
function AutomatorWP_Integration_FluentCommunity() {
    return AutomatorWP_Integration_FluentCommunity::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_FluentCommunity' );
