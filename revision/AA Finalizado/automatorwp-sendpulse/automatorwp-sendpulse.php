<?php
/**
 * Plugin Name:           AutomatorWP - SendPulse
 * Plugin URI:            https://automatorwp.com/add-ons/sendpulse/
 * Description:           Connect AutomatorWP with SendPulse.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-sendpulse
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SendPulse
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_Sendpulse {

    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Sendpulse();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->load_textdomain();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_SENDPULSE_VER', '1.0.0' );
        define( 'AUTOMATORWP_SENDPULSE_FILE', __FILE__ );
        define( 'AUTOMATORWP_SENDPULSE_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_SENDPULSE_URL', plugin_dir_url( __FILE__ ) );
    }

    private function includes() {
        if( $this->meets_requirements() ) {
            // Core Includes
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/clicked-email-link.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/opened-email.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/received-email.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/subscriber-added.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/subscriber-deleted.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/subscriber-updated.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/unsubscribed.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/bounced-email.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/triggers/spam-complaint.php';


            // Actions
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/add-subscriber.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/add-tag-to-subscriber.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/remove-subscriber.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/remove-tag-from-subscriber.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/update-custom-variable.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/send-sms.php';
            require_once AUTOMATORWP_SENDPULSE_DIR . 'includes/actions/create-crm-deal.php';

            
        }
    }

    public function load_textdomain() {
        $lang_dir = AUTOMATORWP_SENDPULSE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_sendpulse_languages_directory', $lang_dir );
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-sendpulse' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-sendpulse', $locale );
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-sendpulse/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-sendpulse', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-sendpulse', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-sendpulse', false, $lang_dir );
        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    function register_integration() {
        automatorwp_register_integration( 'sendpulse', array(
            'label' => 'SendPulse',
            'icon'  => AUTOMATORWP_SENDPULSE_URL . 'assets/sendpulse.svg',
        ) );
    }

    function license( $meta_boxes ) {
        $meta_boxes['automatorwp-sendpulse-license'] = array(
                'title' => 'SendPulse',
                'fields' => array(
                        'automatorwp_sendpulse_license' => array(
                                'type' => 'edd_license',
                                'file' => AUTOMATORWP_SENDPULSE_FILE,
                                'item_name' => 'SendPulse',
                        ),
                )
        );
        return $meta_boxes;
    }

    function activate() {}
    function deactivate() {}

    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                            __( 'AutomatorWP - SendPulse requires %s in order to work. Please install and activate it.', 'automatorwp-sendpulse' ),
                            '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>
            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>
        <?php endif;
    }

    private function meets_requirements() {
        if ( ! class_exists( 'AutomatorWP' ) ) return false;
        return true;
    }
}

function AutomatorWP_Integration_Sendpulse() {
    return AutomatorWP_Integration_Sendpulse::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Sendpulse' );

if ( ! function_exists( 'AutomatorWP_Sendpulse' ) ) {
    function AutomatorWP_Sendpulse() {
        return AutomatorWP_Integration_Sendpulse();
    }
}

if ( ! class_exists( 'AutomatorWP_Sendpulse' ) && class_exists( 'AutomatorWP_Integration_Sendpulse' ) ) {
    class_alias( 'AutomatorWP_Integration_Sendpulse', 'AutomatorWP_Sendpulse' );
}