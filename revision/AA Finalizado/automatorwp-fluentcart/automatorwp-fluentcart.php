<?php
/**
 * Plugin Name:           AutomatorWP - FluentCart
 * Plugin URI:            https://automatorwp.com/add-ons/fluentcart/
 * Description:           Connect AutomatorWP with FluentCart.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-fluentcart
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\FluentCart
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_FluentCart {

    /**
     * @var         AutomatorWP_FluentCart $instance The one true AutomatorWP_FluentCart
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_FluentCart self::$instance The one true AutomatorWP_FluentCart
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_FluentCart();
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

        define( 'AUTOMATORWP_FLUENTCART_VER', '1.0.0' );
        define( 'AUTOMATORWP_FLUENTCART_FILE', __FILE__ );
        define( 'AUTOMATORWP_FLUENTCART_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_FLUENTCART_URL', plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/tags.php';

            // FluentCart Triggers
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/order-paid.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/subscription-activated.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/subscription-cancelled.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/order-created.php';

            // FluentCart Actions
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/create-coupon.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/add-order-note.php';
            require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/cancel-subscription.php';

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

        automatorwp_register_integration( 'fluentcart', array(
            'label' => 'FluentCart',
            'icon'  => AUTOMATORWP_FLUENTCART_URL . 'assets/img/fluentcart.svg',
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
                        __( 'AutomatorWP - FluentCart requires %s and %s in order to work. Please install and activate them.', 'automatorwp-fluentcart' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://fluentcart.com/" target="_blank">FluentCart</a>'
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

        if ( ! defined( 'FLUENTCART_PLUGIN_PATH' ) ) {
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

        $lang_dir = AUTOMATORWP_FLUENTCART_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_fluentcart_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-fluentcart' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-fluentcart', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-fluentcart/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-fluentcart', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-fluentcart', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-fluentcart', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_FluentCart instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_FluentCart The one true AutomatorWP_FluentCart
 */
function AutomatorWP_FluentCart() {
    return AutomatorWP_FluentCart::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_FluentCart' );