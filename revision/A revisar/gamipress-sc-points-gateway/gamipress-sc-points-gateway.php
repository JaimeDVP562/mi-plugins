<?php
/**
 * Plugin Name:     GamiPress - SureCart Points Gateway
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-sc-points-gateway
 * Description:     Use GamiPress points types as a payment gateway for SureCart.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-sc-points-gateway
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\SureCart\Points_Gateway
 * @author          GamiPress <contact@gamipress.com>
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_SureCart_Points_Gateway {

    /**
     * @var         GamiPress_SureCart_Points_Gateway $instance The one true GamiPress_SureCart_Points_Gateway
     * @since       1.0.0
     */
    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_SureCart_Points_Gateway();
            self::$instance->constants();
            self::$instance->classes();
            self::$instance->includes();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function constants() {
        define( 'GAMIPRESS_SC_POINTS_GATEWAY_VER', '1.0.0' );
        define( 'GAMIPRESS_SC_POINTS_GATEWAY_GAMIPRESS_MIN_VER', '3.0.0' );
        define( 'GAMIPRESS_SC_POINTS_GATEWAY_FILE', __FILE__ );
        define( 'GAMIPRESS_SC_POINTS_GATEWAY_DIR', plugin_dir_path( __FILE__ ) );
        define( 'GAMIPRESS_SC_POINTS_GATEWAY_URL', plugin_dir_url( __FILE__ ) );
    }

    private function classes() {
        if( $this->meets_requirements() ) {
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'classes/class-gamipress-sc-points-gateway.php';
        }
    }

    private function includes() {
        if( $this->meets_requirements() ) {
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'includes/admin.php';
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'includes/functions.php';
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'includes/gateway.php';
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_SC_POINTS_GATEWAY_DIR . 'includes/template-functions.php';
        }
    }

    private function hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    function activate() {
        if( $this->meets_requirements() ) {}
    }

    function deactivate() {}

    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - SureCart Points Gateway requires %s (%s or higher) and %s in order to work. Please install and activate them.', 'gamipress-sc-points-gateway' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_SC_POINTS_GATEWAY_GAMIPRESS_MIN_VER,
                        '<a href="https://wordpress.org/plugins/surecart/" target="_blank">SureCart</a>'
                    ); ?>
                </p>
            </div>
            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>
        <?php endif;
    }

    private function meets_requirements() {
        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_SC_POINTS_GATEWAY_GAMIPRESS_MIN_VER, '>=' )
            && class_exists( 'SureCart' ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function load_textdomain() {
        $lang_dir = GAMIPRESS_SC_POINTS_GATEWAY_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_sc_points_gateway_languages_directory', $lang_dir );
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-sc-points-gateway' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-sc-points-gateway', $locale );
        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/gamipress-sc-points-gateway/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'gamipress-sc-points-gateway', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'gamipress-sc-points-gateway', $mofile_local );
        } else {
            load_plugin_textdomain( 'gamipress-sc-points-gateway', false, $lang_dir );
        }
    }
}

/**
 * The main function responsible for returning the one true GamiPress_SureCart_Points_Gateway instance
 *
 * @since       1.0.0
 * @return      \GamiPress_SureCart_Points_Gateway The one true GamiPress_SureCart_Points_Gateway
 */
function GamiPress_SC_Points_Gateway() {
    return GamiPress_SureCart_Points_Gateway::instance();
}
add_action( 'plugins_loaded', 'GamiPress_SC_Points_Gateway' );
