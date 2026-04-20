<?php
/**
 * Plugin Name:           AutomatorWP - Bento
 * Plugin URI:            https://wordpress.org/plugins/email-marketing/
 * Description:           Connect AutomatorWP with Bento.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-bento
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Bento
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Bento {

    /**
     * @var         AutomatorWP_Integration_Bento $instance The one true AutomatorWP_Bento
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Bento self::$instance The one true AutomatorWP_Bento
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Bento();
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
        define( 'AUTOMATORWP_BENTO_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_BENTO_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_BENTO_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_BENTO_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_BENTO_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_BENTO_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BENTO_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BENTO_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_BENTO_DIR . 'includes/actions/add-tag-to-subscriber.php';

            require_once AUTOMATORWP_BENTO_DIR . 'includes/actions/remove-tag-from-subscriber.php';

            require_once AUTOMATORWP_BENTO_DIR . 'includes/actions/create-update-subscriber.php';
            require_once AUTOMATORWP_BENTO_DIR . 'includes/actions/remove-subscriber.php';
            require_once AUTOMATORWP_BENTO_DIR . 'includes/actions/add-subscriber-to-campaign.php';


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

        automatorwp_register_integration( 'bento', array(
            'label' => 'Bento',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/bento-icon-64.png',
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
 * The main function responsible for returning the one true AutomatorWP_Integration_Bento instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Bento The one true AutomatorWP_Integration_Bento
 */
function AutomatorWP_Bento() {
    return AutomatorWP_Integration_Bento::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Bento' );
