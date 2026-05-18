<?php
/**
 * Plugin Name:           AutomatorWP - EasyCart
 * Plugin URI:            https://automatorwp.com/add-ons/easycart/
 * Description:           Connect AutomatorWP with WP EasyCart.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-easycart
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\EASYCART
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main integration class for WP EasyCart with AutomatorWP
 */
final class AutomatorWP_EasyCart {

    /**
     * Holds the singleton instance
     *
     * @var AutomatorWP_EasyCart|null
     */
    private static $instance;

    /**
     * Get or create the singleton instance
     *
     * @return AutomatorWP_EasyCart
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_EasyCart();
            self::$instance->define_constants(); 
            self::$instance->includes();        
            self::$instance->hooks();           
        }
        return self::$instance;
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define( 'AUTOMATORWP_EASYCART_VER',  '1.0.0' );
        define( 'AUTOMATORWP_EASYCART_FILE', __FILE__ );
        define( 'AUTOMATORWP_EASYCART_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_EASYCART_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Load required files
     */
    private function includes() {
        if ( $this->meets_requirements() ) {

            $func_file = AUTOMATORWP_EASYCART_DIR . 'includes/functions.php';
            if ( file_exists( $func_file ) ) {
                require_once $func_file;
            }

            $triggers_dir = AUTOMATORWP_EASYCART_DIR . 'includes/triggers/';
            if ( is_dir( $triggers_dir ) ) {
                foreach ( glob( $triggers_dir . '*.php' ) as $trigger_file ) {
                    require_once $trigger_file;
                }
            }
        }
    }

    /**
     * Add plugin hooks
     */
    private function hooks() {
        add_action( 'plugins_loaded',    array( $this, 'load_textdomain' ) );
        add_action( 'automatorwp_init',  array( $this, 'register_integration' ) );
        add_action( 'admin_notices',     array( $this, 'admin_notices' ) );
    }

    /**
     * Check integration requirements
     *
     * @return bool
     */
    private function meets_requirements() {
        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        if ( ! defined( 'EC_CURRENT_VERSION' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Register EasyCart integration
     */
    public function register_integration() {
        automatorwp_register_integration( 'easycart', array(
            'label' => 'WP EasyCart',
            'icon'  => AUTOMATORWP_EASYCART_URL . 'assets/easycart.svg',
        ) );
    }

    /**
     * Load plugin translations
     */
    public function load_textdomain() {
        $lang_dir = AUTOMATORWP_EASYCART_DIR . 'languages/';
        $lang_dir = apply_filters( 'automatorwp_easycart_languages_directory', $lang_dir );
        $locale   = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-easycart' );
        $mofile   = sprintf( 'automatorwp-easycart-%s.mo', $locale );

        if ( file_exists( WP_LANG_DIR . '/automatorwp-easycart/' . $mofile ) ) {
            load_textdomain( 'automatorwp-easycart', WP_LANG_DIR . '/automatorwp-easycart/' . $mofile );
        } elseif ( file_exists( $lang_dir . $mofile ) ) {
            load_textdomain( 'automatorwp-easycart', $lang_dir . $mofile );
        } else {
            load_plugin_textdomain( 'automatorwp-easycart', false, $lang_dir );
        }
    }

    /**
     * Show admin notice if requirements are not met
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - WP EasyCart requires %s and %s to work. Please install and activate them.', 'automatorwp-easycart' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://es.wordpress.org/plugins/wp-easycart/" target="_blank">WP EasyCart</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;
    }

    /**
     * Return the license type
     *
     * @return string
     */
    public function license() {
        return 'GPLv3';
    }

}

/**
 * Initialize the integration
 *
 * @return AutomatorWP_EasyCart
 */
function AutomatorWP_EasyCart() {
    return AutomatorWP_EasyCart::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_EasyCart' );
