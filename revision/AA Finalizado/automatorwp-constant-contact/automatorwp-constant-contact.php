<?php
/**
 * Plugin Name:           AutomatorWP - Constant Contact
 * Plugin URI:            https://automatorwp.com/add-ons/constant-contact/
 * Description:           Connect AutomatorWP with Constant Contact.
 * Version:               1.0.7
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-constant-contact
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Constant_Contact
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Constant_Contact {

    /**
     * @var         AutomatorWP_Constant_Contact $instance The one true AutomatorWP_Constant_Contact
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Constant_Contact self::$instance The one true AutomatorWP_Constant_Contact
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Constant_Contact();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();            
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
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_CONSTANT_CONTACT_VER', '1.0.7' );

        // Plugin file
        define( 'AUTOMATORWP_CONSTANT_CONTACT_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_CONSTANT_CONTACT_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_CONSTANT_CONTACT_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Includes
            require_once AUTOMATORWP_CONSTANT_CONTACT_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_CONSTANT_CONTACT_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_CONSTANT_CONTACT_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_CONSTANT_CONTACT_DIR . 'includes/scripts.php';

            // Actions


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

        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'constant_contact', array(
            'label' => 'Constant Contact',
            'icon'  => AUTOMATORWP_CONSTANT_CONTACT_URL . 'assets/constant-contact.svg',
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

        $meta_boxes['automatorwp-constant-contact-license'] = array(
            'title' => 'Constant Contact',
            'fields' => array(
                'automatorwp_constant_contact_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_CONSTANT_CONTACT_FILE,
                    'item_name' => 'Constant Contact',
                ),
            )
        );

        return $meta_boxes;

    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    function activate() {

        if( $this->meets_requirements() ) {

        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    function deactivate() {

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
                        __( 'AutomatorWP - Constant Contact requires %s in order to work. Please install and activate it.', 'automatorwp-constant-contact' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
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
        $lang_dir = AUTOMATORWP_CONSTANT_CONTACT_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_constant_contact_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-constant-contact' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-constant-contact', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-constant-contact/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-constant-contact/ folder
            load_textdomain( 'automatorwp-constant-contact', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-constant-contact/languages/ folder
            load_textdomain( 'automatorwp-constant-contact', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-constant-contact', false, $lang_dir );
        }

    }
    

}

/**
 * The main function responsible for returning the one true AutomatorWP_Constant_Contact instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Constant_Contact The one true AutomatorWP_Constant_Contact
 */
function AutomatorWP_Constant_Contact() {
    return AutomatorWP_Constant_Contact::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Constant_Contact' );






