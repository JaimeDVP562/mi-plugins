<?php
/**
 * Plugin Name:     GamiPress - WooCommerce Partial Payments
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-wc-partial-payments
 * Description:     Let users partially pay a WooCommerce purchase by using points.
 * Version:         1.2.4
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-wc-partial-payments
 * WC requires at least:  3.0
 * WC tested up to:       7.4
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\WooCommerce\Partial_Payments
 * @author          GamiPress <contact@gamipress.com>
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_WooCommerce_Partial_Payments {

    /**
     * @var         GamiPress_WooCommerce_Partial_Payments $instance The one true GamiPress_WooCommerce_Partial_Payments
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_WooCommerce_Partial_Payments self::$instance The one true GamiPress_WooCommerce_Partial_Payments
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_WooCommerce_Partial_Payments();
            self::$instance->constants();
            self::$instance->libraries();
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
        define( 'GAMIPRESS_WC_PARTIAL_PAYMENTS_VER', '1.2.4' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_WC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_WC_PARTIAL_PAYMENTS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_WC_PARTIAL_PAYMENTS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin libraries
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function libraries() {

        if( $this->meets_requirements() ) {

            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'libraries/points-rate-field-type.php';

        }
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

            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/filters.php';
            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'includes/template-functions.php';

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
        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    function activate() {

        if( $this->meets_requirements() ) {

        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    function deactivate() {

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - WooCommerce Partial Payments requires %s (%s or higher) and %s in order to work. Please install and activate them.', 'gamipress-wc-partial-payments' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_WC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER,
                        '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>

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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_WC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER, '>=' )
            && class_exists( 'WooCommerce' ) ) {
            return true;
        } else {
            return false;
        }

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
        $lang_dir = GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_wc_partial_payments_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-wc-partial-payments' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-wc-partial-payments', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-wc-partial-payments/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-wc-partial-payments', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-wc-partial-payments', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-wc-partial-payments', false, $lang_dir );
        }
    }

}

/**
 * The main function responsible for returning the one true GamiPress_WooCommerce_Partial_Payments instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_WooCommerce_Partial_Payments The one true GamiPress_WooCommerce_Partial_Payments
 */
function GamiPress_WC_Partial_Payments() {
    return GamiPress_WooCommerce_Partial_Payments::instance();
}
add_action( 'plugins_loaded', 'GamiPress_WC_Partial_Payments' );
