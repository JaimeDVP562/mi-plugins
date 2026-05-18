<?php
/**
 * Plugin Name:           AutomatorWP - Slack
 * Plugin URI:            https://automatorwp.com/add-ons/slack/
 * Description:           Connect AutomatorWP with Slack.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-slack
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Slack
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Slack {

    /**
     * @var         AutomatorWP_Slack $instance The one true AutomatorWP_Slack
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Slack self::$instance The one true AutomatorWP_Slack
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Slack();
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
        define( 'AUTOMATORWP_SLACK_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_SLACK_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SLACK_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SLACK_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_SLACK_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-channel.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-comment-channel.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-comment-thread.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-comment-thread-list.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-comment-user.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-reaction-message.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/add-user-to-channel.php';
            require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/remove-user-from-channel.php';
            
            // Required payment to asign correct scope  
            // require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/invite-user-to-channel.php'; 
            // require_once AUTOMATORWP_SLACK_DIR . 'includes/actions/remove-channel.php';
           
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

        automatorwp_register_integration( 'slack', array(
            'label' => 'Slack',
            'icon'  => AUTOMATORWP_SLACK_URL . 'assets/slack.svg',
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

        $meta_boxes['automatorwp-slack-license'] = array(
            'title' => 'Slack',
            'fields' => array(
                'automatorwp_slack_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_SLACK_FILE,
                    'item_name' => 'Slack',
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
                        __( 'AutomatorWP - Slack requires %s in order to work. Please install and activate it.', 'automatorwp-slack' ),
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
        $lang_dir = AUTOMATORWP_SLACK_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_slack_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-slack' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-slack', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-slack/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-slack/ folder
            load_textdomain( 'automatorwp-slack', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-slack/languages/ folder
            load_textdomain( 'automatorwp-slack', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-slack', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Slack instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Slack The one true AutomatorWP_Slack
 */
function AutomatorWP_Slack() {
    return AutomatorWP_Slack::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Slack' );
