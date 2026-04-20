<?php
/**
 * Plugin Name:           AutomatorWP - Drip
 * Plugin URI:            https://wordpress.org/plugins/email-marketing/
 * Description:           Connect AutomatorWP with Drip.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-drip
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Drip
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Drip {

    /**
     * @var         AutomatorWP_Integration_Drip $instance The one true AutomatorWP_Drip
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Drip self::$instance The one true AutomatorWP_Drip
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Drip();
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
        define( 'AUTOMATORWP_DRIP_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_DRIP_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_DRIP_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_DRIP_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_DRIP_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_DRIP_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_DRIP_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_DRIP_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/add-tag-to-subscriber.php';

            require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/remove-tag-from-subscriber.php';
            
            require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/create-update-subscriber.php';
            require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/remove-subscriber.php';
            require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/add-subscriber-to-campaign.php';
            

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

        automatorwp_register_integration( 'drip', array(
            'label' => 'Drip',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/drip-icon-64.png',
        ) );

    }

    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Drip instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Drip The one true AutomatorWP_Integration_Drip
 */
function AutomatorWP_Drip() {
    return AutomatorWP_Integration_Drip::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Drip' );
