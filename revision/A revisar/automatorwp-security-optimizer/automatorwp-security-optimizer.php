<?php
/**
 * Plugin Name:           AutomatorWP - Security Optimizer integration
 * Plugin URI:            https://automatorwp.com/
 * Description:           Connect AutomatorWP with Security Optimizer (SiteGround Security).
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Text Domain:           automatorwp-integration
 * @package               AutomatorWP\Security_Optimizer
 */

/**
 * Main integration class for AutomatorWP Security Optimizer.
 */
final class AutomatorWP_Integration_Security_Optimizer {

    private static $instance;

    /**
     * Returns the singleton instance of the integration.
     *
     * @return AutomatorWP_Integration_Security_Optimizer
     */
    public static function instance() {
        if( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Security_Optimizer();
            self::$instance->constants();
            self::$instance->includes(); // Security is loaded here
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Defines integration constants.
     */
    private function constants() {
        define( 'AUTOMATORWP_SG_SECURITY_VER', '1.0.0' );
        define( 'AUTOMATORWP_SG_SECURITY_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_SG_SECURITY_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Loads required integration files if the dependencies are met.
     */
    private function includes() {
        if ( ! $this->meets_requirements() ) {
            return;
        }

        if ( file_exists( AUTOMATORWP_SG_SECURITY_DIR . 'includes/functions.php' ) ) {
            require_once AUTOMATORWP_SG_SECURITY_DIR . 'includes/functions.php';
        }
        
        require_once AUTOMATORWP_SG_SECURITY_DIR . 'includes/admin.php';

        add_action( 'automatorwp_init', array( $this, 'load_actions' ), 10 );
    }

    /**
     * Load the files from the actions folder only when it is safe
     */
    public function load_actions() {
        if ( ! class_exists( 'AutomatorWP_Integration_Action' ) ) {
            return;
        }

        $sg_security_active = $this->sg_security_is_active();

        $sg_required_actions = array(
            'block-user.php',
            'unlock-user.php',
            'block-ip.php',
            'unlock-ip.php',
            'force-reset-passwords.php',
            'force-logout-all.php',
        );

        foreach ( glob( AUTOMATORWP_SG_SECURITY_DIR . 'includes/actions/*.php' ) as $action_file ) {
            $action_filename = basename( $action_file );

            if ( in_array( $action_filename, $sg_required_actions ) && ! $sg_security_active ) {
                continue;
            }

            require_once $action_file;
        }
    }

    /**
     * Registers integration hooks with AutomatorWP.
     */
    private function hooks() {
        if ( $this->meets_requirements() ) {
            add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        }
    }

    /**
     * Registers the Security Optimizer integration in AutomatorWP.
     */
    public function register_integration() {
    $integration = array(
        'label' => 'Security Optimizer',
    );

    if ( $this->sg_security_is_active() ) {
        $integration['icon'] = AUTOMATORWP_SG_SECURITY_URL . 'assets/logo-sg-security.svg';
    }

    automatorwp_register_integration( 'security_optimizer', $integration );
}

    /**
     * Checks whether the integration requirements are met.
     *
     * @return bool True if AutomatorWP is active.
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Verifies whether SG Security is active.
     *
     * @return bool True if SG Security is available.
     */
    private function sg_security_is_active() {
        return defined( 'SG_SECURITY_VERSION' ) || class_exists( '\SG_Security\Block_Service\Block_Service' );
    }
}




/**
 * Initialize the plugin on a hook that runs before AutomatorWP is registered.
 */
function AutomatorWP_Integration_Security_Optimizer_Init() {
    return AutomatorWP_Integration_Security_Optimizer::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Security_Optimizer_Init' );

