<?php
/**
 * Plugin Name:           AutomatorWP - W3 Total Cache
 * Plugin URI:            https://automatorwp.com/add-ons/w3-total-cache/
 * Description:           Connect AutomatorWP with W3 Total Cache. Purge caches automatically through automations.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-w3tc
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\W3TC
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_W3TC {

    /**
     * @var         AutomatorWP_W3TC $instance The one true AutomatorWP_W3TC
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_W3TC self::$instance The one true AutomatorWP_W3TC
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_W3TC();
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

        define( 'AUTOMATORWP_W3TC_VER', '1.0.0' );
        define( 'AUTOMATORWP_W3TC_FILE', __FILE__ );
        define( 'AUTOMATORWP_W3TC_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_W3TC_URL', plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_W3TC_DIR . 'includes/functions.php';

            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-all.php';
            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-post.php';
            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-url.php';

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

        automatorwp_register_integration( 'w3_total_cache', array(
                'label' => 'W3 Total Cache',
                'icon'  => AUTOMATORWP_W3TC_URL . 'assets/w3-total-cache.svg',
        ) );

    }

    /**
     * Plugin admin notices
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if( !$this->meets_requirements() && !defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                            __( 'AutomatorWP - W3 Total Cache requires %s and %s in order to work. Please install and activate them.', 'automatorwp-w3tc' ),
                            '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                            '<a href="https://wordpress.org/plugins/w3-total-cache/" target="_blank">W3 Total Cache</a>'
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

        if( !class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        if( !function_exists( 'w3tc_flush_all' ) ) {
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

        $lang_dir = AUTOMATORWP_W3TC_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_w3tc_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-w3tc' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-w3tc', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-w3tc/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-w3tc', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-w3tc', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-w3tc', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_W3TC instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_W3TC The one true AutomatorWP_W3TC
 */
function AutomatorWP_W3TC() {
    return AutomatorWP_W3TC::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_W3TC' );