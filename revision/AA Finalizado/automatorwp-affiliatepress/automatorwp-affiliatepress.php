<?php
/**
 * Plugin Name:           AutomatorWP - AffiliatePress
 * Plugin URI:            https://automatorwp.com/
 * Description:           Connect AutomatorWP with AffiliatePress.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-affiliatepress
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\AffiliatePress
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_AffiliatePress {

    private static $instance;

    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_AffiliatePress();
            self::$instance->constants();
            self::$instance->hooks();
            self::$instance->includes();
        }

        return self::$instance;
    }

    private function constants() {

        define( 'AUTOMATORWP_AFFILIATEPRESS_VER',  '1.0.0' );
        define( 'AUTOMATORWP_AFFILIATEPRESS_FILE', __FILE__ );
        define( 'AUTOMATORWP_AFFILIATEPRESS_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_AFFILIATEPRESS_URL',  plugin_dir_url( __FILE__ ) );
    }

    private function includes() {

        if ( $this->meets_requirements() ) {
            
            $functions_file = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/functions.php';
            if ( file_exists( $functions_file ) ) require_once $functions_file;

            $tags_file = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/tags.php';
            if ( file_exists( $tags_file ) ) require_once $tags_file;

            $ajax_file = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/ajax-functions.php';
            if ( file_exists( $ajax_file ) ) require_once $ajax_file;

            $trigger_register = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/triggers/user-registers-affiliate.php';
            if ( file_exists( $trigger_register ) ) require_once $trigger_register;

            $trigger_commission = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/triggers/user-earns-commission.php';
            if ( file_exists( $trigger_commission ) ) require_once $trigger_commission;

            $trigger_approved = AUTOMATORWP_AFFILIATEPRESS_DIR . 'includes/triggers/affiliate-is-approved.php';
            if ( file_exists( $trigger_approved ) ) require_once $trigger_approved;
        }
    }

    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init',             array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
    }

    public function register_integration() {

        automatorwp_register_integration( 'affiliatepress', array(
            'label' => 'AffiliatePress',
            'icon'  => AUTOMATORWP_AFFILIATEPRESS_URL . 'assets/base-icon.jpg',
        ) );
    }

    public function load_textdomain() {

        load_plugin_textdomain(
            'automatorwp-affiliatepress',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    public function admin_notices() {

        if ( $this->meets_requirements() ) {
            return;
        }

        ?>
        <div class="error">
            <p><?php printf(
                __( 'AutomatorWP - AffiliatePress requires <a href="%s">AutomatorWP</a> in order to work. Please install and activate it first.', 'automatorwp-affiliatepress' ),
                'https://wordpress.org/plugins/automatorwp/'
            ); ?></p>
        </div>
        <?php
    }

    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;
    }
}

function AutomatorWP_AffiliatePress_Init() {
    return AutomatorWP_Integration_AffiliatePress::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_AffiliatePress_Init', 11 );