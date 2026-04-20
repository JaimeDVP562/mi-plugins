<?php
/**
 * Plugin Name:           AutomatorWP - Google Groups
 * Plugin URI:            https://automatorwp.com/add-ons/google-groups/
 * Description:           Connect AutomatorWP with Google Groups to manage groups and members.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-googlegroups
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\GoogleGroups
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Class for Google Groups Integration
 */
final class AutomatorWP_GoogleGroups {

    /**
     * @var AutomatorWP_GoogleGroups The single instance of the class
     */
    private static $instance = null;

    /**
     * Main Instance
     *
     * @since 1.0.0
     * @return AutomatorWP_GoogleGroups
     */
    public static function instance() {

        if ( self::$instance === null ) {
            self::$instance = new self();
            self::$instance->constants();
            self::$instance->includes(); 
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define plugin constants for paths and versions 
     */
    private function constants() {

        define( 'AUTOMATORWP_GOOGLEGROUPS_VER', '1.0.0' );
        define( 'AUTOMATORWP_GOOGLEGROUPS_FILE', __FILE__ );
        define( 'AUTOMATORWP_GOOGLEGROUPS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_GOOGLEGROUPS_URL', plugin_dir_url( __FILE__ ) );

    }

    /**
     * Include required files for triggers, actions, and administration
     */
    private function includes() {

        // Core requirement check 
        if ( ! $this->meets_requirements() ) {
            return;
        }

        // Administrative and Helper bases
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/scripts.php';

        // Google Groups Action registrations
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/add-member.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/remove-member.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/create-group.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/delete-group.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/update-group.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/change-member-role.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/remove-all-members.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/send-message.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/export-members.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/actions/set-topic.php';

        // Google Groups Trigger registrations
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/member-added.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/member-removed.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/group-created.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/group-deleted.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/member-role-changed.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/group-settings-changed.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/member-inactive.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/group-owner-removed.php';
        require_once AUTOMATORWP_GOOGLEGROUPS_DIR . 'includes/triggers/membership-limit-reached.php';

    }

    /**
     * Register global hooks
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        register_activation_hook( AUTOMATORWP_GOOGLEGROUPS_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( AUTOMATORWP_GOOGLEGROUPS_FILE, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );

    }

    /**
     * Register the integration in AutomatorWP 
     */
    public function register_integration() {

        if ( ! function_exists( 'automatorwp_register_integration' ) ) {
            return;
        }

        automatorwp_register_integration( 'googlegroups', array(
            'label' => 'Google Groups',
            'icon'  => AUTOMATORWP_GOOGLEGROUPS_URL . 'assets/img/google-groups.svg',
        ) );

    }

    /**
     * Register the license meta box for EDD
     */
    public function license( $meta_boxes ) {

        $meta_boxes['automatorwp-googlegroups-license'] = array(
            'title'  => 'Google Groups',
            'fields' => array(
                'automatorwp_googlegroups_license' => array(
                    'type'      => 'edd_license',
                    'file'      => AUTOMATORWP_GOOGLEGROUPS_FILE,
                    'item_name' => 'Google Groups',
                ),
            )
        );

        return $meta_boxes;
    }

    /**
     * Activation logic 
     */
    public function activate() {
        // Enable test/mock mode by default when the plugin is activated.
        // This is intended for development/testing so users (students) can try the plugin
        // without configuring Google Workspace credentials. Your colleague can later
        // disable the test mode or the plugin when switching to production.
        if ( function_exists( 'update_option' ) ) {
            update_option( 'automatorwp_googlegroups_test_mode', 1 );
        }

        // Initialize a default mock state (groups + empty members) if not already present.
        if ( function_exists( 'get_option' ) ) {
            $exists = get_option( 'automatorwp_googlegroups_mock_state', false );
            if ( empty( $exists ) ) {
                $state = array(
                    'groups' => array(
                        array( 'email' => 'staff@local.test', 'name' => 'Staff' ),
                        array( 'email' => 'students@local.test', 'name' => 'Students' ),
                    ),
                    'members' => array(),
                );
                update_option( 'automatorwp_googlegroups_mock_state', $state );
            }
        }
    }

    /**
     * Deactivation logic 
     */
    public function deactivate() {
        // Disable test/mock mode when the plugin is deactivated to avoid leaving test
        // mode enabled on the site accidentally.
        if ( function_exists( 'update_option' ) ) {
            update_option( 'automatorwp_googlegroups_test_mode', 0 );
        }
    }

    /**
     * Display administrative notices for dependency errors
     */
    public function admin_notices() {

        if ( $this->meets_requirements() || defined( 'AUTOMATORWP_GOOGLEGROUPS_NOTICES' ) ) {
            return;
        }

        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php printf( __( 'AutomatorWP - Google Groups requires %s in order to work.', 'automatorwp-googlegroups' ), '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>' ); ?></p>
        </div>
        <?php
        define( 'AUTOMATORWP_GOOGLEGROUPS_NOTICES', true );
    }

    /**
     * Verify if dependencies are met
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Load translation text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'automatorwp-googlegroups', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

}

/**
 * Main instance initialization via hook 
 */
function automatorwp_googlegroups() {
    return AutomatorWP_GoogleGroups::instance();
}
add_action( 'plugins_loaded', 'automatorwp_googlegroups' );
