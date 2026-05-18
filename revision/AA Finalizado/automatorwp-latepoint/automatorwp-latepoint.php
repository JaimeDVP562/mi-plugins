<?php
/**
 * Plugin Name:           AutomatorWP - LatePoint
 * Plugin URI:            https://automatorwp.com/add-ons/latepoint/
 * Description:           Connect AutomatorWP with LatePoint.
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-latepoint
 * Domain Path:           /languages/
 *
 * @package               AutomatorWP\LatePoint
 * @author                AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AutomatorWP_LatePoint {

    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_LatePoint();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();            
        }
        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_LATEPOINT_VER', '1.1.0' );
        define( 'AUTOMATORWP_LATEPOINT_FILE', __FILE__ );
        define( 'AUTOMATORWP_LATEPOINT_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_LATEPOINT_URL', plugin_dir_url( __FILE__ ) );
    }

    private function includes() {
        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/trigger-base.php';
            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/tags.php';

            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/triggers/created_booking.php';
            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/triggers/updated_booking.php';
            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/triggers/created_customer.php';
            require_once AUTOMATORWP_LATEPOINT_DIR . 'includes/triggers/user_login.php';
        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function register_integration() {
        automatorwp_register_integration( 'latepoint', array(
            'label' => 'LatePoint',
            'icon'  => AUTOMATORWP_LATEPOINT_URL . 'assets/latepoint.svg',
        ) );
    }

    public function meets_requirements() {
        return class_exists( 'AutomatorWP' ) && class_exists( 'LatePoint' );
    }

    public function license( $meta_boxes ) {
        $meta_boxes['automatorwp-latepoint-license'] = array(
            'title'  => 'LatePoint',
            'fields' => array(
                'automatorwp_latepoint_license' => array(
                    'type'      => 'edd_license',
                    'file'      => AUTOMATORWP_LATEPOINT_FILE,
                    'item_name' => 'LatePoint',
                ),
            )
        );
        return $meta_boxes;
    }

    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) {
            ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p><?php printf( __( 'AutomatorWP - LatePoint requires %s and %s in order to work.', 'automatorwp-latepoint' ), '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>', 'LatePoint' ); ?></p>
            </div>
            <?php
            define( 'AUTOMATORWP_ADMIN_NOTICES', true );
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'automatorwp-latepoint', false, basename( AUTOMATORWP_LATEPOINT_DIR ) . '/languages/' );
    }
}

function automatorwp_latepoint_init() {
    return AutomatorWP_LatePoint::instance();
}
add_action( 'plugins_loaded', 'automatorwp_latepoint_init' );