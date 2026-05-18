<?php
/**
 * Plugin Name:           AutomatorWP - BP Verified Member
 * Plugin URI:            https://automatorwp.com/
 * Description:           Connect AutomatorWP with Verified Member for BuddyPress.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-bp-verified-member
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\BP_Verified_Member
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_BP_Verified_Member {

    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_BP_Verified_Member();
            self::$instance->constants();
            self::$instance->hooks();
            self::$instance->includes();
        }
        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_BP_VERIFIED_MEMBER_VER',  '1.0.0' );
        define( 'AUTOMATORWP_BP_VERIFIED_MEMBER_FILE', __FILE__ );
        define( 'AUTOMATORWP_BP_VERIFIED_MEMBER_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_BP_VERIFIED_MEMBER_URL',  plugin_dir_url( __FILE__ ) );
    }

private function includes() {

        if ( $this->meets_requirements() ) {
            $functions_file = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/functions.php';
            if ( file_exists( $functions_file ) ) require_once $functions_file;

            $tags_file = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/tags.php';
            if ( file_exists( $tags_file ) ) require_once $tags_file;

            $ajax_file = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/ajax-functions.php';
            if ( file_exists( $ajax_file ) ) require_once $ajax_file;

            // --- TRIGGERS ---
            $trigger_verified = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/triggers/trigger-bp-verified-member.php';
            if ( file_exists( $trigger_verified ) ) require_once $trigger_verified;

            $trigger_request = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/triggers/trigger-bp-verification-request.php';
            if ( file_exists( $trigger_request ) ) require_once $trigger_request;

            // --- ACTIONS ---
            $action_verify = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/actions/action-bp-verified-member.php';
            if ( file_exists( $action_verify ) ) require_once $action_verify;

            $action_unverify = AUTOMATORWP_BP_VERIFIED_MEMBER_DIR . 'includes/actions/action-bp-unverify-user.php';
            if ( file_exists( $action_unverify ) ) require_once $action_unverify;
        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init',             array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
    }

    public function register_integration() {
        automatorwp_register_integration( 'bp-verified-member', array(
            'label' => 'BP Verified Member',
            'icon'  => AUTOMATORWP_BP_VERIFIED_MEMBER_URL . 'assets/bp-verified-member-icon.jpg',
        ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'automatorwp-bp-verified-member', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function admin_notices() {
        if ( $this->meets_requirements() ) return;
        ?>
        <div class="error">
            <p><?php printf( __( 'AutomatorWP - BP Verified Member requires <a href="%s">AutomatorWP</a>.', 'automatorwp-bp-verified-member' ), 'https://wordpress.org/plugins/automatorwp/' ); ?></p>
        </div>
        <?php
    }

    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }
}

function AutomatorWP_BP_Verified_Member_Init() {
    return AutomatorWP_Integration_BP_Verified_Member::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_BP_Verified_Member_Init', 11 );