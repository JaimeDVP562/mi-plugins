<?php
/**
 * Plugin Name:           AutomatorWP - SendPulse
 * Plugin URI:            https://automatorwp.com/add-ons/sendpulse/
 * Description:           Connect AutomatorWP with SendPulse.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-sendpulse
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SendPulse
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Sendpulse {

    /**
     * @var         AutomatorWP_Integration_Sendpulse $instance The one true AutomatorWP_Integration_Sendpulse
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Sendpulse self::$instance The one true AutomatorWP_Integration_Sendpulse
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Sendpulse();
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
        define( 'AUTOMATORWP_SENDPULSE_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_SENDPULSE_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SENDPULSE_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SENDPULSE_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/webhook.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/scripts.php';
            // Auto-include all action files
            $actions_dir = AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/';
            if ( is_dir( $actions_dir ) ) {
                foreach ( glob( $actions_dir . '*.php' ) as $action_file ) {
                    require_once $action_file;
                }
            }
            // Upgrades (follows AutomatorWP upgrades structure)
            if ( file_exists( AUTOMATORWP_SENDPULSE_DIR . 'includes/admin/upgrades.php' ) ) {
                require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/admin/upgrades.php';
            }

            // Actions
            // Note: legacy integration actions were removed during cleanup.
            // Add SendPulse-specific action includes here when implemented.

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

        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'sendpulse', array(
            'label' => 'SendPulse',
            'icon'  => AUTOMATORWP_SENDPULSE_URL . 'assets/sendpulse.svg',
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

        $meta_boxes['automatorwp-sendpulse-license'] = array(
                'title' => 'SendPulse',
                'fields' => array(
                        'automatorwp_sendpulse_license' => array(
                                'type' => 'edd_license',
                                'file' => AUTOMATORWP_SENDPULSE_FILE,
                                'item_name' => 'SendPulse',
                        ),
                )
        );

        return $meta_boxes;

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

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                            __( 'AutomatorWP - SendPulse requires %s in order to work. Please install and activate it.', 'automatorwp-sendpulse' ),
                            '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
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
        $lang_dir = AUTOMATORWP_SENDPULSE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_sendpulse_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-sendpulse' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-sendpulse', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-sendpulse/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-sendpulse/ folder
            load_textdomain( 'automatorwp-sendpulse', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-sendpulse/languages/ folder
            load_textdomain( 'automatorwp-sendpulse', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-sendpulse', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Sendpulse instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Sendpulse The one true AutomatorWP_Integration_Sendpulse
 */
function AutomatorWP_Integration_Sendpulse() {
    return AutomatorWP_Integration_Sendpulse::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Sendpulse' );

// Quick startup probe: write a tiny WP_DEBUG-only entry to a plugin-local file so we can confirm the plugin is loading.
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    try {
        $wp_content = defined( 'WP_CONTENT_DIR' ) ? rtrim( WP_CONTENT_DIR, "\\/" ) : rtrim( ABSPATH, "\\/" ) . DIRECTORY_SEPARATOR . 'wp-content';
        $probe_file = $wp_content . DIRECTORY_SEPARATOR . 'automatorwp-sendpulse-debug.log';
        @file_put_contents( $probe_file, date( 'c' ) . " | plugin_loaded\n", FILE_APPEND | LOCK_EX );
    } catch ( Exception $e ) {
        // ignore
    }
}

// Backwards compatibility: keep old class name and helper function so external
// code that depended on the previous `AutomatorWP_Sendpulse` symbol keeps
// working. This performs a safe alias and helper wrapper without changing
// the current integration implementation.
if ( ! function_exists( 'AutomatorWP_Sendpulse' ) ) {
    function AutomatorWP_Sendpulse() {
        return AutomatorWP_Integration_Sendpulse();
    }
}

if ( ! class_exists( 'AutomatorWP_Sendpulse' ) && class_exists( 'AutomatorWP_Integration_Sendpulse' ) ) {
    class_alias( 'AutomatorWP_Integration_Sendpulse', 'AutomatorWP_Sendpulse' );
}

