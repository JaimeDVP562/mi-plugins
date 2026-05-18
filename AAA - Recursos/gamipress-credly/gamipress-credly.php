<?php
/**
 * Plugin Name:     GamiPress - Credly
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-credly
 * Description:     Sync achievements and user earnings with Credly.
 * Version:         1.0.4
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-credly
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Credly
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Credly {

    /**
     * @var         GamiPress_Credly $instance The one true GamiPress_Credly
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Credly self::$instance The one true GamiPress_Credly
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_Credly();
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
        define( 'GAMIPRESS_CREDLY_VER', '1.0.4' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_CREDLY_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_CREDLY_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_CREDLY_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_CREDLY_URL', plugin_dir_url( __FILE__ ) );
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

            require_once GAMIPRESS_CREDLY_DIR . 'includes/admin.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/filters.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/functions.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_CREDLY_DIR . 'includes/widgets.php';

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
                        __( 'GamiPress - Credly requires %s (%s or higher) in order to work. Please install and activate them.', 'gamipress-credly' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_CREDLY_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_CREDLY_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_CREDLY_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_credly_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-credly' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-credly', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-credly/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-credly', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-credly', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-credly', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Credly instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Credly The one true GamiPress_Credly
 */
function GamiPress_Credly() {
    return GamiPress_Credly::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Credly' );
