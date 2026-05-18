<?php
/**
 * Plugin Name:           AutomatorWP - Free Downloads WooCommerce
 * Plugin URI:            https://automatorwp.com/add-ons/free-downloads-woocommerce/
 * Description:           Connect AutomatorWP with Free Downloads WooCommerce.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-free-downloads-woocommerce
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Free_Downloads_WooCommerce
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Free_Downloads_WooCommerce {
    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Free_Downloads_WooCommerce();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_URL', plugin_dir_url( __FILE__ ) );
    }

    private function includes() {

        if( $this->meets_requirements() ) {

            require_once AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . 'includes/triggers/trigger-download-product.php';
            require_once AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . 'includes/triggers/trigger-download-product-category.php';
            require_once AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . 'includes/triggers/trigger-download-product-tag.php';
        }
    }

    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
    }
    function register_integration() {

        automatorwp_register_integration( 'free_downloads_woocommerce', array(
            'label' => 'Free Downloads WooCommerce',
            'icon'  => AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_URL . 'assets/free-downloads-woocommerce.svg',
        ) );

    }
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - Free Downloads WooCommerce requires %s and %s in order to work. Please install and activate them.', 'automatorwp-free-downloads-woocommerce' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/download-now-for-woocommerce/" target="_blank">Free Downloads WooCommerce</a>'
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

        if ( ! defined( 'SOMDN_FILE' ) ) {
            return false;
        }

        return true;

    }
    public function load_textdomain() {

        $lang_dir = AUTOMATORWP_FREE_DOWNLOADS_WOOCOMMERCE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_free_downloads_woocommerce_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-free-downloads-woocommerce' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-free-downloads-woocommerce', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-free-downloads-woocommerce/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-free-downloads-woocommerce', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-free-downloads-woocommerce', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-free-downloads-woocommerce', false, $lang_dir );
        }

    }

}

function AutomatorWP_Free_Downloads_WooCommerce() {
    return AutomatorWP_Free_Downloads_WooCommerce::instance();
}

add_action( 'plugins_loaded', 'AutomatorWP_Free_Downloads_WooCommerce' );


