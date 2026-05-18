<?php
/**
 * Plugin Name:     GamiPress - SureCart Partial Payments
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-sc-partial-payments
 * Description:     Let users partially pay a SureCart purchase by using points.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-sc-partial-payments
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\SureCart\Partial_Payments
 * @author          GamiPress <contact@gamipress.com>
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_SureCart_Partial_Payments {

    /**
     * @var         GamiPress_SureCart_Partial_Payments $instance The one true GamiPress_SureCart_Partial_Payments
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_SureCart_Partial_Payments self::$instance The one true GamiPress_SureCart_Partial_Payments
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_SureCart_Partial_Payments();
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
        define( 'GAMIPRESS_SC_PARTIAL_PAYMENTS_VER', '1.0.0' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_SC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_SC_PARTIAL_PAYMENTS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_SC_PARTIAL_PAYMENTS_URL', plugin_dir_url( __FILE__ ) );
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

            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'libraries/points-rate-field-type.php';

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

            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/filters.php';
            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'includes/template-functions.php';

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
                        __( 'GamiPress - SureCart Partial Payments requires %s (%s or higher) and %s in order to work. Please install and activate them.', 'gamipress-sc-partial-payments' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_SC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER,
                        '<a href="https://wordpress.org/plugins/surecart/" target="_blank">SureCart</a>'
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_SC_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER, '>=' )
            && class_exists( 'SureCart' ) ) {
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
        $lang_dir = GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_sc_partial_payments_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-sc-partial-payments' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-sc-partial-payments', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-sc-partial-payments/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-sc-partial-payments', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-sc-partial-payments', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-sc-partial-payments', false, $lang_dir );
        }
    }

}

/**
 * The main function responsible for returning the one true GamiPress_SureCart_Partial_Payments instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_SureCart_Partial_Payments The one true GamiPress_SureCart_Partial_Payments
 */
function GamiPress_SC_Partial_Payments() {
    return GamiPress_SureCart_Partial_Payments::instance();
}
add_action( 'plugins_loaded', 'GamiPress_SC_Partial_Payments' );
