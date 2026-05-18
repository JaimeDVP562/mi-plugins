<?php
/**
 * Plugin Name:           AutomatorWP - WooCommerce
 * Plugin URI:            https://automatorwp.com/add-ons/woocommerce/
 * Description:           Connect AutomatorWP with WooCommerce.
 * Version:               1.5.2
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-woocommerce
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\WooCommerce
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_WooCommerce {

    /**
     * @var         AutomatorWP_WooCommerce $instance The one true AutomatorWP_WooCommerce
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_WooCommerce self::$instance The one true AutomatorWP_WooCommerce
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_WooCommerce();
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
        define( 'AUTOMATORWP_WOOCOMMERCE_VER', '1.5.2' );

        // Plugin file
        define( 'AUTOMATORWP_WOOCOMMERCE_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_WOOCOMMERCE_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_WOOCOMMERCE_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/view-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/add-product-to-cart.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/add-product-variation-to-cart.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/remove-product-from-cart.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/remove-product-variation-from-cart.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-product-variation.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-product-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-product-tag.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/complete-purchase.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/complete-purchase-number.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-total.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-payment-method.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-status-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-status-product-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-status-product-tag.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-specific-products.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/order-contains-specific-products.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/vendor-sale.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/review-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/review-product-rating.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/user-lifetime-value.php';
            // WooCommerce Memberships
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/membership-created.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/membership-cancelled.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/membership-expired.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/membership-status.php';
            // WooCommerce Subscriptions
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-subscription.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-subscription-variation.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-renewal.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-renewal-variation.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/cancel-subscription.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-expired.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-variation-switch.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/payment-retry-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/cancel-subscription-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-renewal-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-expired-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/subscription-status-category.php';
			require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/purchase-subscription-category.php';

            // Anonymous Triggers
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-product-variation.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-product-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-product-tag.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-complete-purchase.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-total.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-purchase-payment-method.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-order-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-order-status-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-order-status-product-category.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/triggers/anonymous-order-status-product-tag.php';

            // Actions
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/add-email-to-coupon.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/add-user-to-coupon.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/create-coupon.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/remove-user-from-coupon.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/change-status-order.php';
            // WooCommerce Memberships
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/add-membership.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/remove-membership.php';
            // WooCommerce Subscriptions
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/cancel-user-subscription.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/actions/retry-subscription-payment.php';

            // Filters
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters/user-purchased-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters/user-not-purchased-product.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters/user-subscription-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters/user-subscription-variation-status.php';
            require_once AUTOMATORWP_WOOCOMMERCE_DIR . 'includes/filters/user-lifetime-value-filter.php';

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

        automatorwp_register_integration( 'woocommerce', array(
            'label' => 'WooCommerce',
            'icon'  => AUTOMATORWP_WOOCOMMERCE_URL . 'assets/woocommerce.svg',
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

        $meta_boxes['automatorwp-woocommerce-license'] = array(
            'title' => 'WooCommerce',
            'fields' => array(
                'automatorwp_woocommerce_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_WOOCOMMERCE_FILE,
                    'item_name' => 'WooCommerce',
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
                        __( 'AutomatorWP - WooCommerce requires %s and %s in order to work. Please install and activate them.', 'automatorwp-woocommerce' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
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

        if ( ! class_exists( 'WooCommerce' ) ) {
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
        $lang_dir = AUTOMATORWP_WOOCOMMERCE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_woocommerce_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-woocommerce' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-woocommerce', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-woocommerce/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-woocommerce/ folder
            load_textdomain( 'automatorwp-woocommerce', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-woocommerce/languages/ folder
            load_textdomain( 'automatorwp-woocommerce', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-woocommerce', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_WooCommerce instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_WooCommerce The one true AutomatorWP_WooCommerce
 */
function AutomatorWP_WooCommerce() {
    return AutomatorWP_WooCommerce::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_WooCommerce' );
