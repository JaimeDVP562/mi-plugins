<?php
/**
 * Plugin Name:           AutomatorWP - WP User Registration & Membership integration
 * Plugin URI:            https://wordpress.org/add-ons/user-registration/
 * Description:           Connect AutomatorWP with WP User Registration & Membership.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-user-registration
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\User_Registration
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_User_Registration {

    /**
     * @var         AutomatorWP_Integration_User_Registration $instance The one true AutomatorWP_Integration_User_Registration
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_User_Registration self::$instance The one true AutomatorWP_Integration_User_Registration
     */
    public static function instance(): AutomatorWP_Integration_User_Registration {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_User_Registration();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    protected function constants() {
        // Plugin version
        define( 'AUTOMATORWP_USER_REGISTRATION_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_USER_REGISTRATION_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_USER_REGISTRATION_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_USER_REGISTRATION_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() && ! $this->pro_installed() ) {

            require_once AUTOMATORWP_USER_REGISTRATION_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_USER_REGISTRATION_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_USER_REGISTRATION_DIR . 'includes/triggers/register-submit.php';
            require_once AUTOMATORWP_USER_REGISTRATION_DIR . 'includes/triggers/register-update.php';
        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'user_registration', array(
            'label' => 'WP User Registration & Membership',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/user_registration.svg',
        ) );

    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    function license( $meta_boxes ) {

        $meta_boxes['automatorwp-user-registration-license'] = array(
            'title' => 'WP User Registration & Membership',
            'fields' => array(
                'automatorwp_user_registration_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_USER_REGISTRATION_FILE,
                    'item_name' => 'User Registration & Membership',
                ),
            )
        );

        return $meta_boxes;

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - User Registration & Membership requires %s and %s in order to work. Please install and activate them.', 'automatorwp-user-registration' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                                '<a href="https://es.wordpress.org/plugins/user-registration/" target="_blank">WP User Registration & Membership </a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }
    
        if ( ! class_exists( 'UserRegistration' ) ) {
            return false;
        }
    
        return true;
    
    }
    

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {

        if ( ! class_exists( 'AutomatorWP_User_Registration' ) ) {
            return false;
        }
        return true;

    }


/**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_USER_REGISTRATION_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_user_registration_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-user-registration' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-user-registration', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-user-registration/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-user-registration/ folder
            load_textdomain( 'automatorwp-user-registration', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-user-registration/languages/ folder
            load_textdomain( 'automatorwp-user-registration', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-user-registration', false, $lang_dir );
        }

    }


}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_User_Registration instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_User_Registration The one true AutomatorWP_Integration_User_Registration
 */
function AutomatorWP_Integration_User_Registration() {
    return AutomatorWP_Integration_User_Registration::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_User_Registration' );
