<?php
/**
 * Plugin Name: AutomatorWP - YASR Integration
 * Plugin URI: https://tudominio.com/
 * Description: Connect AutomatorWP with Yet Another Stars Rating (YASR).
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tudominio.com/
 * Text Domain: automatorwp-yasr
 * Domain Path: /languages/
 * Requires at least: 4.4
 * Tested up to: 6.4
 * License: GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package AutomatorWP\YASR
 * @author Tu Nombre
 */

final class AutomatorWP_YASR {

    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_YASR();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

    private function constants() {
        // Define your plugin constants here
            // Plugin version
    define( 'AUTOMATORWP_YASR_VERSION', '1.0.0' );

    // Plugin file
    define( 'AUTOMATORWP_YASR_FILE', __FILE__ );

    // Plugin path
    define( 'AUTOMATORWP_YASR_DIR', plugin_dir_path( __FILE__ ) );

    // Plugin URL
    define( 'AUTOMATORWP_YASR_URL', plugin_dir_url( __FILE__ ) );

    }

    private function includes() {
        // Include necessary files here
           // Triggers
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/AutomatorWP_YASR_Update_Entry_Status.php';
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/AutomatorWP_YASR_Rating_Submitted.php';
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/AutomatorWP_YASR_Overall_Submitted.php';
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/AutomatorWP_YASR_Submit_Rating.php';
           // Anonymous Triggers
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/anonymous_Rating.php';
        

    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        
        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        // Add more if needed
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

        if ( ! function_exists( 'yasr_fs' ) ) {
            return false;
        }

        return true;

    }

    function register_integration() {
        automatorwp_register_integration( 'yasr', array(
            'label' => 'WP YASR Integration',
            'icon'  => AUTOMATORWP_YASR_URL . 'assets/yasr.png', // Define the icon URL
        ) );
    }

    function license( $meta_boxes ) {

        $meta_boxes['automatorwp-yasr-license'] = array(
            'title' => 'WP yasr',
            'fields' => array(
                'automatorwp_yasr_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_YASR_FILE,
                    'item_name' => 'WP YASR',
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
                        __( 'AutomatorWP - WP YASR requires %s and %s in order to work. Please install and activate them.', 'automatorwp-yasr' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/yasr/" target="_blank">WP YASR</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    // Add more functions as needed
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_YASR_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_yasr_languages_directory', $lang_dir );
    
        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-yasr' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-yasr', $locale );
    
        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-yasr/' . $mofile;
    
        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-yasr/ folder
            load_textdomain( 'automatorwp-yasr', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-yasr/languages/ folder
            load_textdomain( 'automatorwp-yasr', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-yasr', false, $lang_dir );
        }
    
    }

}



function AutomatorWP_YASR() {
    return AutomatorWP_YASR::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_YASR' );

