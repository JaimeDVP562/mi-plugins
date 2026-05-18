<?php
/**
 * Plugin Name:           AutomatorWP - MemberPress
 * Plugin URI:            https://automatorwp.com/add-ons/memberpress/
 * Description:           Connect AutomatorWP with MemberPress.
 * Version:               1.1.1
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-memberpress
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\MemberPress
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_MemberPress {

    /**
     * @var         AutomatorWP_MemberPress $instance The one true AutomatorWP_MemberPress
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_MemberPress self::$instance The one true AutomatorWP_MemberPress
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_MemberPress();
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
        define( 'AUTOMATORWP_MEMBERPRESS_VER', '1.1.1' );

        // Plugin file
        define( 'AUTOMATORWP_MEMBERPRESS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_MEMBERPRESS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_MEMBERPRESS_URL', plugin_dir_url( __FILE__ ) );
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

            // Triggers
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/register-specific-field-value.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/purchase-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/purchase-lifetime-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/purchase-recurring-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/cancel-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/suspend-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/view-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/membership-expired.php';

            // Add-on Corporate Accounts
            if ( class_exists( 'MPCA_Corporate_Account' ) ) {
                require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/add-subaccount.php';
            }
            
            // Courses
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/complete-lesson.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/start-course.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/complete-course.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/triggers/view-course.php';


            // Actions
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/actions/add-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/actions/remove-membership.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/actions/cancel-membership.php';

            // Includes
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/tags.php';

            // Filters
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/filters/user-is-active.php';
            require_once AUTOMATORWP_MEMBERPRESS_DIR . 'includes/filters/user-is-not-active.php';

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
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'memberpress', array(
            'label' => 'MemberPress',
            'icon'  => AUTOMATORWP_MEMBERPRESS_URL . 'assets/memberpress.svg',
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

        $meta_boxes['automatorwp-memberpress-license'] = array(
            'title' => 'MemberPress',
            'fields' => array(
                'automatorwp_memberpress_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_MEMBERPRESS_FILE,
                    'item_name' => 'MemberPress',
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
                        __( 'AutomatorWP - MemberPress requires %s and %s in order to work. Please install and activate them.', 'automatorwp-memberpress' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://memberpress.com/" target="_blank">MemberPress</a>'
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

        if ( ! class_exists( 'MeprCtrlFactory' ) ) {
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
        $lang_dir = AUTOMATORWP_MEMBERPRESS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_memberpress_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-memberpress' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-memberpress', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-memberpress/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-memberpress/ folder
            load_textdomain( 'automatorwp-memberpress', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-memberpress/languages/ folder
            load_textdomain( 'automatorwp-memberpress', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-memberpress', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_MemberPress instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_MemberPress The one true AutomatorWP_MemberPress
 */
function AutomatorWP_MemberPress() {
    return AutomatorWP_MemberPress::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_MemberPress' );
