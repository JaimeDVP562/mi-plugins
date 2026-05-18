<?php
/**
 * Plugin Name:     GamiPress - Daily Login Rewards
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-daily-login-rewards
 * Description:     Add daily rewards to perform your site visits.
 * Version:         1.1.8
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-daily-login-rewards
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Daily_Login_Rewards
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Daily_Login_Rewards {

    /**
     * @var         GamiPress_Daily_Login_Rewards $instance The one true GamiPress_Daily_Login_Rewards
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Daily_Login_Rewards self::$instance The one true GamiPress_Daily_Login_Rewards
     */
    public static function instance() {

        if( !self::$instance ) {

            self::$instance = new GamiPress_Daily_Login_Rewards();
            self::$instance->constants();
            self::$instance->classes();
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
        define( 'GAMIPRESS_DAILY_LOGIN_REWARDS_VER', '1.1.8' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_DAILY_LOGIN_REWARDS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_DAILY_LOGIN_REWARDS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_DAILY_LOGIN_REWARDS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_DAILY_LOGIN_REWARDS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin classes
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function classes() {

        if( $this->meets_requirements() ) {

            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'classes/class-gamipress-rewards-calendar.php';

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

            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/content-filters.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/logs.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/post-types.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/rewards.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/blocks.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/triggers.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/user-earnings.php';
            require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'includes/widgets.php';

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
                        __( 'GamiPress - Daily Login Rewards requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-daily-login-rewards' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_DAILY_LOGIN_REWARDS_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_DAILY_LOGIN_REWARDS_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_daily_login_rewards_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-daily-login-rewards' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-daily-login-rewards', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-daily-login-rewards/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-daily-login-rewards', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-daily-login-rewards', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-daily-login-rewards', false, $lang_dir );
        }
    }

}

/**
 * The main function responsible for returning the one true GamiPress_Daily_Login_Rewards instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Daily_Login_Rewards The one true GamiPress_Daily_Login_Rewards
 */
function GamiPress_Daily_Login_Rewards() {
    return GamiPress_Daily_Login_Rewards::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Daily_Login_Rewards' );
