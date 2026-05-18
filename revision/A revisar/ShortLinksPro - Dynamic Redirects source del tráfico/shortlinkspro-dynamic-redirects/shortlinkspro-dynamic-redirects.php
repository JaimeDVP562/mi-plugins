<?php
/**
 * Plugin Name:     	ShortLinksPro - Dynamic Redirects
 * Plugin URI:      	https://shortlinkspro.com/add-ons/dynamic-redirects
 * Description:     	Add dynamic redirects to your links!
 * Version:         	1.0.1
 * Author:          	ShortLinksPro
 * Author URI:      	https://shortlinkspro.com/
 * Text Domain:     	shortlinkspro-dynamic-redirects
 * Domain Path: 		/languages/
 * Requires PHP:        7.0
 * Requires at least: 	4.4
 * Tested up to: 		6.8
 * License:         	GPLv3
 *
 * @package         	ShortLinksPro_Dynamic_Redirects
 * @copyright       	Copyright (c) ShortLinksPro Dynamic Redirects
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

final class ShortLinksPro_Dynamic_Redirects {

    /**
     * @var         ShortLinksPro_Dynamic_Redirects $instance The one true ShortLinksPro Dynamic Redirects
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      ShortLinksPro Dynamic Redirects self::$instance The one true ShortLinksPro Dynamic Redirects
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new ShortLinksPro_Dynamic_Redirects();
            self::$instance->constants();
            self::$instance->libraries();
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
        define( 'SHORTLINKSPRO_DYNAMIC_REDIRECTS_VER', '1.0.1' );

        // Plugin file
        define( 'SHORTLINKSPRO_DYNAMIC_REDIRECTS_FILE', __FILE__ );

        // Plugin path
        define( 'SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'SHORTLINKSPRO_DYNAMIC_REDIRECTS_URL', plugin_dir_url( __FILE__ ) );

    }

    /**
     * Include plugin libraries
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function libraries() {

        // Licenses page
        require_once SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR . 'libraries/cmb2-licenses-page/cmb2-licenses-page.php';

        cmb2_lp_reg( 'shortlinkspro' );
        cmb2_lp_reg_license( 'shortlinkspro', 'shortlinkspro_dynamic_redirects_license', 'Dynamic Redirects', plugin_dir_path( __FILE__ ) );


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

            // The rest of files
            require_once SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR . 'includes/admin.php';
            require_once SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR . 'includes/filters.php';
            require_once SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR . 'includes/functions.php';

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

        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Hook in all our important pieces
        add_action( 'shortlinkspro_pre_init', array( $this, 'pre_init' ), 50 );
        add_action( 'shortlinkspro_init', array( $this, 'init' ), 50 );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ), 10 );

    }

    /**
     * Pre init function
     *
     * @access      private
     * @since       1.4.6
     * @return      void
     */
    function pre_init() {

        global $wpdb;
    }

    /**
     * Init function
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    function init() {



    }

    /**
     * Activation
     *
     * @access      private
     * @since       1.0.0
     */
    function activate() {

        // Include our important bits
        $this->libraries();
        $this->includes();

        // Setup default installation date
        $install_date = ( $exists = get_option( 'shortlinkspro_dynamic_redirects_install_date' ) ) ? $exists : '';

        if ( empty( $install_date ) ) {
            update_option( 'shortlinkspro_dynamic_redirects_install_date', date( 'Y-m-d H:i:s' ) );
        }

    }

    /**
     * Deactivation
     *
     * @access      private
     * @since       1.0.0
     */
    function deactivate() {

        // Include our important bits
        $this->libraries();
        $this->includes();

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'SHORTLINKSPRO_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'Shortlinks Pro - Dynamic Redirects requires %s in order to work. Please install and activate it.', 'shortlinkspro-qr-codes' ),
                        '<a href="https://wordpress.org/plugins/shortlinkspro/" target="_blank">Shortlinks Pro</a>',
                    ); ?>
                </p>
            </div>

            <?php define( 'SHORTLINKSPRO_ADMIN_NOTICES', true ); ?>

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

        if ( ! class_exists( 'ShortLinksPro' ) ) {
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
        $lang_dir = SHORTLINKSPRO_DYNAMIC_REDIRECTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'shortlinkspro_dynamic_redirects_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'shortlinkspro-dynamic-redirects' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'shortlinkspro-dynamic-redirects', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/shortlinkspro-dynamic-redirects/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/shortlinkspro-dynamic-redirects/ folder
            load_textdomain( 'shortlinkspro-dynamic-redirects', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/shortlinkspro-dynamic-redirects/languages/ folder
            load_textdomain( 'shortlinkspro-dynamic-redirects', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'shortlinkspro-dynamic-redirects', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \ShortLinksPro_Dynamic_Redirects The one true instance
 */
function ShortLinksPro_Dynamic_Redirects() {
    return ShortLinksPro_Dynamic_Redirects::instance();
}
add_action( 'plugins_loaded', 'ShortLinksPro_Dynamic_Redirects' );