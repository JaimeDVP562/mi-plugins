<?php
/**
 * Plugin Name:           AutomatorWP - FluentBoards
 * Plugin URI:            https://automatorwp.com/add-ons/fluentboards/
 * Description:           Connect AutomatorWP with FluentBoards to create cards, manage boards, and automate workflows.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-fluentboards
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * Requires PHP:          7.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\FluentBoards
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_FluentBoards
{
    /**
     * @var         AutomatorWP_Integration_FluentBoards $instance The one true AutomatorWP_Integration_FluentBoards
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_FluentBoards self::$instance
     */
    public static function instance()
    {
        if ( ! self::$instance ) {
            self::$instance = new self();
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
    private function constants()
    {
        define( 'AUTOMATORWP_FLUENTBOARDS_VER',  '1.0.0' );
        define( 'AUTOMATORWP_FLUENTBOARDS_FILE', __FILE__ );
        define( 'AUTOMATORWP_FLUENTBOARDS_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_FLUENTBOARDS_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes()
    {
        if ($this->meets_requirements()) {


            // Core
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/add-user-to-board.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/assign-task.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/assign-task-to-user.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/change-task-status.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-board.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-label.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-stage.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-sub-task.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-subtask-group.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/create-task.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/list-task-labels.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/list-tasks.php';
            require_once AUTOMATORWP_FLUENTBOARDS_DIR . 'includes/actions/remove-assignee-from-task.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks()
    {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
    }
    
    /**
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration()
    {
        automatorwp_register_integration( 'fluentboards', array(
            'label' => 'FluentBoards',
            'icon'  => AUTOMATORWP_FLUENTBOARDS_URL . 'assets/fluentboards-icon.svg',
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

        $meta_boxes['automatorwp-fluentboards-license'] = array(
                'title' => 'FluentBoards',
                'fields' => array(
                        'automatorwp_fluentboards_license' => array(
                                'type' => 'edd_license',
                                'file' => AUTOMATORWP_FLUENTBOARDS_FILE,
                                'item_name' => 'FluentBoards',
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
                            __( 'AutomatorWP - FluentBoards requires %s in order to work. Please install and activate it.', 'automatorwp-fluentboards' ),
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
        $lang_dir = AUTOMATORWP_FLUENTBOARDS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_fluentboards_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-fluentboards' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-fluentboards', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-fluentboards/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-fluentboards/ folder
            load_textdomain( 'automatorwp-fluentboards', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-fluentboards/languages/ folder
            load_textdomain( 'automatorwp-fluentboards', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-fluentboards', false, $lang_dir );
        }

    }
}
    
/**
 * The main function responsible for returning the one true AutomatorWP_FluentBoards instance to functions everywhere
 *
 * @since       1.0.0
 * @return      AutomatorWP_FluentBoards
 */
function AutomatorWP_FluentBoards()
{
    return AutomatorWP_FluentBoards::Instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_FluentBoards' );
