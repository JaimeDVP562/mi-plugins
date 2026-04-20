<?php
/**
 * Plugin Name:           AutomatorWP - AWeber
 * Plugin URI:            https://automatorwp.com/add-ons/aweber/
 * Description:           Connect AutomatorWP with AWeber.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-aweber
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\AWeber
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_AWeber {

    /**
     * @var         AutomatorWP_Integration_AWeber $instance The one true AutomatorWP_Integration_AWeber
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_AWeber self::$instance The one true AutomatorWP_Integration_AWeber
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_AWeber();

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
        define( 'AUTOMATORWP_AWEBER_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_AWEBER_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_AWEBER_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_AWEBER_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/actions/add-user-list.php';
            require_once AUTOMATORWP_AWEBER_DIR . 'includes/actions/add-user-tag.php';

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

        automatorwp_register_integration( 'aweber', array(
            'label' => 'AWeber',
            'icon'  => AUTOMATORWP_AWEBER_URL . 'assets/aweber.svg',
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

        if ( ! class_exists( 'AutomatorWP_AWeber' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_AWeber instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_AWeber The one true AutomatorWP_Integration_AWeber
 */
function AutomatorWP_Integration_AWeber() {
    return AutomatorWP_Integration_AWeber::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_AWeber' );
