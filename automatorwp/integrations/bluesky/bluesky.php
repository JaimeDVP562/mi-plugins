<?php
/**
 * Plugin Name:           AutomatorWP - Bluesky
 * Plugin URI:            https://automatorwp.com/add-ons/Bluesky/
 * Description:           Connect AutomatorWP with Bluesky.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-bluesky
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Bluesky
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */ 

final class AutomatorWP_Integration_Bluesky {

    /**
     * @var         AutomatorWP_Integration_Bluesky $instance The one true AutomatorWP_Integration_Bluesky
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Bluesky self::$instance The one true AutomatorWP_Integration_Bluesky
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Bluesky();
            
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
        define( 'AUTOMATORWP_BLUESKY_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_BLUESKY_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_BLUESKY_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_BLUESKY_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_BLUESKY_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_BLUESKY_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BLUESKY_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BLUESKY_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_BLUESKY_DIR . 'includes/actions/create-post.php';

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

        automatorwp_register_integration( 'bluesky', array(
            'label' => 'Bluesky',
            'icon'  => AUTOMATORWP_BLUESKY_URL . 'assets/bluesky.svg',
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

        if ( ! class_exists( 'AutomatorWP_Bluesky' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Bluesky instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Bluesky The one true AutomatorWP_Integration_Bluesky
 */
function AutomatorWP_Integration_Bluesky() {
    return AutomatorWP_Integration_Bluesky::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Bluesky' );
