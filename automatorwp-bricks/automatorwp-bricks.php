<?php
/**
 * Plugin Name:     AutomatorWP - Bricks Builder Integration
 * Plugin URI:      https://automatorwp.com/integrations/bricks/
 * Description:     Connect Bricks Builder forms and elements with AutomatorWP.
 * Version:         1.0.0
 * Author:          AutomatorWP
 * Text Domain:     automatorwp-bricks
 * Domain Path:     /languages
 *
 * @package         AutomatorWP\Bricks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_Bricks {

    /**
     * @var AutomatorWP_Integration_Bricks $instance The one true instance
     */
    private static $instance;

    /**
     * Get active instance
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Bricks();
            self::$instance->constants();
            self::$instance->hooks();
            self::$instance->includes();
        }
        return self::$instance;
    }

    /**
     * Setup plugin constants
     */
    private function constants() {
        define( 'AUTOMATORWP_BRICKS_VER',  '1.0.0' );
        define( 'AUTOMATORWP_BRICKS_FILE', __FILE__ );
        define( 'AUTOMATORWP_BRICKS_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_BRICKS_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Setup plugin hooks
     */
    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init',             array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
    }

    /**
     * Include plugin files
     */
    private function includes() {
        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_BRICKS_DIR . "includes/triggers/bricks-form-submission.php";
            require_once AUTOMATORWP_BRICKS_DIR . 'includes/actions/bricks-toggle-element.php';
        }
    }

    /**
     * Registers the integration, triggers and actions
     */
    public function register_integration() {

        automatorwp_register_integration( 'bricks', array(
            'label' => 'Bricks Builder',
            'icon'  => AUTOMATORWP_BRICKS_URL . 'assets/bricks-icon.svg',
        ) );        
    }

    /**
     * Plugin Name: AutomatorWP - Bricks Builder
     * Text Domain: automatorwp-bricks  
     * Domain Path: /languages          
     */

    public function load_textdomain() {
        load_plugin_textdomain( 
            'automatorwp-bricks', 
            false, 
            dirname( plugin_basename( AUTOMATORWP_BRICKS_FILE ) ) . '/languages'
        );
    }

    public function admin_notices() {
        if ( ! $this->meets_requirements() ) {
            ?>
            <div class="error">
                <p><?php printf( __( 'AutomatorWP - Bricks requires <a href="%s">AutomatorWP</a> to work.', 'automatorwp-bricks' ), 'https://wordpress.org/plugins/automatorwp/' ); ?></p>
            </div>
            <?php
        }
    }

    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }
}

/**
 * Main instance
 */
function AutomatorWP_Bricks() {
    return AutomatorWP_Integration_Bricks::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Bricks' );