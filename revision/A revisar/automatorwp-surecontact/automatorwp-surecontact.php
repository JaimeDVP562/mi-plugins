<?php
/**
 * Plugin Name:           AutomatorWP - Sure Contact
 * Plugin URI:            https://automatorwp.com/add-ons/surecontact/
 * Description:           Connect AutomatorWP with SureContact.
 * Version:               1.0.1
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-surecontact
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SureContact
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_SureContact {

    /**
     * @var         AutomatorWP_SureContact $instance The one true AutomatorWP_SureContact
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_SureContact self::$instance The one true AutomatorWP_SureContact
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_SureContact();
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
    private function constants() {
        // Plugin version
        define( 'AutomatorWP_SureContact_VER', '1.0.1' );

        // Plugin file
        define( 'AutomatorWP_SureContact_FILE', __FILE__ );

        // Plugin path
        define( 'AutomatorWP_SureContact_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AutomatorWP_SureContact_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AutomatorWP_SureContact_DIR . 'includes/admin.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/functions.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/ajax-functions.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/scripts.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/tags.php';

            // SureContact Actions
            require_once AutomatorWP_SureContact_DIR . 'includes/actions/create-contact.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/actions/add-tag.php';
            require_once AutomatorWP_SureContact_DIR . 'includes/actions/subscribe-list.php';

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

        automatorwp_register_integration( 'surecontact', array(
                'label' => 'SureContact',
                'icon'  => AutomatorWP_SureContact_URL . 'assets/surecontact.svg',
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

        $meta_boxes['automatorwp-surecontact-license'] = array(
            'title' => 'surecontact',
            'fields' => array(
                'AutomatorWP_SureContact_license' => array(
                    'type' => 'edd_license',
                    'file' => AutomatorWP_SureContact_FILE,
                    'item_name' => 'surecontact',
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
                        __( 'AutomatorWP - surecontact requires %s in order to work. Please install and activate it.', 'automatorwp-surecontact' ),
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
        $lang_dir = AutomatorWP_SureContact_DIR . '/languages/';
        $lang_dir = apply_filters( 'AutomatorWP_SureContact_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-surecontact' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-surecontact', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-surecontact/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-surecontact/ folder
            load_textdomain( 'automatorwp-surecontact', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-surecontact/languages/ folder
            load_textdomain( 'automatorwp-surecontact', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-surecontact', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_SureContact instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_SureContact The one true AutomatorWP_SureContact
 */
function AutomatorWP_SureContact() {
    return AutomatorWP_SureContact::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_SureContact' );
