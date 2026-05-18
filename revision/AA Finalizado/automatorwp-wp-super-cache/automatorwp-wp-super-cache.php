<?php
/**
 * Plugin Name:           AutomatorWP - WP Super Cache
 * Plugin URI:            https://automatorwp.com/add-ons/wp-super-cache/
 * Description:           Connect AutomatorWP with WP Super Cache.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-wp-super-cache
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\WPSuperCache
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_WP_Super_Cache {

    /**
     * @var         AutomatorWP_WP_Super_Cache $instance The one true AutomatorWP_WP_Super_Cache
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_WP_Super_Cache self::$instance The one true AutomatorWP_WP_Super_Cache
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_WP_Super_Cache();
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

        define( 'AUTOMATORWP_WP_SUPER_CACHE_VER', '1.0.0' );
        define( 'AUTOMATORWP_WP_SUPER_CACHE_FILE', __FILE__ );
        define( 'AUTOMATORWP_WP_SUPER_CACHE_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_WP_SUPER_CACHE_URL', plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/tags.php';

            // WP Super Cache Actions
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/actions/purge-all-cache.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/actions/purge-post-cache.php';
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/actions/purge-url-cache.php';

            // WP Super Cache Triggers
            require_once AUTOMATORWP_WP_SUPER_CACHE_DIR . 'includes/triggers/trigger-all-cache-purged.php';

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
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'wp_super_cache', array(
            'label' => 'WP Super Cache',
            'icon'  => AUTOMATORWP_WP_SUPER_CACHE_URL . 'assets/img/wp-super-cache.png',
        ) );

    }

    /**
     * Plugin admin notices
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - WP Super Cache requires %s and %s in order to work. Please install and activate them.', 'automatorwp-wp-super-cache' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/wp-super-cache/" target="_blank">WP Super Cache</a>'
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

        if ( ! function_exists( 'wp_cache_clear_cache' ) ) {
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

        $lang_dir = AUTOMATORWP_WP_SUPER_CACHE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_wp_super_cache_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-wp-super-cache' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-wp-super-cache', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-wp-super-cache/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-wp-super-cache', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-wp-super-cache', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-wp-super-cache', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_WP_Super_Cache instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_WP_Super_Cache The one true AutomatorWP_WP_Super_Cache
 */
function AutomatorWP_WP_Super_Cache() {
    return AutomatorWP_WP_Super_Cache::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_WP_Super_Cache' );