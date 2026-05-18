<?php
/**
 * Plugin Name:           AutomatorWP - Schedule Actions
 * Plugin URI:            https://automatorwp.com/add-ons/schedule-actions/
 * Description:           Schedule any action to run after a time delay of your choice or in a specific date.
 * Version:               1.1.8
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-schedule-actions
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Schedule_Actions
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Schedule_Actions {

    /**
     * @var         AutomatorWP_Schedule_Actions $instance The one true AutomatorWP_Schedule_Actions
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Schedule_Actions self::$instance The one true AutomatorWP_Schedule_Actions
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Schedule_Actions();
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
        define( 'AUTOMATORWP_SCHEDULE_ACTIONS_VER', '1.1.8' );

        // Plugin file
        define( 'AUTOMATORWP_SCHEDULE_ACTIONS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SCHEDULE_ACTIONS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SCHEDULE_ACTIONS_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_SCHEDULE_ACTIONS_DIR . 'includes/filters.php';
            require_once AUTOMATORWP_SCHEDULE_ACTIONS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_SCHEDULE_ACTIONS_DIR . 'includes/logs.php';
            require_once AUTOMATORWP_SCHEDULE_ACTIONS_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_SCHEDULE_ACTIONS_DIR . 'includes/upgrades.php';

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

        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
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

        $meta_boxes['automatorwp-schedule-actions-license'] = array(
            'title' => 'Schedule Actions',
            'fields' => array(
                'automatorwp_schedule_actions_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_SCHEDULE_ACTIONS_FILE,
                    'item_name' => 'Schedule Actions',
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
                        __( 'AutomatorWP - Schedule Actions requires %s in order to work. Please install and activate them.', 'automatorwp-schedule-actions' ),
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
        $lang_dir = AUTOMATORWP_SCHEDULE_ACTIONS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_schedule_actions_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-schedule-actions' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-schedule-actions', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-schedule-actions/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-schedule-actions/ folder
            load_textdomain( 'automatorwp-schedule-actions', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-schedule-actions/languages/ folder
            load_textdomain( 'automatorwp-schedule-actions', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-schedule-actions', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Schedule_Actions instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Schedule_Actions The one true AutomatorWP_Schedule_Actions
 */
function AutomatorWP_Schedule_Actions() {
    return AutomatorWP_Schedule_Actions::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Schedule_Actions' );