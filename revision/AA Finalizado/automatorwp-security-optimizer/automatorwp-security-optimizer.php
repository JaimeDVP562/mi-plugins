<?php
/**
 * Plugin Name:           AutomatorWP - Security Optimizer
 * Plugin URI:            https://automatorwp.com/add-ons/security-optimizer/
 * Description:           Connect AutomatorWP with Security Optimizer (SiteGround Security).
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-security-optimizer
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Security_Optimizer
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Security_Optimizer {

    /**
     * @var         AutomatorWP_Integration_Security_Optimizer $instance The one true AutomatorWP_Integration_Security_Optimizer
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Returns the singleton instance of the integration.
     *
     * @since 1.0.0
     *
     * @return AutomatorWP_Integration_Security_Optimizer
     */
    public static function instance() {
        if( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Security_Optimizer();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }
        return self::$instance;
    }

    /**
     * Defines integration constants.
     *
     * @since 1.0.0
     */
    private function constants() {
        define( 'AUTOMATORWP_SG_SECURITY_VER', '1.0.0' );
        define( 'AUTOMATORWP_SG_SECURITY_FILE', __FILE__ );
        define( 'AUTOMATORWP_SG_SECURITY_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_SG_SECURITY_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Loads required integration files if the dependencies are met.
     *
     * @since 1.0.0
     */
    private function includes() {
        if ( ! $this->meets_requirements() ) {
            return;
        }

        require_once AUTOMATORWP_SG_SECURITY_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_SG_SECURITY_DIR . 'includes/admin.php';

        add_action( 'automatorwp_init', array( $this, 'load_actions' ), 10 );
    }

    /**
     * Load action files
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers the Security Optimizer integration in AutomatorWP.
     *
     * @since 1.0.0
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
     * Plugin admin notices
     *
     * @since  1.0.0
     */
    public function admin_notices() {
        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - Security Optimizer requires %s in order to work. Please install and activate it.', 'automatorwp-security-optimizer' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>
            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>
        <?php endif;
    }

    /**
     * Checks whether the integration requirements are met.
     *
     * @since  1.0.0
     *
     * @return bool True if AutomatorWP is active.
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Verifies whether SG Security is active.
     *
     * @since 1.0.0
     *
     * @return bool True if SG Security is available.
     */
    private function sg_security_is_active() {
        return defined( 'SG_SECURITY_VERSION' ) || class_exists( '\SG_Security\Block_Service\Block_Service' );
    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        $lang_dir = AUTOMATORWP_SG_SECURITY_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_security_optimizer_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-security-optimizer' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-security-optimizer', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-security-optimizer/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-security-optimizer', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-security-optimizer', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-security-optimizer', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Security_Optimizer instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Security_Optimizer The one true AutomatorWP_Integration_Security_Optimizer
 */
function AutomatorWP_Integration_Security_Optimizer() {
    return AutomatorWP_Integration_Security_Optimizer::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Integration_Security_Optimizer' );