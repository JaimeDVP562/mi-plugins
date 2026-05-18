<?php
/**
 * Plugin Name:           AutomatorWP - senpulse
 * Plugin URI:            https://automatorwp.com/add-ons/senpulse/
 * Description:           Connect AutomatorWP with senpulse.
 * Version:               1.0.1
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-senpulse
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\senpulse
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_senpulse {

    /**
     * @var         AutomatorWP_senpulse $instance The one true AutomatorWP_senpulse
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_senpulse self::$instance The one true AutomatorWP_senpulse
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_senpulse();
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
        define( 'AUTOMATORWP_senpulse_VER', '1.0.1' );

        // Plugin file
        define( 'AUTOMATORWP_senpulse_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_senpulse_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_senpulse_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_senpulse_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/create-list.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/create-task.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/remove-task.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/add-comment-task.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/add-tag-task.php';
            require_once AUTOMATORWP_senpulse_DIR . 'includes/actions/remove-tag-task.php';    

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

        automatorwp_register_integration( 'senpulse', array(
            'label' => 'senpulse',
            'icon'  => AUTOMATORWP_senpulse_URL . 'assets/senpulse.svg',
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

        $meta_boxes['automatorwp-senpulse-license'] = array(
            'title' => 'senpulse',
            'fields' => array(
                'automatorwp_senpulse_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_senpulse_FILE,
                    'item_name' => 'senpulse',
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
                        __( 'AutomatorWP - senpulse requires %s in order to work. Please install and activate it.', 'automatorwp-senpulse' ),
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
        $lang_dir = AUTOMATORWP_senpulse_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_senpulse_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-senpulse' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-senpulse', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-senpulse/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-senpulse/ folder
            load_textdomain( 'automatorwp-senpulse', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-senpulse/languages/ folder
            load_textdomain( 'automatorwp-senpulse', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-senpulse', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_senpulse instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_senpulse The one true AutomatorWP_senpulse
 */
function AutomatorWP_senpulse() {
    return AutomatorWP_senpulse::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_senpulse' );
