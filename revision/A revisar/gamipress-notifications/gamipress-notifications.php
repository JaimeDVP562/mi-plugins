<?php
/**
 * Plugin Name:     GamiPress - Notifications
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-notifications
 * Description:     Instantly notify of achievements, steps and/or points awards completion to your users.
 * Version:         1.5.6
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-notifications
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Notifications
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Notifications {

    /**
     * @var         GamiPress_Notifications $instance The one true GamiPress_Notifications
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Notifications self::$instance The one true GamiPress_Notifications
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new GamiPress_Notifications();
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
        define( 'GAMIPRESS_NOTIFICATIONS_VER', '1.5.6' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_NOTIFICATIONS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_NOTIFICATIONS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_NOTIFICATIONS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_NOTIFICATIONS_URL', plugin_dir_url( __FILE__ ) );
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

            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/custom-tables.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/filters.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/content-filters.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_NOTIFICATIONS_DIR . 'integrations/fluentcommunity.php';

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
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', 'gamipress_notifications_register_custom_tables' );
    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    static function activate() {

        GamiPress_Notifications::instance();

        // Get stored version
        if( gamipress_is_network_wide_active() ) {
            $stored_version = get_site_option( 'gamipress_notifications_version', '1.0.0' );
        } else {
            $stored_version = get_option( 'gamipress_notifications_version', '1.0.0' );
        }

        // Setup default GamiPress options
        if( gamipress_is_network_wide_active() ) {
            $gamipress_settings = get_site_option( 'gamipress_settings', array() );
        } else {
            $gamipress_settings = get_option( 'gamipress_settings', array() );
        }

        // Initialize default settings
        $default_settings = array(
            // Achievements
            'achievement_title_pattern' => __( 'Felicidades {user}! Has desbloqueado {title}', 'gamipress-notifications' ),
            // Steps
            'step_title_pattern' => __( 'You completed a step of the {achievement_type} {achievement_title}:', 'gamipress-notifications' ),
            'step_content_pattern' => '{label}' . "\n"
                . __( 'You need to complete the following steps to completely unlock this {achievement_type}:', 'gamipress' ) . "\n"
                . '{achievement_steps}',
            // Points awards
            'points_award_title_pattern' => __( 'You earned {points} {points_label} for completing:', 'gamipress-notifications' ),
            'points_award_content_pattern' => '{label}' . "\n"
                . __( 'Your have now a total of {points_balance} {points_balance_label}', 'gamipress-notifications' ),
            // Points deducts
            'points_deduct_title_pattern' => __( 'You lost {points} {points_label} for:', 'gamipress-notifications' ),
            'points_deduct_content_pattern' => '{label}' . "\n"
                . __( 'Your have now a total of {points_balance} {points_balance_label}', 'gamipress-notifications' ),
            // Ranks
            'rank_title_pattern' => __( 'Congratulations {user}! You reached the {rank_type}: {title}', 'gamipress-notifications' ),
            'rank_content_pattern' => '',
            // Rank requirements
            'rank_requirement_title_pattern' => __( 'You completed a requirement of the {rank_type} {rank_title}:', 'gamipress-notifications' ),
            'rank_requirement_content_pattern' => '{label}' . "\n"
                . __( 'You need to complete the following requirements to completely unlock this {rank_type}:', 'gamipress' ) . "\n"
                . '{rank_requirements}',
        );

        // Add-on settings prefix
        $prefix = 'gamipress_notifications_';

        foreach( $default_settings as $setting => $value ) {

            // If setting not exists, update it
            if( ! isset( $gamipress_settings[$prefix . $setting] ) ) {
                $gamipress_settings[$prefix . $setting] = $value;
            }

        }

        // Update GamiPress options
        if( gamipress_is_network_wide_active() ) {
            update_site_option( 'gamipress_settings', $gamipress_settings );
        } else {
            update_option( 'gamipress_settings', $gamipress_settings );
        }

        // Updated stored version
        if( gamipress_is_network_wide_active() ) {
            update_site_option( 'gamipress_notifications_version', GAMIPRESS_NOTIFICATIONS_VER );
        } else {
            update_option( 'gamipress_notifications_version', GAMIPRESS_NOTIFICATIONS_VER );
        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    static function deactivate() {

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
                        __( 'GamiPress - Notifications requires %s (%s or higher) in order to work. Please install and activate them.', 'gamipress-notifications' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_NOTIFICATIONS_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_NOTIFICATIONS_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_NOTIFICATIONS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_notifications_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-notifications' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-notifications', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-notifications/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-notifications', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-notifications', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-notifications', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Notifications instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Notifications The one true GamiPress_Notifications
 */
function GamiPress_Notifications() {
    return GamiPress_Notifications::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Notifications' );

// Setup our activation and deactivation hooks
register_activation_hook( __FILE__, array( 'GamiPress_Notifications', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GamiPress_Notifications', 'deactivate' ) );
