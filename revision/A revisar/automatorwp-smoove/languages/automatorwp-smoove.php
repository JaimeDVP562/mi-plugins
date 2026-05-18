<?php
/**
 * Plugin Name:           AutomatorWP - Smoove
 * Plugin URI:            https://automatorwp.com/add-ons/smoove/
 * Description:           Connect AutomatorWP with smoove.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-smoove
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Smoove
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */ 

final class AutomatorWP_smoove {

    /**
     * @var         AutomatorWP_smoove $instance The one true AutomatorWP_smoove
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_smoove self::$instance The one true AutomatorWP_Smoove
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_smoove();
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
        define( 'AUTOMATORWP_SMOOVE_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_SMOOVE_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SMOOVE_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SMOOVE_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_SMOOVE_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/functions.php';
            require_once AUTOMATORWP_SMOOVE_DIR . 'includes/scripts.php';

            // Actions
           /*require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/create-card.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/change-card-list.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/delete-card.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/change-desc.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/comment-card.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/add-label.php';
            require_once AUTOMATORWP_SMOOVE_DIR  . 'includes/actions/add-member.php';
            require_once AUTOMATORWP_SMOOVE_DIR . 'includes/actions/add-checklist-item.php';*/

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

        automatorwp_register_integration( 'smmove', array(
            'label' => 'smoove',
            'icon'  => AUTOMATORWP_SMOOVE_URL . 'assets/smoove.svg',
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

        $meta_boxes['automatorwp-smoove-license'] = array(
            'title' => 'smoove',
            'fields' => array(
                'automatorwp_smoove_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_SMOOVE_FILE,
                    'item_name' => 'smoove',
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
                        __( 'AutomatorWP - Smoove requires %s in order to work. Please install and activate it.', 'automatorwp-smoove' ),
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
        $lang_dir = AUTOMATORWP_SMOOVE_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_smoove_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-smoove' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-smoove', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-smoove/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-smoove/ folder
            load_textdomain( 'automatorwp-smoove', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-smoove/languages/ folder
            load_textdomain( 'automatorwp-smoove', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-smoove', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Smoove instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Smoove The one true AutomatorWP_Smoove
 */
function AutomatorWP_Smoove() {
    return AutomatorWP_Smoove::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Smoove' );
