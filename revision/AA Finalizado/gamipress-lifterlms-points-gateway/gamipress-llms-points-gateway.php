<?php
/**
 * Plugin Name:     GamiPress - LifterLMS Points Gateway
 * Description:     Use GamiPress points types as a payment gateway for LifterLMS.
 * Version:         1.2.1
 * Author:          GamiPress
 * Text Domain:     gamipress-llms-points-gateway
 * Domain Path:     /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class GamiPress_LifterLMS_Points_Gateway {
    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new GamiPress_LifterLMS_Points_Gateway();
            self::$instance->constants();
            add_action( 'plugins_loaded', array( self::$instance, 'includes' ), 10 );
        }
        return self::$instance;
    }

    private function constants() {
        define( 'GAMIPRESS_LLMS_POINTS_GATEWAY_DIR', plugin_dir_path( __FILE__ ) );
        define( 'GAMIPRESS_LLMS_POINTS_GATEWAY_URL', plugin_dir_url( __FILE__ ) );
        define( 'GAMIPRESS_LLMS_POINTS_GATEWAY_VER', '1.2.1' );
    }

    public function includes() {
        if ( class_exists( 'LifterLMS' ) && function_exists( 'gamipress_get_points_types' ) ) {
            require_once GAMIPRESS_LLMS_POINTS_GATEWAY_DIR . 'classes/class-gamipress-llms-points-gateway.php';
            require_once GAMIPRESS_LLMS_POINTS_GATEWAY_DIR . 'includes/gateway.php';
        }
    }
}

function GamiPress_LLMS_Points_Gateway() {
    return GamiPress_LifterLMS_Points_Gateway::instance();
}
GamiPress_LLMS_Points_Gateway();