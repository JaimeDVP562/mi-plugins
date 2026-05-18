<?php
/**
 * Plugin Name:     GamiPress - FluentCart Partial Payments
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-fluentcart-partial-payments
 * Description:     Let users partially pay a FluentCart purchase by using GamiPress points.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-fluentcart-partial-payments
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\FluentCart\Partial_Payments
 * @author          GamiPress <contact@gamipress.com>
 * @copyright       Copyright (c) GamiPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class GamiPress_FluentCart_Partial_Payments {

    /**
     * @var GamiPress_FluentCart_Partial_Payments $instance Singleton instance
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @since  1.0.0
     * @return GamiPress_FluentCart_Partial_Payments
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new GamiPress_FluentCart_Partial_Payments();
            self::$instance->constants();
            self::$instance->libraries();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @since  1.0.0
     * @return void
     */
    private function constants() {

        // Plugin version
        define( 'GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_VER', '1.0.0' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin libraries
     *
     * @since  1.0.0
     * @return void
     */
    private function libraries() {

        if ( $this->meets_requirements() ) {
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'libraries/points-rate-field-type.php';
        }
    }

    /**
     * Include plugin files
     *
     * @since  1.0.0
     * @return void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/filters.php';
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'includes/template-functions.php';
        }
    }

    /**
     * Setup plugin hooks
     *
     * @since  1.0.0
     * @return void
     */
    private function hooks() {

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init',          array( $this, 'load_textdomain' ) );
    }

    /**
     * Activation hook
     *
     * @since  1.0.0
     */
    public function activate() {
        // Future: flush rewrite rules, create tables, etc.
    }

    /**
     * Deactivation hook
     *
     * @since  1.0.0
     */
    public function deactivate() {
        // Future: cleanup tasks
    }

    /**
     * Admin notices when requirements are not met
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        /* translators: 1: GamiPress link, 2: min version, 3: FluentCart link */
                        __( 'GamiPress - FluentCart Partial Payments requires %1$s (%2$s or higher) and %3$s in order to work. Please install and activate them.', 'gamipress-fluentcart-partial-payments' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER,
                        '<a href="https://wordpress.org/plugins/fluent-cart/" target="_blank">FluentCart</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>

        <?php endif;
    }

    /**
     * Check if all plugin requirements are met
     *
     * @since  1.0.0
     * @return bool
     */
    private function meets_requirements() {

        return (
            class_exists( 'GamiPress' )
            && defined( 'GAMIPRESS_VER' )
            && version_compare( GAMIPRESS_VER, GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_GAMIPRESS_MIN_VER, '>=' )
            && function_exists( 'FluentCart' )
        );
    }

    /**
     * Load plugin textdomain for translations
     *
     * @since  1.0.0
     * @return void
     */
    public function load_textdomain() {

        $lang_dir = GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_fluentcart_partial_payments_languages_directory', $lang_dir );

        $locale  = apply_filters( 'plugin_locale', get_locale(), 'gamipress-fluentcart-partial-payments' );
        $mofile  = sprintf( '%1$s-%2$s.mo', 'gamipress-fluentcart-partial-payments', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/gamipress-fluentcart-partial-payments/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            load_textdomain( 'gamipress-fluentcart-partial-payments', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            load_textdomain( 'gamipress-fluentcart-partial-payments', $mofile_local );
        } else {
            load_plugin_textdomain( 'gamipress-fluentcart-partial-payments', false, $lang_dir );
        }
    }
}

/**
 * Returns the main plugin instance
 *
 * @since  1.0.0
 * @return GamiPress_FluentCart_Partial_Payments
 */
function GamiPress_FluentCart_Partial_Payments() {
    return GamiPress_FluentCart_Partial_Payments::instance();
}
add_action( 'plugins_loaded', 'GamiPress_FluentCart_Partial_Payments' );
