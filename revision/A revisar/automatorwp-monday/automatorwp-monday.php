<?php
/**
 * Plugin Name:           AutomatorWP - Monday
 * Plugin URI:            https://automatorwp.com/add-ons/monday/
 * Description:           Connect AutomatorWP with Monday.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-monday
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Monday
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */ 

final class AutomatorWP_Monday {

    /**
     * @var         AutomatorWP_Monday $instance The one true AutomatorWP_Monday
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Monday self::$instance The one true AutomatorWP_Monday
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Monday();
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
        define( 'AUTOMATORWP_MONDAY_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_MONDAY_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_MONDAY_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_MONDAY_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/actions/change-board-name.php';
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/actions/change-item-name.php';
            require_once AUTOMATORWP_MONDAY_DIR . 'includes/actions/change-item-value.php';
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

        automatorwp_register_integration( 'monday', array(
            'label' => 'Monday',
            'icon'  => AUTOMATORWP_MONDAY_URL . 'assets/monday.svg',
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

        $meta_boxes['automatorwp-monday-license'] = array(
            'title' => 'Monday',
            'fields' => array(
                'automatorwp_monday_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_MONDAY_FILE,
                    'item_name' => 'Monday',
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
                        __( 'AutomatorWP - Monday requires %s in order to work. Please install and activate it.', 'automatorwp-monday' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
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
        $lang_dir = AUTOMATORWP_MONDAY_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_monday_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-monday' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-monday', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-monday/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-monday/ folder
            load_textdomain( 'automatorwp-monday', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-monday/languages/ folder
            load_textdomain( 'automatorwp-monday', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-monday', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Monday instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Monday The one true AutomatorWP_Monday
 */
function AutomatorWP_Monday() {
    return AutomatorWP_Monday::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Monday' );
