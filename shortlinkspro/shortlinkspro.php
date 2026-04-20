<?php
/**
 * Plugin Name:     	ShortLinks Pro
 * Plugin URI:      	https://shortlinkspro.com
 * Description:     	Shorten, track, manage and share any URL using your own domain name!
 * Version:         	1.2.1
 * Author:          	ShortLinks Pro
 * Author URI:      	https://shortlinkspro.com/
 * Text Domain:     	shortlinkspro
 * Domain Path: 		/languages/
 * Requires PHP:        7.0
 * Requires at least: 	4.4
 * Tested up to: 		6.9
 * License:         	GPLv3
 *
 * @package         	ShortLinksPro
 * @author          	ShortLinks Pro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @copyright       	Copyright (c) ShortLinks Pro
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

final class ShortLinksPro {

    /**
     * @var         ShortLinksPro $instance The one true ShortLinksPro
     * @since       1.0.0
     */
    private static $instance;

    /**
     * @var         array $settings Stored settings
     * @since       1.0.0
     */
    public $settings = null;

    /**
     * @var         ShortLinksPro_Database $db Database object
     * @since       1.0.0
     */
    public $db;

    /**
     * @var         array $cache Cache class
     * @since       1.0.0
     */
    public $cache = array();

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      ShortLinksPro self::$instance The one true ShortLinksPro
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new ShortLinksPro();
            self::$instance->constants();
            self::$instance->libraries();
            self::$instance->classes();
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
        define( 'SHORTLINKSPRO_VER', '1.2.1' );

        // Plugin file
        define( 'SHORTLINKSPRO_FILE', __FILE__ );

        // Plugin path
        define( 'SHORTLINKSPRO_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'SHORTLINKSPRO_URL', plugin_dir_url( __FILE__ ) );

    }

    /**
     * Include plugin libraries
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function libraries() {

        // Custom Tables
        require_once SHORTLINKSPRO_DIR . 'libraries/ct/init.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/ct-ajax-list-table/ct-ajax-list-table.php';

        // CMB2
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2/init.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-metatabs-options/cmb2_metatabs_options.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-tabs/cmb2-tabs.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-field-switch/cmb2-field-switch.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-field-tooltip/cmb2-field-tooltip.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-field-select2/cmb2-field-select2.php';
        require_once SHORTLINKSPRO_DIR . 'libraries/cmb2-conditional-fields/cmb2-conditional-fields.php';

    }

    /**
     * Include plugin classes
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function classes() {

        require_once SHORTLINKSPRO_DIR . 'classes/database.php';

    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        // The rest of files
        require_once SHORTLINKSPRO_DIR . 'includes/admin.php';
        require_once SHORTLINKSPRO_DIR . 'includes/ajax-functions.php';
        require_once SHORTLINKSPRO_DIR . 'includes/cache.php';
        require_once SHORTLINKSPRO_DIR . 'includes/clicks.php';
        require_once SHORTLINKSPRO_DIR . 'includes/cron.php';
        require_once SHORTLINKSPRO_DIR . 'includes/custom-tables.php';
        require_once SHORTLINKSPRO_DIR . 'includes/date.php';
        require_once SHORTLINKSPRO_DIR . 'includes/functions.php';
        require_once SHORTLINKSPRO_DIR . 'includes/links.php';
        require_once SHORTLINKSPRO_DIR . 'includes/redirect.php';
        require_once SHORTLINKSPRO_DIR . 'includes/scripts.php';
        require_once SHORTLINKSPRO_DIR . 'includes/tracking.php';

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
        add_action( 'plugins_loaded', array( $this, 'pre_init' ), 20 );
        add_action( 'plugins_loaded', array( $this, 'init' ), 50 );
        add_action( 'plugins_loaded', array( $this, 'post_init' ), 999 );

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

        $this->db = new ShortLinksPro_Database();

        // Setup WordPress database tables
        $this->db->posts 				= $wpdb->posts;
        $this->db->postmeta 			= $wpdb->postmeta;
        $this->db->users 				= $wpdb->users;
        $this->db->usermeta 			= $wpdb->usermeta;

        // Setup ShortLinksPro database tables
        $this->db->links 		        = $wpdb->shortlinkspro_links;
        $this->db->links_meta 		    = $wpdb->shortlinkspro_links_meta;
        $this->db->clicks 		        = $wpdb->shortlinkspro_clicks;
        $this->db->clicks_meta 		    = $wpdb->shortlinkspro_clicks_meta;

        // Trigger our action to let other plugins know that ShortLinksPro is getting initialized
        do_action( 'shortlinkspro_pre_init' );
    }

    /**
     * Init function
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    function init() {

        // Trigger our action to let other plugins know that ShortLinksPro is ready
        do_action( 'shortlinkspro_init' );

    }

    /**
     * Post init function
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    function post_init() {

        // Trigger our action to let other plugins know that ShortLinksPro has been initialized
        do_action( 'shortlinkspro_post_init' );

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

        require_once SHORTLINKSPRO_DIR . 'includes/install.php';

        shortlinkspro_install();

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

        require_once SHORTLINKSPRO_DIR . 'includes/uninstall.php';

        shortlinkspro_uninstall();

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
        $lang_dir = SHORTLINKSPRO_DIR . '/languages/';
        $lang_dir = apply_filters( 'shortlinkspro_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'shortlinkspro' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'shortlinkspro', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/shortlinkspro/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/shortlinkspro/ folder
            load_textdomain( 'shortlinkspro', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/shortlinkspro/languages/ folder
            load_textdomain( 'shortlinkspro', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'shortlinkspro', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \ShortLinksPro The one true instance
 */
function ShortLinksPro() {
    return ShortLinksPro::instance();
}

ShortLinksPro();