<?php
/**
 * Plugin Name:           AutomatorWP - WP WooCommerce Bookings
 * Plugin URI:            https://automatorwp.com/add-ons/woocommercebookings/
 * Description:           Connect AutomatorWP with WP WooCommerce Bookings.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-woocommercebookings
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\WooCommerceBookings
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_WooCommerceBookings {

    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_WooCommerceBookings();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }
        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_WOOCOMMERCEBOOKINGS_VER', '1.0.0' );
        define( 'AUTOMATORWP_WOOCOMMERCEBOOKINGS_FILE', __FILE__ );
        define( 'AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_WOOCOMMERCEBOOKINGS_URL', plugin_dir_url( __FILE__ ) );
    }

    private function includes() {

        if( $this->meets_requirements() ) {

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/tags.php';

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/cancelled-booking.php';
            new AutomatorWP_WooCommerceBookings_Cancelled_Booking();

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/paid-booking.php';
            new AutomatorWP_WooCommerceBookings_Paid_Booking();

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/unpaid-booking.php';
            new AutomatorWP_WooCommerceBookings_Unpaid_Booking();

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/guest-cancelled-booking.php';
            new AutomatorWP_WooCommerceBookings_Guest_Cancelled_Booking();

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/guest-paid-booking.php';
            new AutomatorWP_WooCommerceBookings_Guest_Paid_Booking();

            require_once AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . 'includes/triggers/guest-unpaid-booking.php';
            new AutomatorWP_WooCommerceBookings_Guest_Unpaid_Booking();

        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    function register_integration() {
        automatorwp_register_integration( 'woocommercebookings', array(
            'label' => 'WP WooCommerce Bookings',
            'icon'  => AUTOMATORWP_WOOCOMMERCEBOOKINGS_URL . 'assets/woocommercebookings.svg',
        ) );
    }

    function license( $meta_boxes ) {
        $meta_boxes['automatorwp-woocommercebookings-license'] = array(
            'title' => 'WP WooCommerce Bookings',
            'fields' => array(
                'automatorwp_woocommercebookings_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_WOOCOMMERCEBOOKINGS_FILE,
                    'item_name' => 'WP WooCommerce Bookings',
                ),
            )
        );
        return $meta_boxes;
    }

    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - WP WooCommerce Bookings requires %s, %s and %s in order to work. Please install and activate them.', 'automatorwp-woocommercebookings' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>',
                        '<a href="https://woocommerce.com/products/woocommerce-bookings/" target="_blank">WooCommerce Bookings</a>'
                    ); ?>
                </p>
            </div>
            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>
        <?php endif;
    }

    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            return false;
        }

        if ( ! class_exists( 'WC_Bookings' ) ) {
            return false;
        }

        return true;

    }

    public function load_textdomain() {
        $lang_dir = AUTOMATORWP_WOOCOMMERCEBOOKINGS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_woocommercebookings_languages_directory', $lang_dir );
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-woocommercebookings' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-woocommercebookings', $locale );

        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-woocommercebookings/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-woocommercebookings', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-woocommercebookings', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-woocommercebookings', false, $lang_dir );
        }
    }
}

function AutomatorWP_WooCommerceBookings() {
    return AutomatorWP_WooCommerceBookings::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_WooCommerceBookings' );