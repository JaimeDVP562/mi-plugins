<?php
/**
 * Plugin Name:     	BBForms
 * Plugin URI:      	https://bbforms.com
 * Description:     	Build [forms] faster and easily just by typing them!
 * Version:         	1.0.9
 * Author:          	BBForms
 * Author URI:      	https://bbforms.com/
 * Text Domain:     	bbforms
 * Domain Path: 		/languages/
 * Requires PHP:        7.0
 * Requires at least: 	4.4
 * Tested up to: 		6.9
 * License:         	GPLv3
 *
 * @package         	BBForms
 * @author          	BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @copyright       	Copyright (c) BBForms
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

final class BBForms {

    /**
     * @var         BBForms $instance The one true BBForms
     * @since       1.0.0
     */
    private static $instance;

    /**
     * @var         array $settings Stored settings
     * @since       1.0.0
     */
    public $settings = null;

    /**
     * @var         BBForms_Database $db Database object
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
     * @return      BBForms self::$instance The one true BBForms
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new BBForms();
            self::$instance->constants();
            self::$instance->libraries();
            self::$instance->classes();
            self::$instance->compatibility();
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
        define( 'BBFORMS_VER', '1.0.9' );

        // Plugin file
        define( 'BBFORMS_FILE', __FILE__ );

        // Plugin path
        define( 'BBFORMS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'BBFORMS_URL', plugin_dir_url( __FILE__ ) );

        // Upload DIR & URL
        $upload_dir = wp_upload_dir();

        if( ! defined( 'BBFORMS_UPLOAD_DIR' ) )
            define( 'BBFORMS_UPLOAD_DIR', $upload_dir['basedir'] . '/bbforms/' );

        if( ! defined( 'BBFORMS_UPLOAD_URL' ) )
            define( 'BBFORMS_UPLOAD_URL', $upload_dir['baseurl'] . '/bbforms/' );

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
        require_once BBFORMS_DIR . 'libraries/ct/init.php';
        require_once BBFORMS_DIR . 'libraries/ct-ajax-list-table/ct-ajax-list-table.php';

        // CMB2
        require_once BBFORMS_DIR . 'libraries/cmb2/init.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-metatabs-options/cmb2_metatabs_options.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-tabs/cmb2-tabs.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-field-select2/cmb2-field-select2.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-field-switch/cmb2-field-switch.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-field-tooltip/cmb2-field-tooltip.php';
        require_once BBFORMS_DIR . 'libraries/cmb2-conditional-fields/cmb2-conditional-fields.php';

    }

    /**
     * Include plugin classes
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function classes() {

        require_once BBFORMS_DIR . 'classes/form.php';

    }

    /**
     * Include compatibility files
     *
     * @access      private
     * @since       4.2.0
     * @return      void
     */
    private function compatibility() {

        // Backward compatibility

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
        require_once BBFORMS_DIR . 'includes/actions.php';
        require_once BBFORMS_DIR . 'includes/admin.php';
        require_once BBFORMS_DIR . 'includes/ajax-functions.php';
        require_once BBFORMS_DIR . 'includes/attachments.php';
        require_once BBFORMS_DIR . 'includes/bbcodes.php';
        require_once BBFORMS_DIR . 'includes/country.php';
        require_once BBFORMS_DIR . 'includes/cache.php';
        require_once BBFORMS_DIR . 'includes/cron.php';
        require_once BBFORMS_DIR . 'includes/custom-tables.php';
        require_once BBFORMS_DIR . 'includes/editor-controls.php';
        require_once BBFORMS_DIR . 'includes/error-messages.php';
        require_once BBFORMS_DIR . 'includes/fields.php';
        require_once BBFORMS_DIR . 'includes/file.php';
        require_once BBFORMS_DIR . 'includes/form-messages.php';
        require_once BBFORMS_DIR . 'includes/form-preview.php';
        require_once BBFORMS_DIR . 'includes/form-templates.php';
        require_once BBFORMS_DIR . 'includes/forms.php';
        require_once BBFORMS_DIR . 'includes/functions.php';
        require_once BBFORMS_DIR . 'includes/help.php';
        require_once BBFORMS_DIR . 'includes/options.php';
        require_once BBFORMS_DIR . 'includes/privacy.php';
        require_once BBFORMS_DIR . 'includes/scripts.php';
        require_once BBFORMS_DIR . 'includes/shortcodes.php';
        require_once BBFORMS_DIR . 'includes/submissions.php';
        require_once BBFORMS_DIR . 'includes/submit.php';
        require_once BBFORMS_DIR . 'includes/tags.php';

    }

    /**
     * Include integrations files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function integrations() {

        require_once BBFORMS_DIR . 'integrations/easy-digital-downloads/easy-digital-downloads.php';
        require_once BBFORMS_DIR . 'integrations/woocommerce/woocommerce.php';

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
     * @since       1.0.0
     * @return      void
     */
    function pre_init() {

        // Load all integrations
        $this->integrations();

        // Trigger our action to let other plugins know that BBForms is getting initialized
        do_action( 'bbforms_pre_init' );
    }

    /**
     * Init function
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    function init() {

        // Trigger our action to let other plugins know that BBForms is ready
        do_action( 'bbforms_init' );

    }

    /**
     * Post init function
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    function post_init() {

        // Trigger our action to let other plugins know that BBForms has been initialized
        do_action( 'bbforms_post_init' );

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

        require_once BBFORMS_DIR . 'includes/install.php';

        bbforms_install();

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

        require_once BBFORMS_DIR . 'includes/uninstall.php';

        bbforms_uninstall();

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
        $lang_dir = BBFORMS_DIR . '/languages/';
        $lang_dir = apply_filters( 'bbforms_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'bbforms' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'bbforms', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/bbforms/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/bbforms/ folder
            load_textdomain( 'bbforms', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/bbforms/languages/ folder
            load_textdomain( 'bbforms', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'bbforms', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \BBForms The one true instance
 */
function BBForms() {
    return BBForms::instance();
}

BBForms();