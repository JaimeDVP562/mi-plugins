<?php
/**
 * Plugin Name:           AutomatorWP - Ti Woocommerce Wishlist
 * Plugin URI:            https://automatorwp.com/add-ons/ti-woocommerce-wishlist/
 * Description:           Connect AutomatorWP with Ti Woocommerce Wishlist.
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-ti-woocommerce-wishlist
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\TI_WOOCOMMERCE_WISHLIST
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_TI_WOOCOMMERCE_WISHLIST {

    /**
     * @var         AutomatorWP_TI_WOOCOMMERCE_WISHLIST $instance The one true AutomatorWP_TI_WOOCOMMERCE_WISHLIST
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_TI_WOOCOMMERCE_WISHLIST self::$instance The one true AutomatorWP_TI_WOOCOMMERCE_WISHLIST
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_TI_WOOCOMMERCE_WISHLIST();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();

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
        define( 'AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR . 'includes/tags.php';
            // Triggers
            require_once AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR . 'includes/triggers/woocommerce-add.php';
            require_once AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR . 'includes/triggers/woocommerce-product-on-sale.php';
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

        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'tiwoocommercewishlist', array(
            'label' => 'WP Ti Woocommerce Wishlist',
            'icon'  => AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_URL . 'assets/tiwoocommercewishlist.svg',
        ) );

    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    function license( $meta_boxes ) {

        $meta_boxes['automatorwp-ti-woocommerce-wishlist-license'] = array(
            'title' => 'WP Ti Woocommerce Wishlist',
            'fields' => array(
                'automatorwp_ti_woocommerce_wishlist_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_FILE,
                    'item_name' => 'WP Ti Woocommerce Wishlist',
                ),
            )
        );

        return $meta_boxes;

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - Ti Woocommerce Wishlist requires %s and %s and %s in order to work. Please install and activate them.', 'automatorwp-ti-woocommerce-wishlist' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">Woocommerce </a>',
                        '<a href="https://wordpress.org/plugins/ti-woocommerce-wishlist/" target="_blank">Ti Woocommerce Wishlist</a>',

                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

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

        if ( ! class_exists( 'WooCommerce' ) ) {
            return false;
        }

         if ( ! class_exists( 'TInvWL' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_ti_woocommerce_wishlist_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-ti-woocommerce-wishlist' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-ti-woocommerce-wishlist', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-ti-woocommerce-wishlist/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-ti-woocommerce-wishlist/ folder
            load_textdomain( 'automatorwp-ti-woocommerce-wishlist', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-ti-woocommerce-wishlist/languages/ folder
            load_textdomain( 'automatorwp-ti-woocommerce-wishlist', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-ti-woocommerce-wishlist', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_TI_WOOCOMMERCE_WISHLIST instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_TI_WOOCOMMERCE_WISHLIST The one true AutomatorWP_TI_WOOCOMMERCE_WISHLIST
 */
function AutomatorWP_TI_WOOCOMMERCE_WISHLIST() {
    return AutomatorWP_TI_WOOCOMMERCE_WISHLIST::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_TI_WOOCOMMERCE_WISHLIST' );
