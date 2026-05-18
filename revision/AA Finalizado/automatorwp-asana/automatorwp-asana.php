<?php
/**
 * Plugin Name:           AutomatorWP - Asana
 * Plugin URI:            https://automatorwp.com/add-ons/asana/
 * Description:           Connect AutomatorWP with Asana to automate task management.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-asana
 * Domain Path:           /languages/
 *
 * @package               AutomatorWP\Asana
 * @author                AutomatorWP
 * @since                 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main AutomatorWP Asana Class.
 *
 * @since 1.0.0
 */
final class AutomatorWP_Asana {

    /**
     * Singleton instance.
     *
     * @var AutomatorWP_Asana
     */
    private static $instance;

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     * @return AutomatorWP_Asana
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Asana();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();            
        }
        return self::$instance;
    }

    /**
     * Defines plugin constants.
     *
     * @since 1.0.0
     * @return void
     */
    private function constants() {
        define( 'AUTOMATORWP_ASANA_VER', '1.0.0' );
        define( 'AUTOMATORWP_ASANA_FILE', __FILE__ );
        define( 'AUTOMATORWP_ASANA_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_ASANA_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Includes plugin files.
     *
     * @since 1.0.0
     * @return void
     */
    private function includes() {
        if ( $this->meets_requirements() ) {

            // Load functions.
            require_once AUTOMATORWP_ASANA_DIR . 'includes/functions.php';

            // Load admin.
            if ( is_admin() ) {
                require_once AUTOMATORWP_ASANA_DIR . 'includes/admin.php';
                require_once AUTOMATORWP_ASANA_DIR . 'includes/ajax-functions.php';
            }

            // Load actions.
            require_once AUTOMATORWP_ASANA_DIR . 'includes/actions/create-task.php';
        }
    }

    /**
     * Registers standard hooks.
     *
     * @since 1.0.0
     * @return void
     */
    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers the Asana integration in AutomatorWP.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_integration() {
        if ( function_exists( 'automatorwp_register_integration' ) ) {
            automatorwp_register_integration( 'asana', array(
                'label' => 'Asana',
                'icon'  => AUTOMATORWP_ASANA_URL . 'assets/asana.png',
            ) );
        }
    }

    /**
     * Adds the Asana license meta box.
     *
     * @since 1.0.0
     * @param array $meta_boxes The existing meta boxes.
     * @return array
     */
    public function license( $meta_boxes ) {
        $meta_boxes['automatorwp-asana-license'] = array(
            'title'  => 'Asana',
            'fields' => array(
                'automatorwp_asana_license' => array(
                    'type'      => 'edd_license',
                    'file'      => AUTOMATORWP_ASANA_FILE,
                    'item_name' => 'Asana',
                ),
            )
        );
        return $meta_boxes;
    }

    /**
     * Displays admin notices if requirements are not met.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) && is_admin() ) {
            ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf( __( 'AutomatorWP - Asana requires %s in order to work.', 'automatorwp-asana' ), '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>' ); ?>
                </p>
            </div>
            <?php
            define( 'AUTOMATORWP_ADMIN_NOTICES', true );
        }
    }

    /**
     * Checks if all dependencies are met.
     *
     * @since 1.0.0
     * @return bool
     */
    public function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Loads the plugin textdomain.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'automatorwp-asana', false, basename( AUTOMATORWP_ASANA_DIR ) . '/languages/' );
    }
}

/**
 * Main instance helper function.
 *
 * @since 1.0.0
 * @return AutomatorWP_Asana
 */
function AutomatorWP_Asana() {
    return AutomatorWP_Asana::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Asana' );
