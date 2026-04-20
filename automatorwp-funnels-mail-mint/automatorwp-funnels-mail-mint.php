<?php
/**
 * Plugin Name:           AutomatorWP - WP Funnels ↔ Mail Mint
 * Plugin URI:            
 * Description:           Integración AutomatorWP ↔ WP Funnels (Mail Mint sync). Skeleton plugin.
 * Version:               0.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-mailmint
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Mailmint
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Mailmint {

    /**
     * @var AutomatorWP_Mailmint
     */
    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Mailmint();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_MAILMINT_VER', '0.1.0' );
        define( 'AUTOMATORWP_MAILMINT_FILE', __FILE__ );
        define( 'AUTOMATORWP_MAILMINT_DIR', plugin_dir_path( AUTOMATORWP_MAILMINT_FILE ) );
        define( 'AUTOMATORWP_MAILMINT_URL', plugin_dir_url( AUTOMATORWP_MAILMINT_FILE ) );
    }

    private function includes() {
        // Always include admin and ajax to allow settings screen even if requirements not met
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/scripts.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/tags.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/functions.php';

        // Load triggers/actions only if requirements met or forced in development
        $force_load = defined( 'AUTOMATORWP_MAILMINT_FORCE_LOAD_TRIGGERS' ) && AUTOMATORWP_MAILMINT_FORCE_LOAD_TRIGGERS;
        if ( $this->meets_requirements() || $force_load ) {
            foreach ( glob( AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/*.php' ) as $file ) {
                require_once $file;
            }
            foreach ( glob( AUTOMATORWP_MAILMINT_DIR . 'includes/actions/*.php' ) as $file ) {
                require_once $file;
            }

            // Ensure AutomatorWP registers our triggers/actions even if its registration hooks
            // were already fired before these files were included. Fire the registration
            // hooks now so anonymous callbacks inside the trigger/action files run.
            if ( function_exists( 'do_action' ) ) {
                do_action( 'automatorwp_register_triggers' );
                do_action( 'automatorwp_register_actions' );
            }
            // Load Mail Mint integration (maps Mail Mint hooks to AutomatorWP triggers)
            if ( file_exists( AUTOMATORWP_MAILMINT_DIR . 'includes/mailmint-integration.php' ) ) {
                require_once AUTOMATORWP_MAILMINT_DIR . 'includes/mailmint-integration.php';
            }
        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

        register_activation_hook( AUTOMATORWP_MAILMINT_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( AUTOMATORWP_MAILMINT_FILE, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    public function register_integration() {
        automatorwp_register_integration( 'mailmint', array(
            'label' => 'Mail Mint',
            'icon'  => AUTOMATORWP_MAILMINT_URL . 'assets/mailmint.svg',
        ) );
    }

    public function activate() {
        // Activation tasks
    }

    public function deactivate() {
        // Deactivation tasks
    }

    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf( __( 'AutomatorWP - Mail Mint requires %s and %s in order to work. Please install and activate them.', 'automatorwp-funnels-mail-mint' ), '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>', '<a href="https://wordpress.org/plugins/wpfunnels/" target="_blank">WP Funnels</a>' ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;
    }

    private function meets_requirements() {
        if ( ! class_exists( 'AutomatorWP' ) ) return false;

        // Check WP Funnels presence via multiple fallbacks
        if ( class_exists( 'WPFunnels' ) ) return true;
        if ( defined( 'WPFUNNELS_VERSION' ) ) return true;
        if ( file_exists( WP_PLUGIN_DIR . '/wpfunnels/wpfunnels.php' ) ) return true;
        // Some WP Funnels installations use a different main file name (wpfnl.php)
        if ( file_exists( WP_PLUGIN_DIR . '/wpfunnels/wpfnl.php' ) ) return true;

        // Fallback: check for any PHP file inside WP Funnels plugin folder
        $wpf_files = glob( WP_PLUGIN_DIR . '/wpfunnels/*.php' );
        if ( ! empty( $wpf_files ) ) return true;

        // Fallback: check active plugins option (covers different main file names)
        if ( function_exists( 'get_option' ) ) {
            $active_plugins = (array) get_option( 'active_plugins', array() );
            if ( in_array( 'wpfunnels/wpfnl.php', $active_plugins, true ) || in_array( 'wpfunnels/wpfunnels.php', $active_plugins, true ) ) {
                return true;
            }
        }

        return false;
    }

    public function load_textdomain() {
        $lang_dir = AUTOMATORWP_MAILMINT_DIR . '/languages/';
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-funnels-mail-mint' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-funnels-mail-mint', $locale );
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-funnels-mail-mint/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-funnels-mail-mint', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-funnels-mail-mint', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-funnels-mail-mint', false, $lang_dir );
        }
    }

}

function AutomatorWP_Mailmint() {
    return AutomatorWP_Mailmint::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Mailmint' );
