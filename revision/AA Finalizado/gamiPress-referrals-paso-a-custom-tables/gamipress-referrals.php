<?php
/**
 * Plugin Name:     GamiPress - Referrals
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-referrals
 * Description:     Add a complete referral system to award users who refer visitors and sign ups.
 * Version:         1.2.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-referrals
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Referrals
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Referrals {

    /**
     * @var         GamiPress_Referrals $instance The one true GamiPress_Referrals
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Referrals self::$instance The one true GamiPress_Referrals
     */
    public static function instance() {

        if( !self::$instance ) {

            self::$instance = new GamiPress_Referrals();
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
        define( 'GAMIPRESS_REFERRALS_VER', '1.2.0' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_REFERRALS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_REFERRALS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_REFERRALS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_REFERRALS_URL', plugin_dir_url( __FILE__ ) );
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

            require_once GAMIPRESS_REFERRALS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/custom-tables.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/custom-tables-views.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/upgrades.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/sale-functions.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/logs.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/requirements.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/rules-engine.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/triggers.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/users.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/widgets.php';

            // Integrations
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/integrations/easy-digital-downloads.php';
            require_once GAMIPRESS_REFERRALS_DIR . 'includes/integrations/woocommerce.php';

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

        add_action( 'init', array( $this, 'load_textdomain' ) );
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
                        __( 'GamiPress - Referrals requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-referrals' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_REFERRALS_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_REFERRALS_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_REFERRALS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_referrals_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-referrals' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-referrals', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-referrals/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-referrals', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-referrals', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-referrals', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Referrals instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Referrals The one true GamiPress_Referrals
 */
function GamiPress_Referrals() {
    return GamiPress_Referrals::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Referrals' );
