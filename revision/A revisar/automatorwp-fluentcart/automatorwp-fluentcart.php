<?php
/**
 * Plugin Name:  AutomatorWP - FluentCart
 * Plugin URI:   https://automatorwp.com/add-ons/fluentcart/
 * Description:  Connect AutomatorWP with FluentCart. Triggers and actions for orders, subscriptions and coupons.
 * Version:      1.0.0
 * Author:       AutomatorWP
 * Author URI:   https://automatorwp.com/
 * License:      GPLv2 or later
 * Text Domain:  automatorwp-fluentcart
 * Domain Path:  /languages
 *
 * @package AutomatorWP\FluentCart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AUTOMATORWP_FLUENTCART_VER',  '1.0.0' );
define( 'AUTOMATORWP_FLUENTCART_DIR',  plugin_dir_path( __FILE__ ) );
define( 'AUTOMATORWP_FLUENTCART_URL',  plugin_dir_url( __FILE__ ) );
define( 'AUTOMATORWP_FLUENTCART_FILE', __FILE__ );

/**
 * Main plugin class — Singleton pattern.
 *
 * @since 1.0.0
 */
final class AutomatorWP_FluentCart {

    /** @var AutomatorWP_FluentCart */
    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->hooks();
    }

    /**
     * Load all required files in dependency order.
     * Note: fluent_cart/order_created does not exist in FluentCart source.
     *
     * @since 1.0.0
     */
    private function includes() {
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/scripts.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/tags.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/order-paid.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/subscription-activated.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/triggers/subscription-cancelled.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/create-coupon.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/add-order-note.php';
        require_once AUTOMATORWP_FLUENTCART_DIR . 'includes/actions/cancel-subscription.php';
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
    }

    /**
     * Register the FluentCart integration with AutomatorWP.
     *
     * @since 1.0.0
     */
    public function register_integration() {
        automatorwp_register_integration( 'fluentcart', array(
            'label' => 'FluentCart',
            'icon'  => AUTOMATORWP_FLUENTCART_URL . 'assets/img/fluentcart.svg',
        ) );
    }
}

/**
 * Boot the plugin on plugins_loaded.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_init() {
    if ( ! defined( 'AUTOMATORWP_FILE' ) ) {
        return;
    }
    if ( ! defined( 'FLUENTCART_PLUGIN_PATH' ) ) {
        return;
    }
    AutomatorWP_FluentCart::instance();
}
add_action( 'plugins_loaded', 'automatorwp_fluentcart_init' );